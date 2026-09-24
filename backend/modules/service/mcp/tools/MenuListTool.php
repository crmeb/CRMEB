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
 * MCP 工具：查询管理后台菜单位置。
 *
 * 回答「XX 功能的菜单在哪 / 去哪个页面操作」类问题时，模型用关键词
 * 检索后台菜单，返回菜单名称、面包屑层级与可点击的前端路由（menu_path），
 * 避免凭记忆编造菜单路径（配合 SystemConfigListTool / ModuleListTool 使用）。
 */
class MenuListTool implements McpToolInterface
{
    /**
     * 面包屑回溯最大层级（防止脏数据成环导致死循环）
     * @var int
     */
    private const MAX_DEPTH = 8;

    /**
     * 管理后台路由前缀
     *
     * 与前端部署约定耦合（非系统配置项）：frontend/admin/src/setting.js 的
     * routePre 与 vite.config.js 的 base 均为 /admin；若调整前端前缀需同步修改此处。
     * @var string
     */
    private const ADMIN_ROUTE_PREFIX = '/admin';

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_menu_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询管理后台菜单位置与结构。回答「XX 功能的菜单在哪」「去哪个页面操作」类问题时必须先用关键词检索：返回菜单名称、所在层级面包屑与可点击链接（url）；回答「后台有哪些菜单」「设置下面有什么」类概览问题时留空关键词按 pid 逐级浏览（type=dir 为目录节点，用其 id 下钻）；禁止凭记忆编造菜单路径';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '后台菜单查询';
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
                'keyword' => ['type' => 'string', 'description' => '按菜单名称关键词搜索，如：优惠券、分销、系统配置；留空时按 pid 浏览子菜单'],
                'pid'     => ['type' => 'number', 'description' => '父级菜单ID（来源为本工具返回的 id），keyword 留空时生效；默认0浏览顶级菜单，对返回 type=dir 的目录项用其 id 作为 pid 继续下钻'],
                'page'    => ['type' => 'number', 'description' => '页码，默认1'],
                'limit'   => ['type' => 'number', 'description' => '每页数量，默认20，最大50'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword/page/limit）
     * @return array 菜单列表（含面包屑与前端路由）和总数
     */
    public function execute(array $args): array
    {
        $keyword = trim((string)($args['keyword'] ?? ''));
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(50, max(1, (int)($args['limit'] ?? 20)));

        // 仅查未删除、后台可见的菜单，按钮等权限节点不返回
        $where = [['is_del', '=', 0], ['is_show', '=', 1]];
        if ($keyword !== '') {
            // 关键词按菜单名称模糊匹配（转义通配符防止注入），只返回真实页面
            $where[] = ['menu_path', '<>', ''];
            $where[] = ['menu_name', 'like', '%' . addcslashes($keyword, '%_') . '%'];
        } else {
            // 无关键词时按父级浏览菜单结构（默认顶级），目录与页面一并返回供逐级下钻
            $where[] = ['pid', '=', max(0, (int)($args['pid'] ?? 0))];
        }

        $list = Db::name('system_menus')->where($where)
            ->field('id,menu_name,menu_path')
            ->order('sort desc,id asc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        $count = Db::name('system_menus')->where($where)->count();

        // 全量菜单映射一次取出，用于逐级回溯父级生成面包屑
        $parents = [];
        $all = Db::name('system_menus')->where('is_del', 0)
            ->field('id,pid,menu_name')
            ->select()
            ->toArray();
        foreach ($all as $row) {
            $parents[(int)$row['id']] = ['pid' => (int)$row['pid'], 'name' => (string)$row['menu_name']];
        }

        foreach ($list as &$item) {
            $item['breadcrumb'] = $this->buildBreadcrumb((int)$item['id'], $parents);
            // 目录（menu_path 为空的纯分组节点）没有可点击页面，type=dir 提示模型用其 id 作为 pid 继续下钻
            $isPage = (string)$item['menu_path'] !== '';
            $item['type'] = $isPage ? 'page' : 'dir';
            // 管理后台完整可点击路径；归一化补前导斜杠，防御历史脏数据（如曾出现 menu_path=system/database/index）
            $item['url'] = $isPage ? self::ADMIN_ROUTE_PREFIX . '/' . ltrim((string)$item['menu_path'], '/') : '';
        }
        unset($item);

        return [
            'count'  => $count,
            'page'   => $page,
            'limit'  => $limit,
            'list'   => $list,
            'notice' => 'type=page 的项用 url 字段生成 Markdown 链接（已含管理后台 /admin 前缀），格式：[菜单名](url)；type=dir 是目录节点无链接，用其 id 作为 pid 参数继续下钻浏览子菜单；查无结果时换更短关键词重试（如「系统设置」→「设置」），仍未找到则如实告知，不要编造路径',
        ];
    }

    /**
     * 由 pid 链逐级回溯生成面包屑（一级 → 二级 → 三级）
     * @param int $id 菜单 ID
     * @param array $parents 全量菜单映射（id => pid/name）
     * @return string 面包屑文本
     */
    private function buildBreadcrumb(int $id, array $parents): string
    {
        $chain = [];
        $current = $id;
        for ($depth = 0; $depth < self::MAX_DEPTH && isset($parents[$current]); $depth++) {
            $chain[] = $parents[$current]['name'];
            $pid = $parents[$current]['pid'];
            // 回溯到顶级或父级缺失（被删/越权数据）时停止
            if ($pid === 0 || !isset($parents[$pid])) {
                break;
            }
            $current = $pid;
        }
        return implode(' → ', array_reverse($chain));
    }
}
