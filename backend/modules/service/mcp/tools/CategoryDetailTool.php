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
 * MCP 工具：商品分类详情
 */
class CategoryDetailTool implements McpToolInterface
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
        return 'crmeb_category_detail';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '按分类ID查询单个商品分类详情，返回该分类完整字段（名称、上级分类、排序等）；'
            . '需要浏览分类树或按父级筛选时用 crmeb_category_list（仅返回上架分类）。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '分类详情';
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
                'id' => ['type' => 'number', 'description' => '分类ID'],
            ],
            'required'   => ['id'],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（id）
     * @return array 分类详情
     * @throws McpException 参数错误或分类不存在
     */
    public function execute(array $args): array
    {
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            throw new McpException('参数错误：缺少 id 或格式不正确');
        }
        $info = $this->dao->getOne(['id' => (int)$args['id']]);
        if (!$info) {
            throw new McpException('分类不存在');
        }
        return is_object($info) ? $info->toArray() : (array)$info;
    }
}
