<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserBrokerage;

class UserBrokerageDao extends BaseDao
{
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
        return $this->search($where)->field('uid,SUM(IF(pm=1,`number`,-`number`)) as brokerage_price')->with(['user' => function ($query) {
            $query->field('uid,avatar,nickname');
        }])->order('brokerage_price desc')->group('uid')->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    /**
     * 获取某些条件的佣金总数
     * @param array $where
     * @return mixed
     */
    public function getBrokerageSumColumn(array $where)
    {
        if (isset($where['uid']) && is_array($where['uid'])) {
            return $this->search($where)->group('uid')->column('sum(number) as num', 'uid');
        } else
            return $this->search($where)->sum('number');
    }

    /**
     * 获取某个账户下的冻结佣金
     * @param int $uid
     * @return float
     */
    public function getUserFrozenPrice(int $uid)
    {
        return $this->search(['uid' => $uid, 'status' => 1, 'pm' => 1])->where('frozen_time', '>', time())->sum('number');
    }
}
