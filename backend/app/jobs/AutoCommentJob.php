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

use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use app\services\user\UserServices;
use app\services\product\product\StoreProductReplyServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

class AutoCommentJob extends BaseJobs
{

    /**
     * 自动评价
     * @param $id
     * @param $cart_ids
     * @return bool
     */
    public function doJob($id, $cart_ids)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        /** @var  $replyServices */
        $replyServices = app()->make(StoreProductReplyServices::class);
        /** @var OrderCenterDao $orderDao */
        $orderDao = app()->make(OrderCenterDao::class);
        // 从订单中台 extra_info.items 读取商品快照（旧 eb_store_order_cart_info 已废弃）
        $order = $orderDao->get((int)$id, ['uid', 'extra_info']);
        if (!$order) return true;
        $uid = (int)$order['uid'];
        $extra = $order['extra_info'];
        $extra = is_string($extra) ? json_decode($extra, true) : ($extra ?? []);
        $items = $extra['items'] ?? [];
        // 按 cart_id 过滤需要自动评价的商品
        $cartIdMap = [];
        foreach ((array)$cart_ids as $cid) {
            $cartIdMap[(string)$cid] = true;
        }
        $list = [];
        foreach ($items as $item) {
            $cartId = (string)($item['cart_id'] ?? 0);
            if (!isset($cartIdMap[$cartId])) continue;
            $unique = $item['attr_info']['unique'] ?? ($item['unique'] ?? '');
            $list[] = [
                'uid' => $uid,
                'oid' => (int)$id,
                'unique' => $unique,
                'product_id' => (int)($item['product_id'] ?? 0),
            ];
        }
        $userInfos = $uid ? $userServices->getColumn([['uid', '=', $uid]], 'nickname,avatar', 'uid') : [];
        $reply = [];
        foreach ($list as $item) {
            $reply[] = [
                'uid' => $item['uid'],
                'oid' => $item['oid'],
                'unique' => $item['unique'],
                'product_id' => $item['product_id'],
                'reply_type' => 'product',
                'nickname' => $userInfos[$item['uid']]['nickname'] ?? '',
                'avatar' => $userInfos[$item['uid']]['avatar'] ?? '',
                'comment' => sys_config('comment_content',''),
                'product_score' => 5,
                'service_score' => 5,
                'add_time' => time(),
            ];
        }
        if (count($reply)) {
            $replyServices->saveAll($reply);
        }
        // 评价落库后检查订单是否全部评价完成，是则流转为已完成
        // 包 try-catch：评价已落库，状态流转失败不应让 Job 重试（重试会导致 saveAll 重复插入）
        // 与 StoreProductReplyServices::checkOrderOverAfterComment 保持一致的防御策略
        try {
            app()->make(OrderCenterServices::class)->checkOrderOver((int)$id);
        } catch (\Throwable $e) {
            Log::error('自动评价后检查订单完成失败: ' . $e->getMessage());
        }
        return true;
    }
}
