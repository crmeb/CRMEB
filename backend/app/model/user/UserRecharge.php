<?php
declare(strict_types=1);

namespace app\model\user;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class UserRecharge extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'user_recharge';

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->bind([
            'nickname' => 'nickname',
            'avatar' => 'avatar'
        ]);
    }

    public function searchUidAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('uid', $value);
        else
            $query->where('uid', $value);
    }

    public function searchOrderIdAttr($query, $value)
    {
        $query->where('order_id', $value);
    }

    public function searchRechargeTypeAttr($query, $value)
    {
        $query->where('recharge_type', $value);
    }

    public function searchNoRechargeTypeAttr($query, $value)
    {
        $query->where('recharge_type', '<>', $value);
    }

    public function searchRefundPriceAttr($query, $value)
    {
        $query->where('refund_price', $value);
    }

    public function searchPaidAttr($query, $value)
    {
        $query->where('paid', $value);
    }

    public function searchLikeAttr($query, $value)
    {
        $query->where(function ($query) use ($value) {
            $query->whereLike('uid|order_id', "%" . $value . "%")->whereOr('uid', 'in', function ($query) use ($value) {
                $query->name('user')->whereLike('nickname', "%" . $value . "%")->field('uid')->select();
            });
        });
    }

    public function searchChannelTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('channel_type', $value);
    }
}
