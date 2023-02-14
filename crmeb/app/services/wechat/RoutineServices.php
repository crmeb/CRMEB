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
declare (strict_types=1);

namespace app\services\wechat;

use app\services\BaseServices;
use app\dao\wechat\WechatUserDao;
use app\services\message\SystemNotificationServices;
use app\services\message\TemplateMessageServices;
use app\services\other\QrcodeServices;
use app\services\user\LoginServices;
use app\services\user\UserServices;
use app\services\user\UserVisitServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\app\MiniProgramService;
use crmeb\services\oauth\OAuth;

/**
 *
 * Class RoutineServices
 * @package app\services\wechat
 */
class RoutineServices extends BaseServices
{

    /**
     * RoutineServices constructor.
     * @param WechatUserDao $dao
     */
    public function __construct(WechatUserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 小程序授权登录
     * @param $code
     * @param $post_cache_key
     * @param $login_type
     * @param $spread_spid
     * @param $spread_code
     * @param $iv
     * @param $encryptedData
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function mp_auth($code, $post_cache_key, $login_type, $spread_spid, $spread_code, $iv, $encryptedData)
    {
        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        [$userInfoCong, $userInfo] = $oauth->oauth($code, [
            'iv' => $iv,
            'encryptedData' => $encryptedData
        ]);
        $session_key = $userInfoCong['session_key'];
        $userInfo['unionId'] = isset($userInfoCong['unionid']) ? $userInfoCong['unionid'] : '';
        $userInfo['openId'] = $openid = $userInfoCong['openid'];
        $userInfo['spid'] = $spread_spid;
        $userInfo['code'] = $spread_code;
        $userInfo['session_key'] = $session_key;
        $userInfo['login_type'] = $login_type;
        $createData = $this->routineOauth($userInfo);

        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $user = $wechatUserServices->getAuthUserInfo($openid, 'routine');
        if (!$user) {
            $user = $wechatUserServices->wechatOauthAfter($createData);
        } else {
            //更新用户信息
            $wechatUserServices->wechatUpdata([$user['uid'], $createData[1]]);
        }
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            /** @var UserVisitServices $visitServices */
            $visitServices = app()->make(UserVisitServices::class);
            $visitServices->loginSaveVisit($user);
            return [
                'userInfo' => $user
            ];
        } else
            throw new ApiException(410038);
    }

