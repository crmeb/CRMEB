<?php

namespace crmeb\subscribes;

use app\admin\model\system\SystemAttachment;
use app\models\store\StoreBargainUser;
use app\models\store\StoreOrder;
use app\models\store\StorePink;
use app\models\user\UserToken;
use think\facade\Db;

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
        try {
            Db::startTrans();
            StoreBargainUser::startBargainUserStatus();//批量修改砍价状态为 砍价失败
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
        try {
            Db::startTrans();
            StoreOrder::orderUnpaidCancel();//订单未支付默认取消
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
        try {
            Db::startTrans();
            StoreOrder::startTakeOrder();//7天自动收货
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
        try {
            Db::startTrans();
            StorePink::statusPink();//拼团到期修改状态
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 60秒钟执行的方法
     */
    public function onTask_60()
    {
    }

    /**
     * 180秒钟执行的方法
     */
    public function onTask_180()
    {
    }

    /**
     * 300秒钟执行的方法
     */
    public function onTask_300()
    {
        UserToken::delToken();//删除一天前的过期token
        SystemAttachment::emptyYesterdayAttachment();//清除昨日海报
        StoreOrder::sendTen();//10分钟未付款发送通知
    }
}