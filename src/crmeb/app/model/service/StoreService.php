<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\service;


use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 客服
 * Class StoreService
 * @package app\model\service
 */
class StoreService extends BaseModel
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
    protected $name = 'store_service';

    /**
     * @var bool
     */
    protected $updateTime = false;


    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i:s', $value);
        return $value;
    }

    /**
     * 用户名一对多关联
     * @return mixed
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field(['uid', 'nickname'])->bind([
            'nickname' => 'nickname'
        ]);
    }

    /**
     * uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        $query->where('uid', $value);
    }

    /**
     * status搜索器
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * account搜索器
     * @param Model $query
     * @param $value
     */
    public function searchAccountAttr($query, $value)
    {
        $query->where('account', $value);
    }

    /**
     * phone搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPhoneAttr($query, $value)
    {
        $query->where('phone', $value);
    }

    /**
     * customer
     * @param Model $query
     * @param $value
     */
    public function searchCustomerAttr($query, $value)
    {
        $query->where('customer', $value);
    }

    /**
     * 用户昵称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNicknameAttr($query, $value)
    {
        $query->whereLike('nickname', '%' . $value . '%');
    }

    /**
     * 用户uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchNoUidAttr($query, $value)
    {
        if ($value) $query->whereNotIn('uid', $value);
    }
}
