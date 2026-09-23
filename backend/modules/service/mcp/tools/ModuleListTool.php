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

use crmeb\interfaces\McpToolInterface;
use think\facade\Db;

/**
 * MCP 工具：已安装模块清单
 *
 * 查询系统模块安装日志（模块框架基础数据），返回已安装模块及启用状态。
 * 供 AI 判断「功能所属模块是否已安装」：未安装的模块在后台不存在对应
 * 菜单与配置项，禁止直接给出其操作路径。
 */
class ModuleListTool implements McpToolInterface
{
    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_module_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询当前系统已安装的模块（插件）清单及启用状态。回答「XX 功能怎么配置」「XX 功能的菜单在哪」等问题前必须先调用本工具：功能所属模块未安装时，后台不存在对应菜单与配置项，禁止给出该模块的操作路径，应告知用户先到插件管理安装。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '模块清单查询';
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
                'keyword' => [
                    'type'        => 'string',
                    'description' => '按模块名称或标识筛选（可选），如：分销、agent、优惠券',
                ],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword 可选）
     * @return array 已安装模块清单
     */
    public function execute(array $args): array
    {
        $keyword = trim((string)($args['keyword'] ?? ''));
        $query = Db::name('module_install_log')
            ->field('module_key,module_name,module_type,version,status,enabled');
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword) {
                $query->whereLike('module_name', "%{$keyword}%")
                    ->whereOr('module_key', 'like', "%{$keyword}%");
            });
        }
        $list = $query->order('id', 'asc')->select()->toArray();
        foreach ($list as &$module) {
            $module['status_text'] = (int)$module['status'] === 1 ? '已安装' : '已卸载';
            $module['enabled_text'] = (int)$module['enabled'] === 1 ? '启用中' : '已停用';
        }
        unset($module);

        return [
            'count'  => count($list),
            'list'   => $list,
            'notice' => '清单未列出的模块 = 当前未安装：其功能在后台不存在菜单与配置，回答时应告知用户先到「插件管理」安装，禁止编造该模块的操作路径',
        ];
    }
}
