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


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

class MemberCard extends BaseModel
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
    protected $name = 'member_card';

    protected $insert = ['add_time', 'update_time'];

    protected $hidden = ['update_time', 'add_time'];

    protected $updateTime = false;

    /**
     * 卡号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchCardNumberAttr($query, $value)
    {
        if ($value) {
            $query->whereLike('card_number', '%' . $value . '%');
        }

    }

    /**
     * 用户uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUseUidAttr($query, $value)
    {
        if (is_array($value)) {
            $query->whereIn('use_uid', $value);
        } else {
            $query->where('use_uid', $value);
        }
    }

    /**
     * 手机号搜索器
     * @param Model $query
     * @param $value
     */
    public function searchPhoneAttr($query, $value)
    {
        if ($value) {
            $query->whereIn('use_uid', function ($query) use ($value) {
                $query->name('user')->whereLike('phone', $value . '%')->field('uid')->select();
            });
        }
    }

    /**
     * 批次id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchBatchCardIdAttr($query, $value)
    {
        $query->where('card_batch_id', $value);
    }

    /**
     * 用户use_time搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUseTimeAttr($query, $value)
    {
        if ($value > 0) {
            $query->where('use_time', '>', 0);
        }
        if ($value == 0) {
            $query->where('use_time', 0);
        }

    }

    public function searchIsStatusAttr($query, $value)
    {
        if ($value) {
            $query->where('status', $value);
        }

    }
}
