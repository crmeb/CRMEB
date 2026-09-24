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

namespace modules\service\mcp\schema;

use crmeb\services\schema\BaseSchemaPageProvider;
use modules\service\mcp\McpToolRegistry;

/**
 * MCP 工具列表页（通用页面系统示例）
 *
 * 纯 PHP 声明后台页面：访问 /admin/plugin/mcp/list 即可查看 MCP 工具列表，
 * 无需任何前端页面与路由注册。
 *
 * 数据来源：McpToolRegistry 注册的全部工具定义（含第三方插件通过
 * McpToolsRegister 事件追加的工具），列表行 id 为注册顺序下标。
 */
class ToolListPage extends BaseSchemaPageProvider
{
    /**
     * 页面标识
     * @return string
     */
    public static function pageKey(): string
    {
        return 'list';
    }

    /**
     * 页面结构配置
     * @return array
     */
    public function config(): array
    {
        return [
            'type'  => 'list',
            'title' => 'MCP 工具列表',
            'search' => [
                ['field' => 'keyword', 'label' => '关键词', 'type' => 'input', 'placeholder' => '中文名/工具名称/功能描述'],
                ['field' => 'category', 'label' => '分类', 'type' => 'select', 'placeholder' => '全部分类', 'options' => $this->categoryOptions()],
            ],
            'columns' => [
                ['prop' => 'index',  'label' => '序号', 'type' => 'text', 'width' => 70],
                ['prop' => 'name',   'label' => '工具名称', 'type' => 'text', 'width' => 220, 'sortable' => true],
                ['prop' => 'label',  'label' => '中文名称', 'type' => 'text', 'width' => 130],
                ['prop' => 'category', 'label' => '分类', 'type' => 'tag', 'width' => 100,
                 'map' => [
                     '用户' => ['theme' => 'primary'],
                     '商品' => ['theme' => 'success'],
                     '订单' => ['theme' => 'warning'],
                     '售后退款' => ['theme' => 'danger'],
                     '营销' => ['theme' => 'warning'],
                     '系统设置' => ['theme' => 'info'],
                     '消息通知' => ['theme' => 'primary'],
                     '开发支持' => ['theme' => 'success'],
                 ]],
                ['prop' => 'description', 'label' => '功能描述', 'type' => 'ellipsis'],
                ['prop' => 'params_count', 'label' => '参数数', 'type' => 'number', 'width' => 90],
                ['prop' => 'interaction', 'label' => '执行方式', 'type' => 'tag', 'width' => 110,
                 'map' => [
                     'execute' => ['label' => '写入需确认', 'theme' => 'warning'],
                     'delete'  => ['label' => '删除需确认', 'theme' => 'danger'],
                     ''        => ['label' => '直接执行', 'theme' => 'success'],
                 ]],
            ],
            'row_actions' => [
                ['action' => 'detail', 'label' => '详情', 'mode' => 'drawer_detail'],
            ],
            'default_order' => ['name' => 'asc'],
        ];
    }

    /**
     * 工具列表数据（注册中心全量定义 + 关键词过滤 + 内存分页）
     *
     * @param array $where 搜索条件（keyword）
     * @param int $page 页码
     * @param int $limit 每页条数
     * @param array $order 排序（当前仅支持 name asc）
     * @return array {list: array, count: int}
     */
    public function list(array $where, int $page, int $limit, array $order = []): array
    {
        $all = $this->loadDefinitions();
        $keyword = trim((string)($where['keyword'] ?? ''));
        if ($keyword !== '') {
            $all = array_values(array_filter($all, function (array $item) use ($keyword): bool {
                return str_contains($item['name'], $keyword)
                    || str_contains($item['label'], $keyword)
                    || str_contains($item['description'], $keyword);
            }));
        }
        // 分类筛选：选项与行数据的 category 同源（未声明分类归入「其他」）
        $category = trim((string)($where['category'] ?? ''));
        if ($category !== '') {
            $all = array_values(array_filter($all, fn(array $item) => (string)$item['category'] === $category));
        }

        // 列排序（当前支持 name 列升/降序，保持分页结果稳定；默认注册顺序）
        $nameOrder = (string)($order['name'] ?? '');
        if ($nameOrder !== '') {
            usort($all, fn(array $a, array $b) => $nameOrder === 'desc'
                ? strcmp($b['name'], $a['name'])
                : strcmp($a['name'], $b['name']));
        }

        $count = count($all);
        $rows = array_slice($all, ($page - 1) * $limit, $limit);
        return ['list' => $rows, 'count' => $count];
    }

    /**
     * 工具详情（按注册顺序下标定位）
     *
     * @param int $id 注册顺序下标（列表行 id）
     * @return array {fields: array}
     */
    public function detail(int $id): array
    {
        $all = $this->loadDefinitions();
        if (!isset($all[$id])) {
            throw new \crmeb\exceptions\AdminException('工具不存在');
        }
        $tool = $all[$id];
        $fields = [
            ['label' => '工具名称', 'value' => $tool['name']],
            ['label' => '中文名称', 'value' => $tool['label']],
            ['label' => '分类', 'value' => $tool['category']],
            ['label' => '功能描述', 'value' => $tool['description']],
            ['label' => '执行方式', 'value' => $tool['interaction'], 'type' => 'tag',
             'map' => [
                 'execute' => ['label' => '写入需确认', 'theme' => 'warning'],
                 'delete'  => ['label' => '删除需确认', 'theme' => 'danger'],
                 ''        => ['label' => '直接执行', 'theme' => 'success'],
             ]],
        ];
        // 入参 Schema 格式化展示
        $schemaJson = json_encode($tool['inputSchema'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $fields[] = ['label' => '入参 Schema', 'value' => $schemaJson, 'type' => 'json'];
        return ['fields' => $fields];
    }

    /**
     * 加载全部工具定义（内置工具注册到注册中心后取全量定义，幂等）
     *
     * @return array 行数据列表（id=注册顺序下标/index/name/label/category/description/params_count/interaction/inputSchema）
     */
    protected function loadDefinitions(): array
    {
        $registry = McpToolRegistry::booted();

        $rows = [];
        foreach ($registry->allDefinitions() as $index => $definition) {
            // 未声明分类（含第三方插件工具）归入「其他」，与筛选选项、权限勾选分组口径一致
            $category = trim((string)($definition['category'] ?? ''));
            $rows[] = [
                // 行 id = 注册顺序下标（同一部署内注册顺序确定，详情按下标回查；
                // 前端行动作固定取 row.id，漏传会导致详情恒落在第 0 条）
                'id'          => $index,
                'index'       => $index,
                'name'        => (string)($definition['name'] ?? ''),
                'label'       => (string)($definition['label'] ?? ''),
                'category'    => $category !== '' ? $category : '其他',
                'description' => (string)($definition['description'] ?? ''),
                'params_count' => count((array)($definition['inputSchema']['properties'] ?? [])),
                'interaction' => (string)($definition['interaction'] ?? ''),
                'inputSchema' => (array)($definition['inputSchema'] ?? []),
            ];
        }
        return $rows;
    }

    /**
     * 分类筛选选项（注册中心约定顺序 + 「其他」兜底）
     *
     * @return array {label, value} 选项列表
     */
    private function categoryOptions(): array
    {
        $categories = array_merge(
            McpToolRegistry::orderedCategories(McpToolRegistry::booted()->allDefinitions()),
            ['其他']
        );
        return array_map(fn(string $category) => ['label' => $category, 'value' => $category], $categories);
    }
}
