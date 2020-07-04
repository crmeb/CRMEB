<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/23
 */

namespace app\models\store;

use app\models\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 客服信息Model
 * Class StoreServiceLog
 * @package app\models\store
 */
class StoreServiceLog extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_service_log';

    /**
     * 消息类型
     * @var array  1=文字 2=表情 3=图片 4=语音 5 = 商品链接 6 = 订单类型
     */
    const MSN_TYPE = [1, 2, 3, 4, 5, 6];

    /**
     * 商品链接消息类型
     */
    const MSN_TYPE_GOODS = 5;

    /**
     * 订单信息消息类型
     */
    const MSN_TYPE_ORDER = 6;


    use ModelTrait;

    /**
     * 客服聊天记录
     * @param $uid
     * @param $toUid
     * @param $page
     * @param $limit
     * @return array
     */
    public static function lst($uid, $toUid, $page, $limit)
    {
        if (!$limit || !$page) return [];
        $model = new self;
        $model = $model->whereIn('uid', [$uid, $toUid]);
        $model = $model->whereIn('to_uid', [$uid, $toUid]);
        $model = $model->order('id DESC');
        $model = $model->page($page, $limit);
        return $model->select()->each(function ($item) {
            $userInfo = StoreService::where('uid', $item['uid'])->field('nickname,avatar')->find();
            if(!$userInfo) $userInfo = User::getUserInfo($item['uid'],'nickname,avatar');
            if ($userInfo) {
                $item['nickname'] = $userInfo['nickname'];
                $item['avatar'] = $userInfo['avatar'];
            }

            if ($item['msn_type'] == self::MSN_TYPE_GOODS && $item['msn']) {
                $item['productInfo'] = StoreProduct::validWhere()->where('id', $item['msn'])->find();
            } else {
                $item['productInfo'] = [];
            }

            $item['orderInfo'] = [];
            if ($item['msn_type'] == self::MSN_TYPE_ORDER && $item['msn']) {
                $order = StoreOrder::getUserOrderDetail($item['uid'], $item['msn']);
                if ($order) {
                    $order = StoreOrder::tidyOrder($order->toArray(), true, true);
                    $order['add_time_y'] = date('Y-m-d', $order['add_time']);
                    $order['add_time_h'] = date('H:i:s', $order['add_time']);
                    $item['orderInfo'] = $order;
                }
            }
            $item['msn_type'] = (int)$item['msn_type'];
        });
    }
}