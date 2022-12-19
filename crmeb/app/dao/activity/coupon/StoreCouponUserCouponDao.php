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

namespace app\dao\activity\coupon;


use app\dao\BaseDao;
use app\model\activity\coupon\StoreCoupon;
use app\model\activity\coupon\StoreCouponUser;

/**
 * Class StoreCouponUserCouponDao
 * @package app\dao\coupon
 */
class StoreCouponUserCouponDao extends BaseDao
{
    /**
     * 主表别名
     * @var string
     */
    protected $alias = 'a';

    /**
     * 连表别名
     * @var string
     */
    protected $joinAlis = 'b';

    /**
     * 主表模型
     * @return string
     */
    public function setModel(): string
    {
        return StoreCouponUser::class;
    }

    /**
     * 连表表明
     * @return string
     */
    public function setJoinModel(): string
    {
        return StoreCoupon::class;
    }

    /**
     * 设置模型
     * @return \crmeb\basic\BaseModel
     */
    public function getModel()
    {
        /** @var StoreCoupon $joinModel */
        $joinModel = app()->make($this->setJoinModel());
        $name = $joinModel->getName();
        return parent::getModel()->alias($this->alias)->join($name . ' ' . $this->joinAlis, $this->joinAlis . '.id=' . $this->alias . '.cid');
    }

    /**
     * 根据下单金额获取用户能使用的优惠卷
     * @param int $uid
     * @param string $truePrice
     * @param int $productId
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUidCouponList(int $uid, string $truePrice, int $productId)
    {
        return $this->getModel()
            ->where($this->alias . '.uid', $uid)
            ->where($this->alias . '.is_fail', 0)
            ->where($this->alias . '.status', 0)
            ->where($this->alias . '.use_min_price', '<=', $truePrice)
            ->whereFindinSet($this->joinAlis . '.product_id', $productId)
            ->where($this->joinAlis . '.type', 2)
            ->field($this->alias . '.*,' . $this->joinAlis . '.type')
            ->order($this->alias . '.coupon_price', 'DESC')
            ->select()
            ->hidden(['status', 'is_fail'])
            ->toArray();
    }

    /**
     * 获取购买金额最小使用范围内的优惠卷
     * @param $uid
     * @param $price
     * @param $value
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUidCouponMinList($uid, $price, $value = '', int $type = 1)
    {
        return $this->getModel()->where($this->alias . '.uid', $uid)
            ->where($this->alias . '.is_fail', 0)
            ->where($this->alias . '.status', 0)
            ->where($this->alias . '.use_min_price', '<=', $price)
            ->when($value, function ($query) use ($value) {
                $query->whereFindinSet($this->joinAlis . '.category_id', $value);
            })
            ->where($this->joinAlis . '.type', $type)
            ->field($this->alias . '.*,' . $this->joinAlis . '.type')
            ->order($this->alias . '.coupon_price', 'DESC')
            ->select()
            ->hidden(['status', 'is_fail'])
            ->toArray();
    }
}
