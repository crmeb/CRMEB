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

use app\dao\user\UserRechargeDao;
use crmeb\interfaces\McpToolInterface;

/**
 * MCP 工具：充值记录列表
 *
 * 查询用户余额充值记录。
 */
class RechargeListTool implements McpToolInterface
{
    /**
     * 充值 DAO
     * @var UserRechargeDao
     */
    private UserRechargeDao $dao;

    /**
     * 构造函数
     * @param UserRechargeDao $dao 充值 DAO
     */
    public function __construct(UserRechargeDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 工具名称
     * @return string
     */
    public function name(): string
    {
        return 'crmeb_recharge_list';
    }

    /**
     * 工具描述
     * @return string
     */
    public function description(): string
    {
        return '查询用户余额充值记录列表，支持按支付状态、充值（支付）方式、用户 UID 筛选，'
            . '返回充值订单号、充值金额、赠送金额、退款金额与支付时间；'
            . 'paid：1 已支付、0 未支付；recharge_type 为实际支付渠道：weixin 微信、alipay 支付宝、balance 余额。';
    }

    /**
     * 工具中文名（管理后台展示）
     * @return string
     */
    public function label(): string
    {
        return '充值记录列表';
    }

    /**
     * 工具分类（管理后台分组展示）
     * @return string
     */
    public function category(): string
    {
        return '财务';
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
                'page'          => ['type' => 'number', 'description' => '页码'],
                'limit'         => ['type' => 'number', 'description' => '每页数量'],
                'uid'           => ['type' => 'number', 'description' => '用户 UID'],
                'paid'          => ['type' => 'number', 'description' => '支付状态：1 已支付、0 未支付'],
                'recharge_type' => ['type' => 'string', 'description' => '充值（支付）方式：weixin/alipay/balance'],
            ],
        ];
    }

    /**
     * 执行工具
     *
     * @param array $args 参数（page/limit/uid/paid/recharge_type）
     * @return array 充值记录列表和总数
     */
    public function execute(array $args): array
    {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = min(100, max(1, (int)($args['limit'] ?? 10)));

        $where = [];
        if (!empty($args['uid'])) {
            $where[] = ['uid', '=', (int)$args['uid']];
        }
        if (isset($args['paid']) && $args['paid'] !== '') {
            $where[] = ['paid', '=', (int)$args['paid'] == 1 ? 1 : 0];
        }
        if (isset($args['recharge_type']) && $args['recharge_type'] !== '') {
            $where[] = ['recharge_type', '=', (string)$args['recharge_type']];
        }

        $list = $this->dao->search($where)
            ->field('id,uid,order_id,price,give_price,recharge_type,paid,pay_time,refund_price,channel_type,add_time')
            ->order('id desc')
            ->page($page, $limit)
            ->select()
            ->toArray();

        $count = $this->dao->search($where)->count();
        return ['list' => $list, 'count' => $count];
    }
}
