<?php

namespace app\jobs;

use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderServices;
use app\services\user\UserServices;
use app\services\product\product\StoreProductReplyServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

class AutoCommentJob extends BaseJobs
{
    use QueueTrait;

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
        /** @var StoreOrderCartInfoServices $cartInfoServices */
        $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        /** @var  $replyServices */
        $replyServices = app()->make(StoreProductReplyServices::class);
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $list = $cartInfoServices->getColumn([['cart_id', 'in', $cart_ids]], 'cart_info,uid,oid,unique,product_id');
        $uids = array_column($list, 'uid');
        $userInfos = $userServices->getColumn([['uid', 'in', $uids]], 'nickname,avatar', 'uid');
        $reply = [];
        foreach ($list as $item) {
            $reply[] = [
                'uid' => $item['uid'],
                'oid' => $item['oid'],
                'unique' => $item['unique'],
                'product_id' => $item['product_id'],
                'reply_type' => 'product',
                'nickname' => $userInfos[$item['uid']]['nickname'],
                'avatar' => $userInfos[$item['uid']]['avatar'],
                'comment' => sys_config('comment_content',''),
                'product_score' => 5,
                'service_score' => 5,
                'add_time' => time(),
            ];
        }
        if (count($reply)) {
            $replyServices->saveAll($reply);
        }
        $orderServices->checkOrderOver($replyServices, $cartInfoServices->getCartColunm(['oid' => $id], 'unique', ''), $id);
        return true;
    }
}
