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

use app\dao\shipping\ExpressDao;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：物流公司列表
 *
 * 返回所有启用的快递公司信息。
 */
class ExpressListTool implements McpToolInterface
{
    /**
     * 物流公司 DAO
     * @var ExpressDao
     */
    private ExpressDao $dao;

    /**
     * 构造函数
     * @param ExpressDao $dao 物流公司 DAO
     */
    public function __construct(ExpressDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_order_express_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '获取物流公司列表';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '物流公司列表';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '订单';
    }

    /**
     * 入参 Schema
     * @return array
     */
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass(),
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（无）
     * @return array 物流公司列表
     */
    public function execute(array $args): array
    {
        $list = $this->dao->search([['is_show', '=', 1]])
            ->field('id,name,code')
            ->select()
            ->toArray();
        return ['list' => $list];
    }
}
