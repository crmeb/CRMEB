<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message\wechat;


use app\services\BaseServices;
use app\services\other\QrcodeServices;
use app\services\user\LoginServices;
use app\services\user\UserServices;
use app\services\wechat\WechatReplyServices;
use app\services\wechat\WechatUserServices;

class MessageServices extends BaseServices
{
    /**
     * 扫码
     * @param $message
     * @return array|\EasyWeChat\Message\Image|\EasyWeChat\Message\News|\EasyWeChat\Message\Text|\EasyWeChat\Message\Voice|string
     */
    public function wechatEventScan($message)
    {
        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);
        /** @var WechatReplyServices $wechatReplyService */
        $wechatReplyService = app()->make(WechatReplyServices::class);
        $response = $wechatReplyService->reply('subscribe');
        if ($message->EventKey && ($qrInfo = $qrcodeService->getQrcode($message->Ticket, 'ticket'))) {
            $qrcodeService->scanQrcode($message->Ticket, 'ticket');
            if (strtolower($qrInfo['third_type']) == 'spread') {
                try {
                    $spreadUid = $qrInfo['third_id'];
                    /** @var WechatUserServices $wechatUser */
                    $wechatUser = app()->make(WechatUserServices::class);
                    $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                    if ($spreadUid == $uid) {
                        $response = '自己不能推荐自己';
                    }
                    /** @var UserServices $userService */
                    $userService = app()->make(UserServices::class);
                    $userInfo = $userService->get($uid);
                    if (!$userInfo) {
                        $response = '用户不存在';
                    }
                    if ($userInfo['spread_uid']) {
                        $response = '已有推荐人!';
                    }
                    /** @var LoginServices $loginService */
                    $loginService = app()->make(LoginServices::class);
                    if ($loginService->updateUserInfo(['code' => $spreadUid], $userInfo)) {
                        $response = '绑定推荐人失败!';
                    }
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
        }
        return $response;
    }

    /**
     * 取消关注
     * @param $message
     */
    public function wechatEventUnsubscribe($message)
    {
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        $wechatUser->unSubscribe($message->FromUserName);
    }


    /**
     * 公众号关注
     * @param $message
     * @return array|\EasyWeChat\Message\Image|\EasyWeChat\Message\News|\EasyWeChat\Message\Text|\EasyWeChat\Message\Voice|string
     */
    public function wechatEventSubscribe($message)
    {
        /** @var WechatReplyServices $wechatReplyService */
        $wechatReplyService = app()->make(WechatReplyServices::class);
        $response = $wechatReplyService->reply('subscribe');
        if (isset($message->EventKey)) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            if ($message->EventKey && ($qrInfo = $qrcodeService->getQrcode($message->Ticket, 'ticket'))) {
                $qrcodeService->scanQrcode($message->Ticket, 'ticket');
                if (strtolower($qrInfo['third_type']) == 'spread') {
                    try {
                        $spreadUid = $qrInfo['third_id'];
                        /** @var WechatUserServices $wechatUser */
                        $wechatUser = app()->make(WechatUserServices::class);
                        $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                        if ($spreadUid == $uid) return '自己不能推荐自己';
                        /** @var UserServices $userService */
                        $userService = app()->make(UserServices::class);
                        $userInfo = $userService->get($uid);
                        if ($userInfo['spread_uid']) return '已有推荐人!';
                        $userInfo->spread_uid = $spreadUid;
                        $userInfo->spread_time = time();
                        if (!$userInfo->save()) {
                            $response = '绑定推荐人失败!';
                        }
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                }
            }
        }
        return $response;
    }

    /**
     * 位置 事件
     * @param $message
     * @return string
     */
    public function wechatEventLocation($message)
    {
        //return 'location';
    }

    /**
     * 跳转URL  事件
     * @param $message
     * @return string
     */
    public function wechatEventView($message)
    {
        //return 'view';
    }

    /**
     * 图片 消息
     * @param $message
     * @return string
     */
    public function wechatMessageImage($message)
    {
        //return 'image';
    }

    /**
     * 语音 消息
     * @param $message
     * @return string
     */
    public function wechatMessageVoice($message)
    {
        //return 'voice';
    }

    /**
     * 视频 消息
     * @param $message
     * @return string
     */
    public function wechatMessageVideo($message)
    {
        //return 'video';
    }

    /**
     * 位置  消息
     */
    public function wechatMessageLocation($message)
    {
        //return 'location';
    }

    /**
     * 链接   消息
     * @param $message
     * @return string
     */
    public function wechatMessageLink($message)
    {
        //return 'link';
    }

    /**
     * 其它消息  消息
     */
    public function wechatMessageOther($message)
    {
        //return 'other';
    }
}
