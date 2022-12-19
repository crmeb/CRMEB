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

namespace app\model\user;

use app\model\order\StoreOrder;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

class UserBrokerage extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_brokerage';

    /**
     * 关联订单表
     * @return UserBill|model\relation\HasOne
     */
    public function order()
    {
        return $this->hasOne(StoreOrder::class, 'id', 'link_id')->field(['id', 'total_num'])->bind(['total_num']);
    }

    /**
     * 关联用户
     * @return model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

    /**
     * 用户uid
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value))
                $query->whereIn('uid', $value);
            else
                $query->where('uid', $value);
        }
    }

    /**
     * 关联id
     * @param Model $query
     * @param $value
     */
    public function searchLinkIdAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('link_id', $value);
        else
            $query->where('link_id', $value);
    }

    /**
     * 支出|获得
     * @param Model $query
     * @param $value
     */
    public function searchPmAttr($query, $value)
    {
        if ($value !== '') $query->where('pm', $value);
    }


    /**
     * 类型
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('type', $value);
        else
            $query->where('type', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchNotTypeAttr($query, $value)
    {
        if (is_array($value))
            $query->whereNotIn('type', $value);
        else
            $query->where('type', '<>', $value);
    }

    /**
     * 状态 0：带确定 1：有效 -1：无效
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 是否收货 0：未收货 1：已收货
     * @param Model $query
     * @param $value
     */
    public function searchTakeAttr($query, $value)
    {
        $query->where('take', $value);
    }

    /**
     * 模糊搜索
     * @param Model $query
     * @param $value
     */
    public function searchLikeAttr($query, $value)
    {
        $query->where(function ($query) use ($value) {
            $query->where('uid|title', 'like', "%$value%")->whereOr('uid', 'in', function ($query) use ($value) {
                $query->name('user')->whereLike('uid|account|nickname|phone', '%' . $value . '%')->field('uid')->select();
            });
        });
    }

    /**
     * 时间
     * @param Model $query
     * @param $value
     */
    public function searchAddTimeAttr($query, $value)
    {
        if (is_string($value)) $query->whereTime($query, $value);
        if (is_array($value) && count($value) == 2) $query->whereTime('add_time', 'between', $value);
    }
}
