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

namespace app\model\activity\live;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * Class LiveRoom
 * @package app\model\live
 */
class LiveRoom extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'live_room';

    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'add_time';

    protected function setAddTimeAttr()
    {
        return time();
    }

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    public function getAddTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    public function getStartTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    public function getEndTimeAttr($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }
        return '';
    }

    public function roomGoods()
    {
        return $this->hasMany(LiveRoomGoods::class, 'live_room_id', 'id');
    }

    public function anchor()
    {
        return $this->hasOne(LiveAnchor::class, 'wechat', 'anchor_wechat')->where('is_show', 1)->where('is_del', 0)->bind(['anchor_img' => 'cover_img']);
    }

    /**
     * 直播间id
     * @param Model $query
     * @param $value
     */
    public function searchRoomIdAttr($query, $value)
    {
        if (is_array($value))
            $query->whereIn('room_id', $value);
        else
            $query->where('room_id', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsShowAttr($query, $value)
    {
        if ($value !== '') $query->where('is_show', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchKerwordAttr($query, $value)
    {
        if ($value !== '') $query->whereLike('id|room_id|name|anchor_name|anchor_wechat', "%{$value}%");
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            switch ($value) {
                case 1:
                    $query->whereIn('live_status', [101, 105, 106]);
                    break;
                case 2:
                    $query->whereIn('live_status', [102]);
                    break;
                case 3:
                    $query->whereIn('live_status', [103, 104, 107]);
                    break;
            }
        }
    }

}
