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
namespace app\subscribes;

use app\services\activity\combination\StorePinkServices;
use app\services\agent\AgentManageServices;
use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use think\facade\Log;

/**
 * 定时任务类
 * Class TaskSubscribe
 * @package crmeb\subscribes
 */
class TaskSubscribe
{
    public function handle()
    {

    }

    /**
     * 2秒钟执行的方法
     */
    public function onTask_2()
    {
    }

    /**
     * 6秒钟执行的方法
     */
    public function onTask_6()
    {
        file_put_contents(runtime_path() . '.timer', time());
    }

    /**
     * 10秒钟执行的方法
     */
    public function onTask_10()
    {
    }

    /**
     * 30秒钟执行的方法
     */
    public function onTask_30()
    {
        //自动取消订单
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderServices->orderUnpaidCancel();
    }

    /**
     * 60秒钟执行的方法
     */
    public function onTask_60()
    {
        //拼团失败处理
        /** @var StorePinkServices $storePinkServices */
        $storePinkServices = app()->make(StorePinkServices::class);
        $storePinkServices->statusPink();
        //自动解绑上级绑定
        try {
            /** @var AgentManageServices $agentManage */
            $agentManage = app()->make(AgentManageServices::class);
            $agentManage->removeSpread();
        } catch (\Throwable $e) {
            Log::error('自动解除上级绑定失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 180秒钟执行的方法
     */
    public function onTask_180()
    {
        //更新直播商品状态
        try {
            /** @var LiveGoodsServices $liveGoods */
            $liveGoods = app()->make(LiveGoodsServices::class);
            $liveGoods->syncGoodStatus();
        } catch (\Throwable $e) {
            Log::error('更新直播商品状态失败,失败原因:' . $e->getMessage());
        }
        //更新直播间状态
        try {
            /** @var LiveRoomServices $liveRoom */
            $liveRoom = app()->make(LiveRoomServices::class);
            $liveRoom->syncRoomStatus();
        } catch (\Throwable $e) {
            Log::error('更新直播间状态失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 300秒钟执行的方法
     */
    public function onTask_300()
    {
        //自动收货
        /** @var StoreOrderTakeServices $services */
        $services = app()->make(StoreOrderTakeServices::class);
        $services->autoTakeOrder();
        //清除昨日海报
        /** @var SystemAttachmentServices $attach */
        $attach = app()->make(SystemAttachmentServices::class);
        $attach->emptyYesterdayAttachment();
        //查询预售到期商品自动下架
        /** @var StoreProductServices $product */
        $product = app()->make(StoreProductServices::class);
        $product->downAdvance();
        //自动好评
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $orderServices->autoComment();
    }
}
