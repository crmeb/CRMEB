<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message\wechat;


use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\combination\StorePinkServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\BaseServices;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreProductServices;
use app\services\user\LoginServices;
use app\services\user\UserServices;
use app\services\wechat\WechatQrcodeServices;
use app\services\wechat\WechatReplyServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\app\WechatService;
use think\facade\Log;

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
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        /** @var LoginServices $loginService */
        $loginService = app()->make(LoginServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);

        $response = $wechatReplyService->reply('subscribe');
        if ($message->EventKey && ($qrInfo = $qrcodeService->getQrcode($message->Ticket, 'ticket'))) {
            $qrcodeService->scanQrcode($message->Ticket, 'ticket');
            $thirdType = explode('-', $qrInfo['third_type']);
            $baseUrl = sys_config('site_url');
            if (in_array(strtolower($thirdType[0]), ['spread', 'agent', 'wechatqrcode', 'product', 'combination', 'seckill', 'bargain', 'pink'])) {
                //扫码需要生成用户流程
                $spreadUid = $qrInfo['third_id'];
                $spreadInfo = $userService->get($spreadUid);
                $is_new = $wechatUser->saveUser($message->FromUserName);
                $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                $userInfo = $userService->get($uid);
                if (strtolower($thirdType[0]) == 'spread') {
                    try {
                        if ($spreadUid == $uid) {
                            $response = '自己不能推荐自己';
                        } else if (!$userInfo) {
                            $response = '用户不存在';
                        } else if (!$spreadInfo) {
                            $response = '上级用户不存在';
                        } else if ($userInfo['spread_uid']) {
                            $response = '已有推荐人!';
                        } else if (!$loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new)) {
                            $response = '绑定推荐人失败!';
                        }
                        $wechatNews['title'] = sys_config('site_name');
                        $wechatNews['image'] = sys_config('wap_login_logo');
                        $wechatNews['url'] = $baseUrl . '/pages/index/index';
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'agent') {
                    try {
                        if ($spreadUid == $uid) {
                            $response = '自己不能推荐自己';
                        } else if (!$userInfo) {
                            $response = '用户不存在';
                        } else if (!$spreadInfo) {
                            $response = '上级用户不存在';
                        } else if ($userInfo->is_division) {
                            $response = '您是事业部,不能绑定成为别人的员工';
                        } else if ($userInfo->is_agent) {
                            $response = '您是代理商,不能绑定成为别人的员工';
                        } else if ($loginService->updateUserInfo(['code' => $spreadUid, 'is_staff' => 1], $userInfo, $is_new)) {
                            $response = '绑定店员成功!';
                        }
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'wechatqrcode') {
                    /** @var WechatQrcodeServices $wechatQrcodeService */
                    $wechatQrcodeService = app()->make(WechatQrcodeServices::class);
                    try {
                        //wechatqrcode类型的二维码数据中,third_id为渠道码的id
                        $qrcodeInfo = $wechatQrcodeService->qrcodeInfo($qrInfo['third_id']);
                        $spreadUid = $qrcodeInfo['uid'];
                        $spreadInfo = $userService->get($spreadUid);
                        $is_new = $wechatUser->saveUser($message->FromUserName);
                        $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                        $userInfo = $userService->get($uid);
                        if ($qrcodeInfo['status'] == 0 || $qrcodeInfo['is_del'] == 1 || ($qrcodeInfo['end_time'] < time() && $qrcodeInfo['end_time'] > 0)) {
                            $response = '二维码已失效';
                        } else if ($spreadUid == $uid) {
                            $response = '自己不能推荐自己';
                        } else if (!$userInfo) {
                            $response = '用户不存在';
                        } else if (!$spreadInfo) {
                            $response = '上级用户不存在';
                        } else if ($loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new)) {
                            //写入扫码记录,返回内容
                            $response = $wechatQrcodeService->wechatQrcodeRecord($qrcodeInfo, $userInfo, $spreadInfo);
                        }
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'product') {
                    try {
                        /** @var StoreProductServices $productService */
                        $productService = app()->make(StoreProductServices::class);
                        $productInfo = $productService->get($thirdType[1] ?? 0);
                        $wechatNews['title'] = $productInfo->store_name;
                        $wechatNews['image'] = $productInfo->image;
                        $wechatNews['description'] = $productInfo->store_info;
                        $wechatNews['url'] = $baseUrl . '/pages/goods_details/index?id=' . $thirdType[1];
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'combination') {
                    try {
                        /** @var StoreCombinationServices $combinationService */
                        $combinationService = app()->make(StoreCombinationServices::class);
                        $productInfo = $combinationService->get($thirdType[1] ?? 0);
                        $wechatNews['title'] = $productInfo->title;
                        $wechatNews['image'] = $productInfo->image;
                        $wechatNews['description'] = $productInfo->info;
                        $wechatNews['url'] = $baseUrl . '/pages/activity/goods_combination_details/index?id=' . $thirdType[1];
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'seckill') {
                    try {
                        /** @var StoreSeckillServices $seckillService */
                        $seckillService = app()->make(StoreSeckillServices::class);
                        $productInfo = $seckillService->get($thirdType[1] ?? 0);
                        $wechatNews['title'] = $productInfo->title;
                        $wechatNews['image'] = $productInfo->image;
                        $wechatNews['description'] = $productInfo->info;
                        $wechatNews['url'] = $baseUrl . '/pages/activity/goods_seckill_details/index?id=' . $thirdType[1];
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'bargain') {
                    try {
                        /** @var StoreBargainServices $bargainService */
                        $bargainService = app()->make(StoreBargainServices::class);
                        $productInfo = $bargainService->get($thirdType[1] ?? 0);
                        $wechatNews['title'] = $productInfo->title;
                        $wechatNews['image'] = $productInfo->image;
                        $wechatNews['description'] = $productInfo->info;
                        $wechatNews['url'] = $baseUrl . '/pages/activity/goods_bargain_details/index?id=' . $thirdType[1] . '&bargain=' . $thirdType[2];
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                } elseif (strtolower($thirdType[0]) == 'pink') {
                    try {
                        /** @var StorePinkServices $pinkService */
                        $pinkService = app()->make(StorePinkServices::class);
                        /** @var StoreCombinationServices $combinationService */
                        $combinationService = app()->make(StoreCombinationServices::class);
                        $pinktInfo = $pinkService->get($thirdType[1]);
                        $productInfo = $combinationService->get($pinktInfo->cid);
                        $wechatNews['title'] = $productInfo->title;
                        $wechatNews['image'] = $productInfo->image;
                        $wechatNews['description'] = $productInfo->info;
                        $wechatNews['url'] = $baseUrl . '/pages/activity/goods_combination_status/index?id=' . $thirdType[1];
                        $loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new);
                        $messages = WechatService::newsMessage($wechatNews);
                        WechatService::staffService()->message($messages)->to($message->FromUserName)->send();
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                }
            } else {
                //扫码不生成用户流程
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
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        /** @var LoginServices $loginService */
        $loginService = app()->make(LoginServices::class);

        $response = $wechatReplyService->reply('subscribe');
        if (isset($message->EventKey)) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            if ($message->EventKey && ($qrInfo = $qrcodeService->getQrcode($message->Ticket, 'ticket'))) {
                $qrcodeService->scanQrcode($message->Ticket, 'ticket');
                if (strtolower($qrInfo['third_type']) == 'spread') {
                    try {
                        $spreadUid = $qrInfo['third_id'];
                        $wechatUser->saveUser($message->FromUserName);
                        $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                        if ($spreadUid == $uid) return '自己不能推荐自己';
                        $userInfo = $userService->get($uid);
                        if ($userInfo['spread_uid']) return '已有推荐人!';
                        $userInfo->spread_uid = $spreadUid;
                        $userInfo->spread_time = time();
                        if (!$userInfo->save()) {
                            return '绑定推荐人失败!';
                        }
                    } catch (\Exception $e) {
                        $response = $e->getMessage();
                    }
                }
            }
            if (strtolower($qrInfo['third_type']) == 'agent') {
                try {
                    $spreadUid = $qrInfo['third_id'];
                    $spreadInfo = $userService->get($spreadUid);
                    $is_new = $wechatUser->saveUser($message->FromUserName);
                    $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                    $userInfo = $userService->get($uid);
                    if ($spreadUid == $uid) {
                        $response = '自己不能推荐自己';
                    } else if (!$userInfo) {
                        $response = '用户不存在';
                    } else if (!$spreadInfo) {
                        $response = '上级用户不存在';
                    } else if ($userInfo->is_division) {
                        $response = '您是事业部,不能绑定成为别人的员工';
                    } else if ($userInfo->is_agent) {
                        $response = '您是代理商,不能绑定成为别人的员工';
                    } else if ($loginService->updateUserInfo(['code' => $spreadUid, 'is_staff' => 1], $userInfo, $is_new)) {
                        $response = '绑定店员成功!';
                    }
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
            if (strtolower($qrInfo['third_type']) == 'wechatqrcode') {
                /** @var WechatQrcodeServices $wechatQrcodeService */
                $wechatQrcodeService = app()->make(WechatQrcodeServices::class);
                try {
                    //wechatqrcode类型的二维码数据中,third_id为渠道码的id
                    $qrcodeInfo = $wechatQrcodeService->qrcodeInfo($qrInfo['third_id']);
                    $spreadUid = $qrcodeInfo['uid'];
                    $spreadInfo = $userService->get($spreadUid);
                    $is_new = $wechatUser->saveUser($message->FromUserName);
                    $uid = $wechatUser->getFieldValue($message->FromUserName, 'openid', 'uid', ['user_type', '<>', 'h5']);
                    $userInfo = $userService->get($uid);

                    if ($qrcodeInfo['status'] == 0 || $qrcodeInfo['is_del'] == 1 || ($qrcodeInfo['end_time'] < time() && $qrcodeInfo['end_time'] > 0)) {
                        $response = '二维码已失效';
                    } else if ($spreadUid == $uid) {
                        $response = '自己不能推荐自己';
                    } else if (!$userInfo) {
                        $response = '用户不存在';
                    } else if (!$spreadInfo) {
                        $response = '上级用户不存在';
                    } else if ($loginService->updateUserInfo(['code' => $spreadUid], $userInfo, $is_new)) {
                        //写入扫码记录,返回内容
                        $response = $wechatQrcodeService->wechatQrcodeRecord($qrcodeInfo, $userInfo, $spreadInfo, 1);
                    }
                } catch (\Exception $e) {
                    $response = $e->getMessage();
                }
            }
        }

        // 更新关注标识
        if (!is_string($response)) {
            $wechatUser->subscribe($message->FromUserName);
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
