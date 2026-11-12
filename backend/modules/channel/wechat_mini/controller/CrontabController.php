<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\channel\wechat_mini\controller;

use modules\channel\wechat_mini\service\LiveGoodsServices;
use modules\channel\wechat_mini\service\LiveRoomServices;

/**
 * 小程序直播定时入口
 * 与插件内 LiveProductStatusTask、LiveRoomStatusTask 做同一件事，保留旧的 HTTP 地址给外部 crontab
 */
class CrontabController
{
    /**
     * 更新直播商品状态
     * @return mixed
     */
    public function syncGoodStatus()
    {
        app()->make(LiveGoodsServices::class)->syncGoodStatus();
        return app('json')->success('执行成功');
    }

    /**
     * 更新直播间状态
     * @return mixed
     */
    public function syncRoomStatus()
    {
        app()->make(LiveRoomServices::class)->syncRoomStatus();
        return app('json')->success('执行成功');
    }
}
