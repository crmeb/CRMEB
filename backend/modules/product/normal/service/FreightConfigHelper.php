<?php
declare(strict_types=1);

namespace modules\product\normal\service;

/**
 * 运费配置规范化工具
 * 统一新旧格式兼容逻辑，供 ProductSaveServices 和 ProductServices 复用
 *
 * 新格式结构：
 *   express:      {type: template|fixed|free, postage: N, temp_id: N}
 *   door_service: {type: free|fixed, postage: N}
 *
 * 旧格式兼容：检测到顶层 type/fixed/template 节点时自动升级为新格式
 */
class FreightConfigHelper
{
    /**
     * 默认运费配置
     * express 默认运费模板（temp_id=0 表示未配置），door_service 默认免费上门
     */
    public const DEFAULT_CONFIG = [
        'express'      => ['type' => 'template', 'postage' => 0, 'temp_id' => 0],
        'door_service' => ['type' => 'free', 'postage' => 0],
    ];

    /**
     * express.type 合法枚举
     */
    private const EXPRESS_TYPES = ['template', 'fixed', 'free'];

    /**
     * door_service.type 合法枚举
     */
    private const DOOR_TYPES = ['free', 'fixed'];

    /**
     * 规范化运费配置（兼容旧格式输入，输出新双节点格式）
     *
     * 兼容策略：
     * - 检测到顶层 type/fixed/template 节点时视为旧格式，自动升级
     * - 旧 door_service 无 type 字段，根据 postage > 0 推导为 fixed/free
     * - type=free 时强制 postage=0，避免脏数据残留
     * - type=template 时保留 temp_id，type=fixed 时 temp_id 置 0
     *
     * @param mixed $config 原始配置（可能是数组、JSON 字符串、null）
     * @return array 规范化后的新格式配置
     */
    public static function normalize($config): array
    {
        // JSON 字符串反序列化
        if (is_string($config) && $config !== '') {
            $decoded = json_decode($config, true);
            $config = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($config)) {
            $config = [];
        }

        // ===== 旧格式兼容：检测顶层 type/fixed/template 节点，自动升级为新格式 =====
        if (isset($config['type']) || isset($config['fixed']) || isset($config['template'])) {
            return self::upgradeFromLegacy($config);
        }

        // ===== 新格式规范化 =====
        return self::normalizeNewFormat($config);
    }

    /**
     * 旧格式升级为新格式
     * 旧格式：{type, fixed:{postage}, template:{temp_id}, door_service:{postage}}
     * 新格式：{express:{type,postage,temp_id}, door_service:{type,postage}}
     *
     * @param array $config 旧格式配置
     * @return array 新格式配置
     */
    private static function upgradeFromLegacy(array $config): array
    {
        $oldType = (string)($config['type'] ?? 'template');

        // express 节点：从旧 type 映射
        $expressType = in_array($oldType, self::EXPRESS_TYPES, true) ? $oldType : 'template';
        $express = [
            'type'    => $expressType,
            // type=fixed 时读旧 fixed.postage，其他情况置 0
            'postage' => $expressType === 'fixed'
                ? round((float)($config['fixed']['postage'] ?? 0), 2)
                : 0.00,
            // type=template 时读旧 template.temp_id，其他情况置 0
            'temp_id' => $expressType === 'template'
                ? (int)($config['template']['temp_id'] ?? 0)
                : 0,
        ];

        // door_service 节点：旧格式无 type，根据 postage 推导
        $oldDoorPostage = round((float)($config['door_service']['postage'] ?? 0), 2);
        $doorService = [
            'type'    => $oldDoorPostage > 0 ? 'fixed' : 'free',
            'postage' => $oldDoorPostage,
        ];

        return [
            'express'      => $express,
            'door_service' => $doorService,
        ];
    }

    /**
     * 规范化新格式配置
     *
     * @param array $config 新格式配置（可能字段缺失或类型不规范）
     * @return array 规范化后的新格式配置
     */
    private static function normalizeNewFormat(array $config): array
    {
        // ===== express 节点 =====
        $express = $config['express'] ?? [];
        if (!is_array($express)) {
            $express = [];
        }
        $expressType = (string)($express['type'] ?? 'template');
        if (!in_array($expressType, self::EXPRESS_TYPES, true)) {
            $expressType = 'template';
        }
        // type=free 时 postage 不读取，统一置 0 避免脏数据
        $expressPostage = $expressType === 'free'
            ? 0.00
            : round((float)($express['postage'] ?? 0), 2);
        // type=template 时保留 temp_id，其他情况置 0
        $expressTempId = $expressType === 'template'
            ? (int)($express['temp_id'] ?? 0)
            : 0;

        // ===== door_service 节点 =====
        $doorService = $config['door_service'] ?? [];
        if (!is_array($doorService)) {
            $doorService = [];
        }
        $doorType = (string)($doorService['type'] ?? 'free');
        if (!in_array($doorType, self::DOOR_TYPES, true)) {
            $doorType = 'free';
        }
        // type=free 时 postage 不读取，统一置 0
        $doorPostage = $doorType === 'free'
            ? 0.00
            : round((float)($doorService['postage'] ?? 0), 2);

        return [
            'express' => [
                'type'    => $expressType,
                'postage' => $expressPostage,
                'temp_id' => $expressTempId,
            ],
            'door_service' => [
                'type'    => $doorType,
                'postage' => $doorPostage,
            ],
        ];
    }
}
