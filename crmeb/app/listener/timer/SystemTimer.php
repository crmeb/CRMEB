<?php

namespace app\listener\timer;

use app\services\activity\combination\StorePinkServices;
use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\agent\AgentManageServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use app\services\system\timer\SystemTimerServices;
use crmeb\interfaces\ListenerInterface;
use think\facade\Log;
use Workerman\Crontab\Crontab;

class SystemTimer implements ListenerInterface
{
    public function handle($event): void
    {

        new Crontab('*/6 * * * * *', function () {
            file_put_contents(runtime_path() . '.timer', time());
        });

        /** @var SystemTimerServices $systemTimerServices */
        $systemTimerServices = app()->make(SystemTimerServices::class);
        $list = $systemTimerServices->selectList(['is_del' => 0, 'is_open' => 1])->toArray();
        foreach ($list as &$item) {
            //获取定时任务时间字符串
            $timeStr = $this->getTimerStr($item);
//            Log::error('mark:'.$item['mark']);
//            Log::error($timeStr);

            if ($item['mark'] == 'order_cancel') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔30秒执行一次自动取消订单 '.date('Y-m-d H:i:s'));
                    //未支付自动取消订单
                    try {
                        /** @var StoreOrderServices $orderServices */
                        $orderServices = app()->make(StoreOrderServices::class);
                        $orderServices->orderUnpaidCancel();
                    } catch (\Throwable $e) {
                        Log::error('自动取消订单失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'pink_expiration') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔1分钟执行一次拼团到期订单处理 '.date('Y-m-d H:i:s'));
                    //拼团到期订单处理
                    try {
                        /** @var StorePinkServices $storePinkServices */
                        $storePinkServices = app()->make(StorePinkServices::class);
                        $storePinkServices->statusPink();
                    } catch (\Throwable $e) {
                        Log::error('拼团到期订单处理失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'agent_unbind') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔1分钟执行一次自动解除上级绑定 '.date('Y-m-d H:i:s'));
                    //自动解绑上级绑定
                    try {
                        /** @var AgentManageServices $agentManage */
                        $agentManage = app()->make(AgentManageServices::class);
                        $agentManage->removeSpread();
                    } catch (\Throwable $e) {
                        Log::error('自动解除上级绑定失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'live_product_status') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔3分钟执行一次更新直播商品状态 '.date('Y-m-d H:i:s'));
                    //更新直播商品状态
                    try {
                        /** @var LiveGoodsServices $liveGoods */
                        $liveGoods = app()->make(LiveGoodsServices::class);
                        $liveGoods->syncGoodStatus();
                    } catch (\Throwable $e) {
                        Log::error('更新直播商品状态失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'live_room_status') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔3分钟执行一次更新直播间状态 '.date('Y-m-d H:i:s'));
                    //更新直播间状态
                    try {
                        /** @var LiveRoomServices $liveRoom */
                        $liveRoom = app()->make(LiveRoomServices::class);
                        $liveRoom->syncRoomStatus();
                    } catch (\Throwable $e) {
                        Log::error('更新直播间状态失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'take_delivery') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔5分钟执行一次自动收货 '.date('Y-m-d H:i:s'));
                    //自动收货
                    try {
                        /** @var StoreOrderTakeServices $services */
                        $services = app()->make(StoreOrderTakeServices::class);
                        $services->autoTakeOrder();
                    } catch (\Throwable $e) {
                        Log::error('自动收货失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'advance_off') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔5分钟执行一次查询预售到期商品自动下架 '.date('Y-m-d H:i:s'));
                    //查询预售到期商品自动下架
                    try {
                        /** @var StoreProductServices $product */
                        $product = app()->make(StoreProductServices::class);
                        $product->downAdvance();
                    } catch (\Throwable $e) {
                        Log::error('预售到期商品自动下架失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'product_replay') {
                new Crontab($timeStr, function () {
//                    Log::error('每隔5分钟执行一次自动好评 '.date('Y-m-d H:i:s'));
                    //自动好评
                    try {
                        /** @var StoreOrderServices $orderServices */
                        $orderServices = app()->make(StoreOrderServices::class);
                        $orderServices->autoComment();
                    } catch (\Throwable $e) {
                        Log::error('自动好评失败,失败原因:' . $e->getMessage());
                    }
                });
            }

            if ($item['mark'] == 'clear_poster') {
                new Crontab($timeStr, function () {
//                    Log::error('每天0时30分0秒执行一次清除昨日海报 '.date('Y-m-d H:i:s'));
                    //清除昨日海报
                    try {
                        /** @var SystemAttachmentServices $attach */
                        $attach = app()->make(SystemAttachmentServices::class);
                        $attach->emptyYesterdayAttachment();
                    } catch (\Throwable $e) {
                        Log::error('清除昨日海报失败,失败原因:' . $e->getMessage());
                    }
                });
            }
        }
    }

    public function getTimerStr($data): string
    {
        $timeStr = '';
        switch ($data['type']) {
            case 1:
                $timeStr = '*/' . $data['second'] . ' * * * * *';
                break;
            case 2:
                $timeStr = '0 */' . $data['minute'] . ' * * * *';
                break;
            case 3:
                $timeStr = '0 0 */' . $data['hour'] . ' * * *';
                break;
            case 4:
                $timeStr = '0 0 0 */' . $data['day'] . ' * *';
                break;
            case 5:
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * *';
                break;
            case 6:
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * ' . ($data['week'] == 7 ? 0 : $data['week']);
                break;
            case 7:
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' ' . $data['day'] . ' * *';
                break;
        }
        return $timeStr;
    }
}