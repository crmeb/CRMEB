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
use app\model\user\UserBrokerageFrozen;

/**
 * 佣金冻结
 * Class UserBrokerageFrozenDao
 * @package app\dao\user
 */
class UserBrokerageFrozenDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserBrokerageFrozen::class;
    }

    /**
     * 搜索
     * @param array $where
     * @return \crmeb\basic\BaseModel|mixed|\think\Model
     */
    public function search(array $where = [])
    {
        return parent::search($where)->when(isset($where['isFrozen']), function ($query) use ($where) {
            if ($where['isFrozen']) {
                $query->where('frozen_time', '>', time());
            } else {
                $query->where('frozen_time', '<=', time());
            }
        });
    }

    /**
     * 获取某个账户下的冻结佣金
     * @param int $uid
     * @param bool $isFrozen 获取冻结之前或者冻结之后的总金额
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserFrozenPrice(int $uid, bool $isFrozen = true)
    {
        return $this->search(['uid' => $uid, 'status' => 1, 'isFrozen' => $isFrozen])->column('price', 'id');
    }

    /**
     * 修改佣金冻结状态
     * @param string $orderId
     * @return \crmeb\basic\BaseModel
     */
    public function updateFrozen(string $orderId)
    {
        return $this->search(['order_id' => $orderId, 'isFrozen' => true])->update(['status' => 0]);
    }

    /**
     * 获取用户的冻结佣金数组
     * @return mixed
     */
    public function getFrozenBrokerage()
    {
        return $this->getModel()->where('frozen_time', '>', time())
            ->where('status', 1)
            ->group('uid')
            ->column('SUM(price) as sum_price', 'uid');
    }

    /**
     * @param $uids
     * @return float
     */
    public function getSumFrozenBrokerage($uids)
    {
        return $this->getModel()->whereIn('uid', $uids)->where('frozen_time', '>', time())->sum('price');
    }
}