    /**
     * 小程序授权登录
     * @param $code
     * @param $spid
     * @param $spread
     * @param $iv
     * @param $encryptedData
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function newAuth($code, $spid, $spread, $iv, $encryptedData)
    {
        if (!$code) {
            throw new ApiException(100100);
        }
        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        [$userInfoCong, $userInfo] = $oauth->oauth($code, [
            'iv' => $iv,
            'encryptedData' => $encryptedData
        ]);
        $session_key = $userInfoCong['session_key'];

        $userInfo['unionId'] = isset($userInfoCong['unionid']) ? $userInfoCong['unionid'] : '';
        $userInfo['openId'] = $openid = $userInfoCong['openid'];
        $userInfo['spid'] = $spid;
        $userInfo['code'] = $spread;
        $userInfo['session_key'] = $session_key;
        $userInfo['login_type'] = 'routine';
        $createData = $this->routineOauth($userInfo);
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $user = $wechatUserServices->getAuthUserInfo($openid, 'routine');
        //获取是否强制绑定手机号
        $storeUserMobile = sys_config('store_user_mobile');
        if ($storeUserMobile && !$user) {
            $userInfoKey = md5($openid . '_' . time() . '_rouine');
            CacheService::set($userInfoKey, $createData, 7200);
            return ['key' => $userInfoKey];
        } else if (!$user) {
            $user = $wechatUserServices->wechatOauthAfter($createData);
        } else {
            //更新用户信息
            $wechatUserServices->wechatUpdata([$user['uid'], $createData[1]]);
        }
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            /** @var UserVisitServices $visitServices */
            $visitServices = app()->make(UserVisitServices::class);
            $visitServices->loginSaveVisit($user);
            $token['userInfo'] = $user;
            return $token;
        } else
            throw new ApiException(410019);
    }

    /**
     * 小程序创建用户后返回uid
     * @param $routine
     * @return array
     */
    public function routineOauth($routine)
    {
        $routineInfo['nickname'] = filter_emoji($routine['nickName']);//姓名
        $routineInfo['sex'] = $routine['gender'];//性别
        $routineInfo['language'] = $routine['language'];//语言
        $routineInfo['city'] = $routine['city'];//城市
        $routineInfo['province'] = $routine['province'];//省份
        $routineInfo['country'] = $routine['country'];//国家
        $routineInfo['headimgurl'] = $routine['avatarUrl'];//头像
        $routineInfo['openid'] = $routine['openId'];
        $routineInfo['session_key'] = $routine['session_key'];//会话密匙
        $routineInfo['unionid'] = $routine['unionId'];//用户在开放平台的唯一标识符
        $routineInfo['user_type'] = 'routine';//用户类型
        $routineInfo['phone'] = $routine['phone'] ?? $routine['purePhoneNumber'] ?? '';
        $spid = $routine['spid'] ?? 0;//绑定关系uid
        //获取是否有扫码进小程序
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        if (isset($routine['code']) && $routine['code'] && ($info = $qrcode->get($routine['code']))) {
            $spid = $info['third_id'];
        }
        return [$routine['openId'], $routineInfo, $spid, $routine['login_type'] ?? 'routine', 'routine'];
    }

    /**
     * 小程序支付回调
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function notify()
    {
        return MiniProgramService::handleNotify();
    }

    /**
     * 获取小程序订阅消息id
     * @return bool|mixed|null
     */
    public function tempIds()
    {
        return CacheService::remember('TEMP_IDS_LIST', function () {
            /** @var SystemNotificationServices $sysNotify */
            $sysNotify = app()->make(SystemNotificationServices::class);
            $marks = $sysNotify->getColumn([['routine_id', '>', 0]], 'routine_id', 'mark');
            $ids = array_values($marks);
            /** @var TemplateMessageServices $tempMsgServices */
            $tempMsgServices = app()->make(TemplateMessageServices::class);
            $list = $tempMsgServices->getColumn([['id', 'in', $ids]], 'tempid', 'id');

            $tempIdsList = [];
            foreach ($marks as $key => $item) {
                $tempIdsList[$key] = $list[$item];
            }
            return $tempIdsList;
        });
    }

    /**
     * 获取小程序直播列表
     * @param $page
     * @param $limit
     * @return array|bool|mixed
     */
    public function live($page, $limit)
    {
        $list = CacheService::remember('WECHAT_LIVE_LIST_' . $page . '_' . $limit, function () use ($page, $limit) {
            $list = MiniProgramService::getLiveInfo((int)$page, (int)$limit);
            foreach ($list as &$item) {
                $item['_start_time'] = date('m-d H:i', $item['start_time']);
            }
            return $list;
        }, 600) ?: [];
        return $list;
    }

    /**
     * 静默授权
     * @param $code
     * @param $spread
     * @param $spid
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuth($code, $spread, $spid)
    {

        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        $userInfoConfig = $oauth->oauth($code, ['silence' => true]);
        if (!isset($userInfoConfig['openid'])) {
            throw new ApiException(410078);
        }
        $routineInfo = [
            'unionid' => $userInfoConfig['unionid'] ?? ''
        ];
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        if ($spread && ($info = $qrcode->getOne(['id' => $spread, 'status' => 1]))) {
            $spid = $info['third_id'];
        }

        $openid = $userInfoConfig['openid'];
        $routineInfo['openid'] = $openid;
        $routineInfo['headimgurl'] = sys_config('h5_avatar');
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $user = $wechatUserServices->getAuthUserInfo($openid, 'routine');
        $createData = [$openid, $routineInfo, $spid, '', 'routine'];
        //获取是否强制绑定手机号
        $storeUserMobile = sys_config('store_user_mobile');
        if ($storeUserMobile && !$user) {
            $userInfoKey = md5($openid . '_' . time() . '_routine');
            CacheService::set($userInfoKey, $createData, 7200);
            return ['key' => $userInfoKey];
        } else if (!$user) {
            //写入用户信息
            $user = $wechatUserServices->wechatOauthAfter($createData);
            $token = $this->createToken((int)$user['uid'], 'api');
            if ($token) {
                $token['new_user'] = (int)sys_config('get_avatar', 0);
                return $token;
            } else
                throw new ApiException(410019);
        } else {
            if (!$user['status']) throw new ApiException(410027);
            //更新用户信息
            $wechatUserServices->wechatUpdata([$user['uid'], ['code' => $spid]]);
            $token = $this->createToken((int)$user['uid'], 'api');
            /** @var UserVisitServices $visitServices */
            $visitServices = app()->make(UserVisitServices::class);
            $visitServices->loginSaveVisit($user);
            if ($token) {
                $token['new_user'] = 0;
                return $token;
            } else
                throw new ApiException(410019);
        }

    }

    /**
     * 静默授权
     * @param $code
     * @param $spread
     * @param $spid
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function silenceAuthNoLogin($code, $spread, $spid)
    {
        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        $userInfoConfig = $oauth->oauth($code, ['silence' => true]);
        if (!isset($userInfoConfig['openid'])) {
            throw new ApiException(410078);
        }
        $routineInfo = [
            'unionid' => $userInfoConfig['unionid'] ?? ''
        ];
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        if ($spread && ($info = $qrcode->getOne(['id' => $spread, 'status' => 1]))) {
            $spid = $info['third_id'];
        }

        $openid = $userInfoConfig['openid'];
        $routineInfo['openid'] = $openid;
        $routineInfo['headimgurl'] = sys_config('h5_avatar');
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $user = $wechatUserServices->getAuthUserInfo($openid, 'routine');
        $createData = [$openid, $routineInfo, $spid, '', 'routine'];

        if (!$user) {
            $userInfoKey = md5($openid . '_' . time() . '_routine');
            CacheService::set($userInfoKey, $createData, 7200);
            return ['auth_login' => 1, 'key' => $userInfoKey];
        } else {
            //更新用户信息
            $wechatUserServices->wechatUpdata([$user['uid'], ['code' => $spid]]);
            $token = $this->createToken((int)$user['uid'], 'api');
            /** @var UserVisitServices $visitServices */
            $visitServices = app()->make(UserVisitServices::class);
            $visitServices->loginSaveVisit($user);
            if ($token) {
                $token['userInfo'] = $user;
                return $token;
            } else
                throw new ApiException(410019);
        }

    }

    /**
     * 手机号登录 静默授权绑定关系
     * @param $code
     * @param $spread
     * @param $spid
     * @param $phone
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function silenceAuthBindingPhone($code, $spread, $spid, $phone)
    {
        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        $userInfoConfig = $oauth->oauth($code, ['silence' => true]);
        if (!isset($userInfoConfig['openid'])) {
            throw new ApiException(410078);
        }
        $routineInfo = [
            'unionid' => $userInfoConfig['unionid'] ?? ''
        ];
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        if ($spread && ($info = $qrcode->getOne(['id' => $spread, 'status' => 1]))) {
            $spid = $info['third_id'];
        }

        $openid = $userInfoConfig['openid'];
        $routineInfo['openid'] = $openid;
        $routineInfo['headimgurl'] = sys_config('h5_avatar');
        $routineInfo['phone'] = $phone;
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        $createData = [$openid, $routineInfo, $spid, '', 'routine'];
        //写入用户信息
        $user = $wechatUserServices->wechatOauthAfter($createData);
        $token = $this->createToken((int)$user['uid'], 'api');
        /** @var UserVisitServices $visitServices */
        $visitServices = app()->make(UserVisitServices::class);
        $visitServices->loginSaveVisit($user);
        if ($token) {
            $token['new_user'] = $user['new_user'];
            return $token;
        } else
            throw new ApiException(410019);
    }

    /**
     * 自动获取手机号绑定
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @param $spread
     * @param $spid
     * @param string $key
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function authBindingPhone($code, $iv, $encryptedData, $spread, $spid, $key = '')
    {
        $wechatInfo = [];
        $userInfo = [];
        $userType = $login_type = 'routine';
        if ($key) {
            [$openid, $wechatInfo, $spreadId, $login_type, $userType] = $createData = CacheService::get($key);
        }

        /** @var OAuth $oauth */
        $oauth = app()->make(OAuth::class, ['mini_program']);
        [$userInfoCong, $userInfo] = $oauth->oauth($code, [
            'iv' => $iv,
            'encryptedData' => $encryptedData
        ]);
        $session_key = $userInfoCong['session_key'];
        if (!$userInfo || !isset($userInfo['purePhoneNumber'])) {
            throw new ApiException(410079);
        }

        $spreadId = $spid ?? 0;
        /** @var QrcodeServices $qrcode */
        $qrcode = app()->make(QrcodeServices::class);
        if ($spread && ($info = $qrcode->getOne(['id' => $spread, 'status' => 1]))) {
            $spreadId = $info['third_id'];
        }
        $openid = $userInfoCong['openid'];
        $wechatInfo['openid'] = $openid;
        $wechatInfo['unionid'] = $userInfoCong['unionid'] ?? '';
        $wechatInfo['spid'] = $spreadId;
        $wechatInfo['code'] = $spread;
        $wechatInfo['session_key'] = $session_key;
        $wechatInfo['phone'] = $userInfo['purePhoneNumber'];
        /** @var WechatUserServices $wechatUserServices */
        $wechatUserServices = app()->make(WechatUserServices::class);
        //写入用户信息
        $user = $wechatUserServices->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $login_type, $userType]);
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            /** @var UserVisitServices $visitServices */
            $visitServices = app()->make(UserVisitServices::class);
            $visitServices->loginSaveVisit($user);
            return [
                'token' => $token['token'],
                'userInfo' => $user,
                'expires_time' => $token['params']['exp'],
            ];
        } else
            throw new ApiException(410019);
    }


    /**
     * 更新用户信息
     * @param $uid
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateUserInfo($uid, array $data)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw new ApiException(100026);
        }
        $userInfo = [];
        $userInfo['nickname'] = filter_emoji($data['nickName'] ?? '');//姓名
        $userInfo['sex'] = $data['gender'] ?? '';//性别
        $userInfo['language'] = $data['language'] ?? '';//语言
        $userInfo['city'] = $data['city'] ?? '';//城市
        $userInfo['province'] = $data['province'] ?? '';//省份
        $userInfo['country'] = $data['country'] ?? '';//国家
        $userInfo['headimgurl'] = $data['avatarUrl'] ?? '';//头像
        $userInfo['is_complete'] = 1;
        /** @var LoginServices $loginService */
        $loginService = app()->make(LoginServices::class);
        $loginService->updateUserInfo($userInfo, $user);
        //更新用户信息
        if (!$this->dao->update(['uid' => $user['uid'], 'user_type' => 'routine'], $userInfo)) {
            throw new ApiException(100013);
        }
        return true;
    }
}
