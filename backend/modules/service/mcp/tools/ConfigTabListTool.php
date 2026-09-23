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

use app\dao\system\config\SystemConfigTabDao;
use crmeb\interfaces\McpToolInterface;
use think\facade\Db;

/**
 * MCP 工具：查询系统设置的配置分组目录。
 *
 * 回答「系统有哪些设置/配置分组」类概览问题时调用，返回每个分组的
 * 名称、所在菜单面包屑、可点击链接与分组内配置项数量；查看分组下
 * 各配置项明细时用返回的 tab_id 调用 SystemConfigListTool。
 */
class ConfigTabListTool implements McpToolInterface
{
    /**
     * 管理后台路由前缀
     *
     * 与前端部署约定耦合（非系统配置项）：frontend/admin/src/setting.js 的
     * routePre 与 vite.config.js 的 base 均为 /admin；若调整前端前缀需同步修改此处。
     * @var string
     */
    private const ADMIN_ROUTE_PREFIX = '/admin';

    /** 面包屑回溯最大层级（防止脏数据成环导致死循环） */
    private const MAX_DEPTH = 8;

    /**
     * 配置分组 DAO
     * @var SystemConfigTabDao
     */
    private SystemConfigTabDao $dao;

    /**
     * 构造函数
     * @param SystemConfigTabDao $dao 配置分组 DAO
     */
    public function __construct(SystemConfigTabDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_config_tab_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询系统设置的配置分组目录：返回每个配置分组的名称（title）、所在菜单面包屑（breadcrumb）、可点击链接（url，已含管理后台 /admin 前缀）与分组内配置项数量（config_count）。回答「系统有哪些设置」「有哪些配置分组」类概览问题时调用本工具；本工具只提供分组目录与入口，回答「哪些配置值得优化」「配置现状分析」等需要给出具体结论的问题时，必须继续用返回的 tab_id 调用 crmeb_config_list 拉取配置项明细（含当前值）后再作答，不得以目录作为最终答案';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '配置分组目录';
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
                'keyword' => ['type' => 'string', 'description' => '按分组名称关键词搜索，如：签到、支付、LOGO；留空返回全部分组'],
                'page'    => ['type' => 'number', 'description' => '页码，默认1'],
                'limit'   => ['type' => 'number', 'description' => '每页数量，默认20，最大100'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（keyword/page/limit）
     * @return array 配置分组列表（含面包屑、链接与配置项数量）和总数
     */
    public function execute(array $args): array
    {
        $keyword = trim((string)($args['keyword'] ?? ''));
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 20)));

        // 仅返回启用（status=1）的配置分组；关键词按分组名模糊匹配（转义通配符防止注入）
        $where = ['status' => 1];
        if ($keyword !== '') {
            $where['title'] = addcslashes($keyword, '%_');
        }

        $list = $this->dao->search($where)
            ->field('id,title,menus_id')
            ->order('sort desc,id asc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        $count = $this->dao->search($where)->count();

        // 一次分组统计各分组的配置项数量
        $counts = [];
        $tabIds = array_column($list, 'id');
        if ($tabIds !== []) {
            $counts = Db::name('system_config')->whereIn('config_tab_id', $tabIds)
                ->field('config_tab_id, COUNT(*) AS cnt')
                ->group('config_tab_id')
                ->select()
                ->toArray();
            $counts = array_column($counts, 'cnt', 'config_tab_id');
        }

        // 全量菜单映射一次取出，用分组绑定的 menus_id 回溯生成菜单面包屑
        $parents = [];
        $all = Db::name('system_menus')->where('is_del', 0)
            ->field('id,pid,menu_name')
            ->select()
            ->toArray();
        foreach ($all as $row) {
            $parents[(int)$row['id']] = ['pid' => (int)$row['pid'], 'name' => (string)$row['menu_name']];
        }

        foreach ($list as &$item) {
            $tabId = (int)$item['id'];
            $menuId = (int)$item['menus_id'];
            $item['tab_id'] = $tabId;
            $item['breadcrumb'] = isset($parents[$menuId]) ? $this->buildBreadcrumb($menuId, $parents) : '';
            // 系统配置分组的后台路由统一为 /setting/system_config/{tab_id}，无需依赖菜单是否存在
            $item['url'] = self::ADMIN_ROUTE_PREFIX . '/setting/system_config/' . $tabId;
            $item['config_count'] = (int)($counts[$tabId] ?? 0);
            unset($item['id'], $item['menus_id']);
        }
        unset($item);

        return [
            'count'  => $count,
            'page'   => $page,
            'limit'  => $limit,
            'list'   => $list,
            'notice' => '用 url 字段生成 Markdown 链接（已含管理后台 /admin 前缀），格式：[分组名](url)；用 tab_id 调用 crmeb_config_list 可查看该分组下配置项明细；breadcrumb 为空表示该分组未生成后台菜单，此时文字路径只写「系统配置分组：分组名」',
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
