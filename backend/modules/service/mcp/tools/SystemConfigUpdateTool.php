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

use app\dao\system\config\SystemConfigDao;
use app\services\system\config\SystemConfigServices;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;
use modules\service\mcp\SensitiveData;

/**
 * MCP 工具：修改系统配置
 *
 * 按配置变量名（menu_name）更新配置项的值，保存后清除配置缓存立即生效。
 */
class SystemConfigUpdateTool implements McpToolInterface
{
    /**
     * 系统配置 DAO
     * @var SystemConfigDao
     */
    private SystemConfigDao $dao;

    /**
     * 系统配置服务
     * @var SystemConfigServices
     */
    private SystemConfigServices $configServices;

    /**
     * 构造函数
     * @param SystemConfigDao $dao 系统配置 DAO
     * @param SystemConfigServices $configServices 系统配置服务
     */
    public function __construct(SystemConfigDao $dao, SystemConfigServices $configServices)
    {
        $this->dao = $dao;
        $this->configServices = $configServices;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_config_update';
    }

    /**
     * 交互声明：本工具会修改系统配置并立即生效，需用户确认后才真实执行
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
        return '修改系统设置（系统配置）中指定配置项的值；需传入配置变量名（menu_name，可通过 crmeb_config_list 查询）和新值，修改后立即生效';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '系统配置修改';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'required'   => ['menu_name', 'value'],
            'properties' => [
                'menu_name' => ['type' => 'string', 'description' => '配置变量名，如 site_url、store_brokerage_ratio'],
                'value'     => ['description' => '新的配置值，支持字符串/数字/布尔值/数组'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（menu_name/value）
     * @return array 修改结果（凭证类新旧值均脱敏）
     * @throws McpException 配置不存在、取值非法或参数缺失
     */
    public function execute(array $args): array
    {
        $menuName = trim((string)($args['menu_name'] ?? ''));
        if ($menuName === '') {
            throw new McpException('menu_name 不能为空');
        }
        if (!array_key_exists('value', $args)) {
            throw new McpException('value 不能为空');
        }
        $value = $args['value'];

        $config = $this->dao->getOne(['menu_name' => $menuName]);
        if (!$config) {
            throw new McpException("配置项 {$menuName} 不存在，请先通过 crmeb_config_list 查询确认变量名");
        }
        $config = is_array($config) ? $config : $config->toArray();

        // 单选/下拉/多选类型校验取值合法性，防止写入无效选项
        $this->validateOptionValue($config, $value);

        $oldValue = json_decode((string)($config['value'] ?? ''), true);
        if (!$this->configServices->setConfig($menuName, $value)) {
            throw new McpException("配置项 {$menuName} 保存失败");
        }

        return [
            'menu_name' => $menuName,
            'info'      => (string)($config['info'] ?? ''),
            'old_value' => SensitiveData::mask($oldValue, $menuName, (string)($config['input_type'] ?? '')),
            'new_value' => SensitiveData::mask($value, $menuName, (string)($config['input_type'] ?? '')),
            'message'   => '修改成功',
        ];
    }

    /**
     * 校验 radio/select/checkbox 类型配置的取值是否在参数选项内
     * @param array $config 配置记录
     * @param mixed $value 新值
     * @throws McpException 取值不在选项范围内
     */
    private function validateOptionValue(array $config, $value): void
    {
        $type = (string)($config['type'] ?? '');
        if (!in_array($type, ['radio', 'select', 'checkbox'], true)) {
            return;
        }
        $options = [];
        foreach (explode("\n", (string)($config['parameter'] ?? '')) as $line) {
            $parts = explode('=>', $line, 2);
            if (count($parts) === 2) {
                $options[trim($parts[0])] = trim($parts[1]);
            }
        }
        if (!$options) {
            return;
        }
        // checkbox 为多值，逐个校验；radio/select 为单值
        $values = $type === 'checkbox' ? (array)$value : [$value];
        foreach ($values as $val) {
            if (!is_scalar($val)) {
                throw new McpException("配置项 {$config['menu_name']} 的取值必须为字符串/数字/布尔值");
            }
            $key = (string)$val;
            if (!array_key_exists($key, $options)) {
                $allow = implode('、', array_map(fn($k, $v) => $k . '=' . $v, array_keys($options), $options));
                throw new McpException("配置项 {$config['menu_name']} 的取值 {$val} 不在可选范围内，允许值：{$allow}");
            }
        }
    }
}
