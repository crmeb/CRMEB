<?php

namespace app\api\controller\v1;

use app\services\activity\combination\StorePinkServices;
use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\agent\AgentManageServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\crontab\SystemCrontabServices;

/**
 * 定时任务控制器
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/02/21
 */
class CrontabController
{
    /**
     * 定时任务调用接口
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/17
     */
    public function crontabRun()
    {
        app()->make(SystemCrontabServices::class)->crontabApiRun();
    }

    /**
     * 检测定时任务是否正常，必须6秒执行一次
     */
    public function crontabCheck()
    {
        file_put_contents(root_path() . 'runtime/.timer', time());
    }

    /**
     * 未支付自动取消订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderUnpaidCancel()
    {
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderServices->orderUnpaidCancel();
    }

    /**
     * 拼团到期订单处理
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function pinkExpiration()
    {
        /** @var StorePinkServices $storePinkServices */
        $storePinkServices = app()->make(StorePinkServices::class);
        $storePinkServices->statusPink();
    }

    /**
     * 自动解绑上级绑定
     */
    public function agentUnbind()
    {
        /** @var AgentManageServices $agentManage */
        $agentManage = app()->make(AgentManageServices::class);
        $agentManage->removeSpread();
    }

    /**
     * 更新直播商品状态
     */
    public function syncGoodStatus()
    {
        /** @var LiveGoodsServices $liveGoods */
        $liveGoods = app()->make(LiveGoodsServices::class);
        $liveGoods->syncGoodStatus();
    }

    /**
     * 更新直播间状态
     */
    public function syncRoomStatus()
    {
        /** @var LiveRoomServices $liveRoom */
        $liveRoom = app()->make(LiveRoomServices::class);
        $liveRoom->syncRoomStatus();
    }

    /**
     * 自动收货
     */
    public function autoTakeOrder()
    {
        /** @var StoreOrderTakeServices $services */
        $services = app()->make(StoreOrderTakeServices::class);
        $services->autoTakeOrder();
    }

    /**
     * 查询预售到期商品自动下架
     */
    public function downAdvance()
    {
        /** @var StoreProductServices $product */
        $product = app()->make(StoreProductServices::class);
        $product->downAdvance();
    }

    /**
     * 自动好评
     */
    public function autoComment()
    {
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderServices->autoComment();
    }

    /**
     * 清除昨日海报
     * @throws \Exception
     */
    public function emptyYesterdayAttachment()
    {
        /** @var SystemAttachmentServices $attach */
        $attach = app()->make(SystemAttachmentServices::class);
        $attach->emptyYesterdayAttachment();
    }
}
