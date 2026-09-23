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

use app\dao\product\product\StoreCategoryDao;
use modules\service\mcp\McpException;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：商品分类列表
 *
 * 支持树形结构与分页两种返回模式，可按父级 ID 过滤。
 */
class CategoryListTool implements McpToolInterface
{
    /**
     * 商品分类 DAO
     * @var StoreCategoryDao
     */
    private StoreCategoryDao $dao;

    /**
     * 构造函数
     * @param StoreCategoryDao $dao 商品分类 DAO
     */
    public function __construct(StoreCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_category_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '获取商品分类列表，支持树形结构展示';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '分类列表';
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
                'page'  => ['type' => 'number', 'description' => '页码（非树形模式时有效）'],
                'limit' => ['type' => 'number', 'description' => '每页数量（非树形模式时有效）'],
                'tree'  => ['type' => 'boolean', 'description' => '是否返回树形结构，默认为true'],
                'pid'   => ['type' => 'number', 'description' => '父级ID，指定则只返回该父级下的分类'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/tree/pid）
     * @return array 分类列表和总数
     * @throws McpException 分类不存在
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));
        $isTree = $args['tree'] ?? true;
        $pid = $args['pid'] ?? null;

        // 树形模式：取全量分类后按指定父级构建树
        if ($isTree) {
            $allList = $this->dao->search([['is_show', '=', 1]])
                ->order('sort desc, id desc')
                ->select()
                ->toArray();
            $rootPid = $pid !== null ? (int)$pid : 0;
            $tree = $this->buildTree($allList, $rootPid);
            return ['list' => $tree, 'count' => count($tree)];
        }

        // 普通分页模式
        $where = [['is_show', '=', 1]];
        if ($pid !== null) {
            $where[] = ['pid', '=', (int)$pid];
        }
        $list = $this->dao->search($where)
            ->order('sort desc, id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();
        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 递归构建分类树
     *
     * @param array $list 全量分类数据
     * @param int   $pid  父级 ID
     * @return array 树形结构
     */
    private function buildTree(array $list, int $pid): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int)$item['pid'] === $pid) {
                $children = $this->buildTree($list, (int)$item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
