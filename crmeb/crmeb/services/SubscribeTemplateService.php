<?php

namespace crmeb\services;

use app\admin\model\wechat\WechatUser;
use app\admin\model\wechat\StoreService as ServiceModel;
use app\models\routine\RoutineTemplate;
use crmeb\interfaces\ProviderInterface;
use think\facade\Db;

/**
 * 小程序模板消息
 * Class RoutineTemplate
 * @package app\routine\model\routine
 */
class SubscribeTemplateService implements ProviderInterface
{

    //订单发货提醒(送货)
    const ORDER_POSTAGE_SUCCESS = 1128;
    //提现成功通知
    const USER_EXTRACT = 1470;
    //确认收货通知
    const OREDER_TAKEVER = 1481;
    //订单取消
    const ORDER_CLONE = 1134;
    //订单发货提醒(快递)
    const ORDER_DELIVER_SUCCESS = 1458;
    //拼团成功
    const PINK_TRUE = 3098;
    //砍价成功
    const BARGAIN_SUCCESS = 2727;
    //核销成功通知
    const ORDER_WRITE_OFF = 3116;
    //新订单提醒
    const ORDER_NEW = 1476;
    //退款通知
    const ORDER_REFUND = 1451;
    //充值成功
    const RECHARGE_SUCCESS = 755;
    //订单支付成功
    const ORDER_PAY_SUCCESS = 1927;
    //申请退款通知 管理员提醒
    const ORDER_REFUND_STATUS = 1468;
    //积分到账提醒
    const INTEGRAL_ACCOUT = 335;
    //拼团状态通知
    const PINK_STATUS = 3353;

    public static function getConstants($code = '')
    {
        $oClass = new \ReflectionClass(__CLASS__);
        $stants = $oClass->getConstants();
        if ($code) return isset($stants[$code]) ? $stants[$code] : '';
        else return $stants;
    }

    public function register($config)
    {

    }

    /**
     * 根据模板编号获取模板ID
     * @param string $tempKey
     * @return mixed|string
     */
    public static function setTemplateId($tempKey = '')
    {
        if ($tempKey == '') return '';
        return RoutineTemplate::where('tempkey', $tempKey)->where('type', 0)->where('status', 1)->value('tempid');
    }


    /**
     * 发送订阅模板消息
     * @param string $tempCode 所需下发的模板编号
     * @param string $openId 接收者（用户）的 openid
     * @param array $dataKey 模板内容，不填则下发空模板
     * @param string $link 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转
     * @return bool|\EasyWeChat\Support\Collection|null
     */
    public static function sendTemplate(string $tempCode, string $openId, array $dataKey, string $link = '')
    {
        if (!$openId || !$tempCode) return false;
        return MiniProgramService::sendSubscribeTemlate($openId, trim(self::setTemplateId(self::getConstants($tempCode))), $dataKey, $link);
    }
}