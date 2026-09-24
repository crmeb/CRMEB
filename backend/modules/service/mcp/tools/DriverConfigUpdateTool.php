<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\service\mcp\tools;

use app\dao\driver\DriverDao;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：修改第三方驱动配置
 *
 * 按驱动类型与标识定位驱动后，更新其配置字段值（写入 eb_driver 的
 * config_fields JSON）或切换启用/禁用状态；启用遵循同类型互斥约定
 * （启用一个自动禁用同类型其余驱动），保存后清除缓存立即生效。
 */
class DriverConfigUpdateTool implements McpToolInterface
{
    /**
     * 配置值实际存于系统配置（sys_config）的受保护字段
     * 一号通 appid/appsecret 为系统全局配置，禁止通过驱动配置修改
     */
    private const PROTECTED_FIELDS = ['yihaotong_appid', 'yihaotong_appsecret'];

    /**
     * 通用驱动 DAO
     * @var DriverDao
     */
    private DriverDao $dao;

    /**
     * 构造函数
     * @param DriverDao $dao 通用驱动 DAO
     */
    public function __construct(DriverDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_driver_config_update';
    }

    /**
     * 交互声明：本工具会修改驱动配置/启停状态并立即生效，需用户确认后才真实执行
     * @return string execute=需确认后执行
     */
    public function interaction(): string
    {
        return 'execute';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '修改第三方驱动的配置项值或启用/禁用驱动。支持短信/邮件/云存储/机器人/物流/电子面单/发票/打印/商品采集等类型。'
            . '需传入驱动类型（driver_type）与驱动标识（driver_key，可先通过 crmeb_driver_list 查询），'
            . 'config 传字段名到新值的映射（字段名与定义需匹配 crmeb_driver_list 返回的 config_fields.field），'
            . 'status 传 1=启用 0=禁用（启用同类型驱动会自动禁用同类型其余驱动）；修改后立即生效';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '驱动配置修改';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '系统设置';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['driver_type', 'driver_key'],
            'properties' => [
                'driver_type' => [
                    'type'        => 'string',
                    'description' => '驱动类型：sms=短信 email=邮件 upload=云存储 bot=机器人 logistics=物流 '
                        . 'express=电子面单 invoice=电子发票 printer=小票打印 copyproduct=商品采集',
                ],
                'driver_key'  => ['type' => 'string', 'description' => '驱动标识，如 yihaotong、aliyun、qq'],
                'config'      => [
                    'type'                 => 'object',
                    'description'          => '要更新的配置字段映射（字段名 => 新值），'
                        . '字段名必须与 crmeb_driver_list 返回的 config_fields[].field 一致',
                    'additionalProperties' => true,
                ],
                'status'      => [
                    'type'        => 'integer',
                    'enum'        => [0, 1],
                    'description' => '驱动启停状态：1=启用（同类型互斥，自动禁用其余驱动） 0=禁用',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（driver_type/driver_key 必填，config/status 至少一项）
     * @return array 修改结果（凭证类配置新旧值均脱敏）
     * @throws McpException 驱动不存在、字段非法或参数缺失
     */
    public function execute(array $args): array
    {
        $type = strtolower(trim((string)($args['driver_type'] ?? '')));
        $key = trim((string)($args['driver_key'] ?? ''));
        if ($type === '' || $key === '') {
            throw new McpException('driver_type 与 driver_key 不能为空');
        }

        $driver = $this->dao->getByTypeAndKey($type, $key);
        if (!$driver) {
            throw new McpException("驱动 {$type}/{$key} 不存在，请先通过 crmeb_driver_list 查询确认");
        }

        $config = $args['config'] ?? null;
        $status = $args['status'] ?? null;
        if ((!is_array($config) || $config === []) && $status === null) {
            throw new McpException('config 与 status 至少传入一项，否则无内容可修改');
        }
        if ($status !== null && !in_array((int)$status, [0, 1], true)) {
            throw new McpException('status 取值只能为 0（禁用）或 1（启用）');
        }

        $oldStatus = (int)$driver->status;
        // 配置值更新：校验字段合法后写入 config_fields 的 value 属性
        $configResult = [];
        if (is_array($config) && $config !== []) {
            $configResult = $this->saveConfig($driver, $config);
        }

        // 启停更新：启用时先禁用同类型其余驱动（互斥模式，与后台行为一致）
        $statusChanged = false;
        if ($status !== null && (int)$status !== $oldStatus) {
            if ((int)$status === 1) {
                $this->dao->disableOthers($type, (int)$driver->id);
            }
            $this->dao->update((int)$driver->id, ['status' => (int)$status]);
            $statusChanged = true;
        }

        if ($configResult !== [] || $statusChanged) {
            DriverRegistry::resetCache();
            if ($type === 'upload') {
                CacheService::delete('storage_list');
            }
        }

        return [
            'driver_type' => $type,
            'driver_key'  => $key,
            'driver_name' => (string)$driver->driver_name,
            'config'      => $configResult,
            'status'      => [
                'old'     => $oldStatus,
                'new'     => $oldStatus === (int)($status ?? $oldStatus) ? $oldStatus : (int)$status,
                'changed' => $statusChanged,
            ],
            'message'     => '修改成功',
        ];
    }

    /**
     * 更新驱动配置字段值（写入 config_fields JSON 的 value 属性）
     * @param \app\model\driver\Driver $driver 驱动记录
     * @param array $config 字段名 => 新值映射
     * @return array 每个字段的修改明细（凭证类新旧值均脱敏）
     * @throws McpException 字段不在驱动定义中或为受保护字段
     */
    private function saveConfig(\app\model\driver\Driver $driver, array $config): array
    {
        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        if ($configFields === []) {
            throw new McpException("驱动 {$driver->driver_key} 无配置字段定义，无需配置");
        }

        // 按字段名建立索引，便于校验传入字段是否合法
        $fieldMap = [];
        foreach ($configFields as $index => $item) {
            if (!empty($item['field'])) {
                $fieldMap[(string)$item['field']] = $index;
            }
        }

        $result = [];
        foreach ($config as $fieldName => $value) {
            $fieldName = (string)$fieldName;
            if (!isset($fieldMap[$fieldName])) {
                $allow = implode('、', array_keys($fieldMap));
                throw new McpException("配置字段 {$fieldName} 不存在，该驱动允许的字段：{$allow}");
            }
            if (in_array($fieldName, self::PROTECTED_FIELDS, true)) {
                throw new McpException("配置字段 {$fieldName} 为系统全局配置，不允许通过驱动配置修改");
            }
            $index = $fieldMap[$fieldName];
            $oldValue = $configFields[$index]['value'] ?? null;
            $configFields[$index]['value'] = $value;
            $result[] = [
                'field'      => $fieldName,
                'label'      => (string)($configFields[$index]['label'] ?? $fieldName),
                'old_value'  => SensitiveData::mask($oldValue, $fieldName, (string)($configFields[$index]['type'] ?? '')),
                'new_value'  => SensitiveData::mask($value, $fieldName, (string)($configFields[$index]['type'] ?? '')),
            ];
        }

        $this->dao->update((int)$driver->id, ['config_fields' => $configFields]);
        return $result;
    }
}
