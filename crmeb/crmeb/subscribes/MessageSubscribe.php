<?php
namespace crmeb\subscribes;

use app\admin\model\wechat\WechatMessage;
use app\models\user\WechatUser;

/**
 * 用户消息事件
 * Class MessageSubscribe
 * @package crmeb\subscribes
 */
class MessageSubscribe
{
    public function handle()
    {

    }

    /**
     * 微信消息前置操作
     * @param $event
     */
    public function onWechatMessageBefore($event)
    {
        list($message) = $event;
        WechatUser::saveUser($message->FromUserName);

        $event = isset($message->Event) ?
            $message->MsgType.(
            $message->Event == 'subscribe' && isset($message->EventKey) ? '_scan' : ''
            ).'_'.$message->Event : $message->MsgType;
        WechatMessage::setMessage(json_encode($message),$message->FromUserName,strtolower($event));
    }

    /**
     * 用户取消关注公众号前置操作
     * @param $event
     */
    public function onWechatEventUnsubscribeBefore($event)
    {
        list($message) = $event;
        WechatUser::unSubscribe($message->FromUserName);
    }
}