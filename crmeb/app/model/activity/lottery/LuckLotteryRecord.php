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
declare (strict_types=1);

namespace app\model\activity\lottery;

use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 *
 * Class LuckLotteryRecordDao
 * @package app\model\activity\lottery
 */
class LuckLotteryRecord extends BaseModel
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
    protected $name = 'luck_lottery_record';

    /**
     * 收货信息修改器
     * @param $value
     * @return false|string
     */
    protected function setReceiveInfoAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 收货信息获取器
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getReceiveInfoAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 发货信息修改器
     * @param $value
     * @return false|string
     */
    protected function setDeliverInfoAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 发货信息获取器
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getDeliverInfoAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 关联抽奖
     * @return \think\model\relation\HasOne
     */
    public function lottery()
    {
        return $this->hasOne(LuckLottery::class, 'id', 'lottery_id');
    }

    /**
     * 关联奖品
     * @return \think\model\relation\HasOne
     */
    public function prize()
    {
        return $this->hasOne(LuckPrize::class, 'id', 'prize_id');
    }

    /**
     * 关联用户
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field('uid,real_name,nickname,phone');
    }

    /**
     * 用户uid搜索器
     * @param $query Model
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value) $query->where('uid', $value);
    }

    /**
     * 关键词搜索器
     * @param $query Model
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') {
            $query->where(function ($query1) use ($value) {
                $query1->where('id|uid|lottery_id|prize_id', 'LIKE', '%' . $value . '%')->whereOr('uid', 'IN', function ($query1) use ($value) {
                    $query1->name('user')->field('uid')->where('account|nickname|phone|real_name|uid', 'LIKE', "%$value%")->select();
                })->whereOr('lottery_id', 'IN', function ($query1) use ($value) {
                    $query1->name('luck_lottery')->field('id')->where('name|desc|content', 'LIKE', "%$value%")->select();
                })->whereOr('prize_id', 'IN', function ($query1) use ($value) {
                    $query1->name('luck_prize')->field('id')->where('name|prompt', 'LIKE', "%$value%")->select();
                });
            });
        }
    }

    /**
     * 抽奖id搜索器
     * @param $query Model
     * @param $value
     */
    public function searchLotteryIdAttr($query, $value)
    {
        if ($value) $query->where('lottery_id', $value);
    }

    /**
     * 奖品id搜索器
     * @param $query Model
     * @param $value
     */
    public function searchPrizeIdAttr($query, $value)
    {
        if ($value) $query->where('prize_id', $value);
    }

    /**
     * 奖品类型搜索器
     * @param $query Model
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereIn('type', $value);
            } else {
                $query->where('type', $value);
            }
        }
    }

    /**
     * 奖品不再这个类型中搜索器
     * @param $query Model
     * @param $value
     */
    public function searchNotTypeAttr($query, $value)
    {
        if ($value) {
            if (is_array($value)) {
                $query->whereNotIn('type', $value);
            } else {
                $query->where('type', '<>', $value);
            }
        }
    }

    /**
     * 是否领取
     * @param $query Model
     * @param $value
     */
    public function searchIsReceiveAttr($query, $value)
    {
        if ($value !== '') $query->where('is_reveive', $value);
    }

    /**
     * 是否发货处理
     * @param $query Model
     * @param $value
     */
    public function searchIsDeliverAttr($query, $value)
    {
        if ($value !== '') $query->where('is_deliver', $value);
    }
}
