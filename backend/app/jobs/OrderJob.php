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

namespace app\jobs;

use app\services\order\OrderLifecycleServices;
use app\services\product\CenterProductServices;
use app\services\user\UserLabelRelationServices;
use app\services\user\UserLevelServices;
use crmeb\basic\BaseJobs;
use crmeb\services\workerman\ChannelService;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * 订单消息队列
 * Class OrderJob
 * @package crmeb\jobs
 */
class OrderJob extends BaseJobs
{

    /**
     * 执行订单支付成功发送消息
     * @param $order
     * @return bool
     */
    public function doJob($order)
    {
        //计算商品节省金额
        try {
            $this->setEconomizeMoney($order);
        } catch (\Throwable $e) {
            Log::error('计算节省金额,失败原因:' . $e->getMessage());
            throw $e;
        }
        //更新用户支付订单数量
        try {
            $this->setUserPayCountAndPromoter($order);
        } catch (\Throwable $e) {
            Log::error('更新用户订单数失败,失败原因:' . $e->getMessage());
            throw $e;
        }
        //增加用户标签
        try {
            $this->setUserLabel($order);
        } catch (\Throwable $e) {
            Log::error('用户标签添加失败,失败原因:' . $e->getMessage());
            throw $e;
        }
        try {
            // 通过事件触发渠道层客服消息发送，避免 app 层直接引用 modules
            $channel = $this->resolveNoticeChannel($order);
            if (in_array($channel, [0, 2], true)) {//公众号发送模板消息
                event('OrderPaySuccessCustomerNotice', ['order' => $order, 'type' => 1]);
            } else if (in_array($channel, [1, 2], true)) {//小程序发送模板消息
                event('OrderPaySuccessCustomerNotice', ['order' => $order, 'type' => 0]);
            }
        } catch (\Exception $e) {
            throw new ValidateException('发送客服消息,短信消息失败,失败原因:' . $e->getMessage());
        }


        //检测会员等级
        try {
            /** @var UserLevelServices $levelServices */
            $levelServices = app()->make(UserLevelServices::class);
            $levelServices->detection((int)$order['uid']);
        } catch (\Throwable $e) {
            Log::error('会员等级升级失败,失败原因:' . $e->getMessage());
            throw $e;
        }
        //向后台发送新订单消息
        try {
            ChannelService::instance()->send('NEW_ORDER', ['order_id' => $order['order_id']]);
        } catch (\Throwable $e) {
            Log::error('向后台发送新订单消息失败,失败原因:' . $e->getMessage());
        }
        return true;
    }

    /**
     * 设置用户购买次数和检测时候成为推广人
     * @param $order
     */
    public function setUserPayCountAndPromoter($order)
    {
        app()->make(OrderLifecycleServices::class)->recordPayCountAndPromoter((array)$order);
    }

    /**
     * 设置用户购买的标签
     * @param $order
     */
    public function setUserLabel($order)
    {
        // 从订单中台 extra_info.items 提取商品ID列表（旧 cart_info 表已废弃）
        /** @var OrderCenterDao $centerDao */
        $centerDao = app()->make(OrderCenterDao::class);
        $centerOrder = $centerDao->get(['order_no' => $order['order_no'] ?? $order['order_id'] ?? ''], ['extra_info']);
        $productIds = [];
        if ($centerOrder) {
            $extra = $centerOrder['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                $pid = (int)($item['product_id'] ?? 0);
                if ($pid > 0) {
                    $productIds[] = $pid;
                }
            }
        }
        if (!$productIds) {
            return true;
        }
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        $label = $productServices->getColumn([['id', 'in', array_unique($productIds)]], 'label_list');
        $labelIds = array_values(array_unique(array_filter(array_map('intval', explode(',', implode(',', $label))), static fn ($id) => $id > 0)));
        if (!$labelIds) {
            return true;
        }
        /** @var UserLabelRelationServices $labelServices */
        $labelServices = app()->make(UserLabelRelationServices::class);
        $where = [
            ['label_id', 'in', $labelIds],
            ['uid', '=', $order['uid']]
        ];
        $data = [];
        $userLabel = $labelServices->getColumn($where, 'label_id');
        foreach ($labelIds as $item) {
            if (!in_array($item, $userLabel)) {
                $data[] = ['uid' => $order['uid'], 'label_id' => $item];
            }
        }
        $re = true;
        if ($data) {
            $re = $labelServices->saveAll($data);
        }
        return $re;
    }


    /**
     * 计算节约金额（转调订单生命周期服务，数据源为中台订单列）
     * @param $order
     * @return bool
     */
    public function setEconomizeMoney($order)
    {
        /** @var OrderLifecycleServices $lifecycleServices */
        $lifecycleServices = app()->make(OrderLifecycleServices::class);
        return $lifecycleServices->recordEconomize((array)$order);
    }

    /**
     * 兼容旧任务的渠道编号及中台渠道标识；未知渠道不发送微信专属通知。
     * @param array $order 订单任务数据
     * @return int|null
     */
    protected function resolveNoticeChannel(array $order): ?int
    {
        if (isset($order['is_channel'])) {
            return (int)$order['is_channel'];
        }
        return match ($order['channel_type'] ?? '') {
            'wechat', 'wechat_mp' => 0,
            'routine', 'wechat_mini' => 1,
            default => null,
        };
    }
}
