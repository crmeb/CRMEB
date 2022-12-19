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

/**
 * Class LiveRoomGoods
 * @package app\model\live
 */
class LiveRoomGoods extends BaseModel
{
    use ModelTrait;

    protected $name = 'live_room_goods';

    public function goods()
    {
        return $this->hasOne(LiveGoods::class, 'id', 'live_goods_id');
    }

    public function room()
    {
        return $this->hasOne(LiveRoom::class, 'id', 'live_room_id');
    }
}
