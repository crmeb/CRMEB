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
declare (strict_types=1);

namespace app\dao\live;


use app\dao\BaseDao;
use app\model\live\LiveRoomGoods;


class LiveRoomGoodsDao extends BaseDao
{


    protected function setModel(): string
    {
        return LiveRoomGoods::class;
    }

    public function clear($id)
    {
        return $this->getModel()->where('live_room_id', $id)->delete();
    }
}
