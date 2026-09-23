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

namespace app\dao\user;

use crmeb\basic\BaseDao;
use app\model\user\UserBrokerage;

/**
 * 用户佣金持久化查询。
 */
class UserBrokerageDao extends BaseDao
{
    /**
     * 按收款人汇总订单佣金及退款冲正，不混入提现流水。
     * @param int $orderId 中台订单ID
     * @return array
     */
    public function getOrderBrokerageRewards(int $orderId): array
    {
        return $this->getModel()->where('link_id', $orderId)->where('status', 1)
            ->whereIn('type', ['self_brokerage', 'one_brokerage', 'two_brokerage', 'staff_brokerage',
                'agent_brokerage', 'division_brokerage', 'pink_master_brokerage', 'refund', 'brokerage_refund'])
            ->field('uid, pm, SUM(number) AS number')->group('uid, pm')->order('uid asc')->select()->toArray();
    }

    /**
     * 退款后解除本单原佣金冻结，避免冻结额超过冲正后余额。
     * @param int $orderId 中台订单ID
     * @return void
     */
    public function releaseOrderRewardFreeze(int $orderId): void
    {
        $this->getModel()->where('link_id', $orderId)->where('pm', 1)
            ->whereIn('type', ['self_brokerage', 'one_brokerage', 'two_brokerage', 'staff_brokerage',
                'agent_brokerage', 'division_brokerage', 'pink_master_brokerage'])
            ->update(['frozen_time' => 0]);
    }
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserBrokerage::class;
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @param array $typeWhere
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page = 0, int $limit = 0, array $typeWhere = [])
    {
        return $this->search($where)->when(count($typeWhere) > 0, function ($query) use ($typeWhere) {
            $query->where($typeWhere);
        })->field($field)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('id desc')->select()->toArray();
    }

    /**
     * 查询列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserBrokerageList(array $where)
    {
        return $this->search($where)->select()->toArray();
    }

    /**
     * 获取佣金排行
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function brokerageRankList(array $where, int $page = 0, int $limit = 0)
    {
        //SUM(IF(pm=1,`number`,-`number`))
        if ($where['pm'] == 1) $where['not_type'] = ['extract_fail'];
        return $this->search($where)->field('uid,SUM(number) as brokerage_price')->with(['user' => function ($query) {
            $query->field('uid,avatar,nickname');
        }])->order('brokerage_price desc')->group('uid')->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    /**
     * 获取某些条件的佣金总数
     * @param array $where
     * @return mixed
     * @throws \ReflectionException
     */
    public function getBrokerageSumColumn(array $where)
    {
        if ($where['pm'] == 1) $where['not_type'] = ['extract_fail'];
        if (isset($where['uid']) && is_array($where['uid'])) {
            return $this->search($where)->group('uid')->column('sum(number) as num', 'uid');
        } else
            return $this->search($where)->sum('number');
    }

    /**
     * 获取某个账户下的冻结佣金
     * @param int $uid
     * @return float
     * @throws \ReflectionException
     */
    public function getUserFrozenPrice(int $uid)
    {
        return $this->search(['uid' => $uid, 'status' => 1, 'pm' => 1])->where('frozen_time', '>', time())->sum('number');
    }
}
