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

use app\model\system\SystemUserLevel;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\model;

/**
 * Class UserLevel
 * @package app\model\user
 */
class UserLevel extends BaseModel
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
    protected $name = 'user_level';

    public function levelInfo()
    {
        return $this->hasOne(SystemUserLevel::class, 'id', 'level_id');
    }

    /**
     * 用户uid
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        $query->where('uid', $value);
    }

    /**
     * 是否永久
     * @param Model $query
     * @param $value
     */
    public function searchIsForeverAttr($query, $value)
    {
        $query->where('is_forever', $value);
    }

    /**
     * 过期时间
     * @param Model $query
     * @param $value
     */
    public function searchValidTimeAttr($query, $value)
    {
        $query->where('valid_time', '>', $value);
    }

    /**
     * 状态
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 是否通知
     * @param Model $query
     * @param $value
     */
    public function searchRemindAttr($query, $value)
    {
        $query->where('remind', $value);
    }

    /**
     * 是否删除
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        $query->where('is_del', $value);
    }
}
