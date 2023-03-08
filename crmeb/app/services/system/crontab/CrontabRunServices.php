<?php

namespace app\services\system\crontab;

use app\services\activity\combination\StorePinkServices;
use app\services\activity\live\LiveGoodsServices;
use app\services\activity\live\LiveRoomServices;
use app\services\agent\AgentManageServices;
use app\services\order\StoreOrderServices;
use app\services\order\StoreOrderTakeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\attachment\SystemAttachmentServices;
use think\facade\Log;

/**
 * 执行定时任务
 * @author 吴汐
 * @email 442384644@qq.com
 * @date 2023/03/01
 */
class CrontabRunServices
{
    /**
     * 定时任务类型 每一个定义的类型会对应CrontabRunServices类中的一个方法
     * @var string[]
     */
    public $markList = [
        'orderCancel' => '未支付自动取消订单',
        'pinkExpiration' => '拼团到期订单处理',
        'agentUnbind' => '到期自动解绑上级',
        'liveProductStatus' => '自动更新直播商品状态',
        'liveRoomStatus' => '自动更新直播间状态',
        'takeDelivery' => '订单自动收货',
        'advanceOff' => '预售商品到期自动下架',
        'productReplay' => '订单商品自动好评',
        'clearPoster' => '清除昨日海报',
    ];

    /**
     * 调用不存在的方法
     * @param $name
     * @param $arguments
     * @return mixed|void
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function __call($name, $arguments)
    {
        $this->crontabLog($name . '方法不存在');
    }

    /**
     * 定时任务日志
     * @param $msg
     */
    protected function crontabLog($msg)
    {
        $timer_log_open = config("log.timer_log", false);
        if ($timer_log_open) {
            $date = date('Y-m-d H:i:s', time());
            Log::write($date . $msg, 'crontab');
        }
    }

    /**
     * 未支付自动取消订单
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function orderCancel()
    {
        try {
            app()->make(StoreOrderServices::class)->orderUnpaidCancel();
            $this->crontabLog(' 执行未支付自动取消订单');
        } catch (\Throwable $e) {
            $this->crontabLog('自动取消订单失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 拼团到期订单处理
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function pinkExpiration()
    {
        try {
            app()->make(StorePinkServices::class)->statusPink();
            $this->crontabLog(' 执行拼团到期订单处理');
        } catch (\Throwable $e) {
            $this->crontabLog('拼团到期订单处理失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 自动解除上级绑定
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function agentUnbind()
    {
        try {
            app()->make(AgentManageServices::class)->removeSpread();
            $this->crontabLog(' 执行自动解绑上级绑定');
        } catch (\Throwable $e) {
            $this->crontabLog('自动解除上级绑定失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 更新直播商品状态
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function liveProductStatus()
    {
        try {
            app()->make(LiveGoodsServices::class)->syncGoodStatus();
            $this->crontabLog(' 执行更新直播商品状态');
        } catch (\Throwable $e) {
            $this->crontabLog('更新直播商品状态失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 更新直播间状态
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function liveRoomStatus()
    {
        try {
            app()->make(LiveRoomServices::class)->syncRoomStatus();
            $this->crontabLog(' 执行更新直播间状态');
        } catch (\Throwable $e) {
            $this->crontabLog('更新直播间状态失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 自动收货
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function takeDelivery()
    {
        try {
            app()->make(StoreOrderTakeServices::class)->autoTakeOrder();
            $this->crontabLog(' 执行自动收货');
        } catch (\Throwable $e) {
            $this->crontabLog('自动收货失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 预售到期商品自动下架
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function advanceOff()
    {
        try {
            app()->make(StoreProductServices::class)->downAdvance();
            $this->crontabLog(' 执行预售到期商品自动下架');
        } catch (\Throwable $e) {
            $this->crontabLog('预售到期商品自动下架失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 自动好评
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function productReplay()
    {
        try {
            app()->make(StoreOrderServices::class)->autoComment();
            $this->crontabLog(' 执行自动好评');
        } catch (\Throwable $e) {
            $this->crontabLog('自动好评失败,失败原因:' . $e->getMessage());
        }
    }

    /**
     * 清除昨日海报
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/01
     */
    public function clearPoster()
    {
        try {
            app()->make(SystemAttachmentServices::class)->emptyYesterdayAttachment();
            $this->crontabLog(' 执行清除昨日海报');
        } catch (\Throwable $e) {
            $this->crontabLog('清除昨日海报失败,失败原因:' . $e->getMessage());
        }
    }
}
