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

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 抽奖活动
 * Class LuckLottery
 * @package app\model\activity\lottery
 */
class LuckLottery extends BaseModel
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
    protected $name = 'luck_lottery';

    /**
     * 抽奖用户等级修改器
     * @param $value
     * @return false|string
     */
    protected function setUserLevelAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 抽奖用户等级获取器
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getUserLevelAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 抽奖用户标签修改器
     * @param $value
     * @return false|string
     */
    protected function setUserLabelAttr($value)
    {
        if ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }
        return '';
    }

    /**
     * 抽奖用户标签获取器
     * @param $value
     * @param $data
     * @return mixed
     */
    protected function getUserLabelAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 关联奖品
     * @return \think\model\relation\HasOne
     */
    public function prize()
    {
        return $this->hasMany(LuckPrize::class, 'lottery_id', 'id')->where('status', 1)->where('is_del', 0)->order('sort asc,id asc');
    }

    /**
     * 关键词搜索器
     * @param $query Model
     * @param $value
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value !== '') $query->where('id|name|desc|content', 'like', '%' . $value . '%');
    }

    /**
     * 抽奖形式搜索器
     * @param $query Model
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value) $query->where('type', $value);
    }

    /**
     * 抽奖类型搜索器
     * @param $query Model
     * @param $value
     */
    public function searchFactorAttr($query, $value)
    {
        if ($value !== '') $query->where('factor', $value);
    }

    /**
     * 状态搜索器
     * @param $query Model
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }

    /**
     * 是否删除搜索器
     * @param $query Model
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }
}
