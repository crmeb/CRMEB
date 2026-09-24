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
use crmeb\plugin\ModuleStateChecker;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：驱动检查（查询第三方驱动的安装与配置状态）
 *
 * 读取统一驱动字典（eb_driver）与驱动插件生命周期状态，输出每个驱动的
 * 启用开关、所属插件是否可用、配置字段填写情况（敏感字段脱敏，仅返回
 * 是否已填写）与缺失的必填项，用于诊断"短信/邮件/存储等能力不可用"的
 * 原因（驱动未启用 / 插件被禁用 / 密钥未配置）。
 */
class DriverListTool implements McpToolInterface
{
    /**
     * 配置值实际存于系统配置（sys_config）而非驱动表的特例字段
     * 一号通的 appid/appsecret 由系统全局配置统一管理，驱动表中 value 恒为空
     */
    private const SYS_CONFIG_FIELDS = ['yihaotong_appid', 'yihaotong_appsecret'];

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
        return 'crmeb_driver_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询第三方驱动（短信 sms、邮件 email、云存储 upload、机器人 bot、物流 logistics、电子面单 express、'
            . '电子发票 invoice、小票打印 printer、商品采集 copyproduct）的安装与配置状态。'
            . '返回每个驱动的启用开关、所属驱动插件是否可用、配置字段填写情况（密码类字段脱敏，仅显示是否已填写）'
            . '与缺失的必填项。诊断某项能力（如发短信）不可用时优先调用本工具定位原因；'
            . '修改配置请使用 crmeb_driver_config_update';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '驱动列表';
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
            'properties' => [
                'driver_type' => [
                    'type'        => 'string',
                    'description' => '驱动类型筛选：sms=短信 email=邮件 upload=云存储 bot=机器人 logistics=物流 '
                        . 'express=电子面单 invoice=电子发票 printer=小票打印 copyproduct=商品采集，不传返回全部类型',
                ],
                'driver_key'  => [
                    'type'        => 'string',
                    'description' => '驱动标识筛选（如 yihaotong、aliyun），需配合 driver_type 使用，不传返回该类型全部驱动',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（driver_type/driver_key 均可选）
     * @return array 驱动检查结果列表（含状态汇总）
     * @throws McpException 驱动类型或标识不存在
     */
    public function execute(array $args): array
    {
        $type = strtolower(trim((string)($args['driver_type'] ?? '')));
        $key = trim((string)($args['driver_key'] ?? ''));

        $rows = $this->dao->getList($type);
        if ($rows === []) {
            throw new McpException($type === ''
                ? '系统中未安装任何驱动插件'
                : "驱动类型 {$type} 下未安装任何驱动");
        }

        $list = [];
        foreach ($rows as $row) {
            if ($key !== '' && (string)$row['driver_key'] !== $key) {
                continue;
            }
            $list[] = $this->formatDriver($row);
        }
        if ($list === []) {
            throw new McpException("驱动 {$type}/{$key} 不存在，请先去掉 driver_key 参数查看该类型已安装的驱动列表");
        }

        return [
            'count' => count($list),
            'list'  => $list,
        ];
    }

    /**
     * 格式化单个驱动的检查结果
     * @param array $row eb_driver 记录（config_fields 已解码为数组）
     * @return array 检查结果（基础信息 + 插件状态 + 配置填写情况）
     */
    private function formatDriver(array $row): array
    {
        $pluginId = (string)($row['owner_plugin_id'] ?? '');
        $pluginActive = $pluginId !== '' && ModuleStateChecker::isDriverActive($pluginId);
        $enabled = (int)($row['status'] ?? 0) === 1;

        $fields = $this->formatConfigFields($row);
        $missing = [];
        foreach ($fields as $field) {
            if (!empty($field['required']) && empty($field['has_value'])) {
                $missing[] = $field['field'];
            }
        }

        return [
            'driver_type'   => (string)$row['driver_type'],
            'driver_key'    => (string)$row['driver_key'],
            'driver_name'   => (string)($row['driver_name'] ?? ''),
            'description'   => (string)($row['description'] ?? ''),
            'status'        => $enabled ? 1 : 0,
            'status_text'   => $enabled ? '已启用' : '已禁用',
            'plugin_active' => $pluginActive,
            'available'     => $enabled && $pluginActive,
            'available_text'=> $enabled && $pluginActive ? '可用' : ($pluginActive ? '驱动未启用' : '驱动插件被禁用或未安装'),
            'config_fields' => $fields,
            'missing_required' => $missing,
        ];
    }

    /**
     * 格式化配置字段定义与填写情况（敏感字段脱敏）
     * @param array $row eb_driver 记录
     * @return array 字段列表（field/label/type/required/sensitive/has_value/value）
     */
    private function formatConfigFields(array $row): array
    {
        $configFields = is_array($row['config_fields'] ?? null) ? $row['config_fields'] : [];
        $fields = [];
        foreach ($configFields as $item) {
            $fieldName = (string)($item['field'] ?? '');
            if ($fieldName === '') {
                continue;
            }
            $sensitive = $this->isSensitiveField($fieldName, (string)($item['type'] ?? ''));
            $value = array_key_exists('value', $item) ? $item['value'] : null;
            // 一号通凭证存于系统配置，从 sys_config 读取后同样脱敏
            if ($value === null && (string)$row['driver_key'] === 'yihaotong'
                && in_array($fieldName, self::SYS_CONFIG_FIELDS, true)) {
                $value = sys_config($fieldName, '');
            }
            $hasValue = $value !== null && $value !== '';
            $fields[] = [
                'field'      => $fieldName,
                'label'      => (string)($item['label'] ?? $fieldName),
                'type'       => (string)($item['type'] ?? 'text'),
                'required'   => !empty($item['required']),
                'sensitive'  => $sensitive,
                'has_value'  => $hasValue,
                // 敏感字段已填写时回传掩码占位（明确表达「已配置、内容隐藏」，防止模型把 null 误判为未配置），未填写回传 null；非敏感字段回传明文（防上下文与日志泄露）
                'value'      => $sensitive ? ($hasValue ? '******' : null) : $value,
            ];
        }
        return $fields;
    }

    /**
     * 判断是否为敏感配置字段（密码类类型或凭证类字段名）
     * @param string $fieldName 字段名
     * @param string $fieldType 字段类型（password 表示密码输入框）
     * @return bool
     */
    private function isSensitiveField(string $fieldName, string $fieldType): bool
    {
        return SensitiveData::isCredential($fieldName, $fieldType);
    }
}
