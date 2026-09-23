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
declare (strict_types=1);

namespace modules\channel\wechat_mini\service;

use crmeb\basic\BaseService;
use app\services\other\QrcodeServices;
use app\services\user\LoginServices;
use app\services\user\UserServices;
use app\services\user\UserVisitServices;
use app\services\user\UserWechatAuthServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use modules\channel\wechat_mini\sdk\MiniProgramService;
use modules\channel\wechat_mini\service\MiniProgram;

/**
 *
 * Class RoutineServices
 * @package app\services\wechat
 */
class RoutineServices extends BaseService
{

    /**
     * 返回用户信息的缓存key，返回是否强制绑定手机号
     * @param $code
     * @param $spread
     * @param $spid
     * @return array
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authType($code, $spread, $spid)
    {
        $agent_id = 0;
        $userInfoConfig = app()->make(MiniProgram::class)->oauth($code, ['silence' => true]);
        if (!isset($userInfoConfig['openid'])) {
            throw new ApiException('静默授权失败');
        }
        $routineInfo = ['unionid' => $userInfoConfig['unionid'] ?? ''];
        $info = app()->make(QrcodeServices::class)->getOne(['id' => $spread, 'status' => 1]);
        if ($spread && $info) {
            if ($info['third_type'] == 'agent') {
                $agent_id = $info['third_id'];
            } else {
                $spid = $info['third_id'];
            }
        }
        $openid = $userInfoConfig['openid'];
        $routineInfo['openid'] = $openid;
        $routineInfo['spid'] = $spid;
        $routineInfo['code'] = $spread;
        $routineInfo['session_key'] = $userInfoConfig['session_key'];
        $routineInfo['headimgurl'] = sys_config('h5_avatar');
        $createData = [$openid, $routineInfo, $spid, $agent_id, 'routine', 'routine'];
        $userInfoKey = md5($openid . '_' . time() . '_routine');
        CacheService::set($userInfoKey, $createData, 7200);
        $bindPhone = false;
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->getAuthUserInfo($openid, 'routine');
        if (sys_config('store_user_mobile') && (($user && $user['phone'] === '') || !$user)) $bindPhone = true;
        return ['bindPhone' => $bindPhone, 'key' => $userInfoKey];
    }

    /**
     * 根据缓存获取token
     * @param $key
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function authLogin($key)
    {
        $createData = CacheService::get($key);
        if (!is_array($createData) || count($createData) < 6) {
            throw new ApiException('授权已过期，请重新登录');
        }
        //写入用户信息
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->wechatOauthAfter($createData);
        if (!$user || !isset($user['uid'])) {
            throw new ApiException('用户认证失败，请重新登录');
        }
        $token = app()->make(\app\services\user\LoginServices::class)->createToken((int)$user['uid'], 'api', '', 'wechat_mini', 'wechat_code');
        if ($token) {
            app()->make(UserVisitServices::class)->loginSaveVisit($user);
            return [
                'token' => $token['token'],
                'expires_time' => $token['params']['exp'],
                'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
            ];
        } else {
            throw new ApiException('登录失败');
        }
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
        $agent_id = 0;
        $userType = $login_type = 'routine';
        if ($key) {
            $cachedData = CacheService::get($key);
            if (!is_array($cachedData) || count($cachedData) < 6) {
                throw new ApiException('授权已过期，请重新登录');
            }
            [$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType] = $cachedData;
        }

        /** @var MiniProgram $oauth */
        $oauth = app()->make(MiniProgram::class);
        [$userInfoCong, $userInfo] = $oauth->oauth($code, [
            'iv' => $iv,
            'encryptedData' => $encryptedData
        ]);
        $session_key = $userInfoCong['session_key'];
        if (!$userInfo || !isset($userInfo['purePhoneNumber'])) {
            throw new ApiException('获取用户信息失败');
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
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType]);
        if (!$user || !isset($user['uid'])) {
            throw new ApiException('用户认证失败，请重新登录');
        }
        $token = app()->make(\app\services\user\LoginServices::class)->createToken((int)$user['uid'], 'api', '', 'wechat_mini', 'wechat_code_with_phone');
        if ($token) {
            app()->make(UserVisitServices::class)->loginSaveVisit($user);
            return [
                'token' => $token['token'],
                'expires_time' => $token['params']['exp'],
                'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
            ];
        } else {
            throw new ApiException('登录失败');
        }
    }

    /**
     * 小程序手机号登录
     * @param $key
     * @param $phone
     * @param string $spread_code
     * @param string $spread_spid
     * @param string $code
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/12
     */
    public function phoneLogin($key, $phone, $spread = '', $agent_id = '', $spid = '', $code = '')
    {
        if ($code === '') {
            $cachedData = CacheService::get($key);
            if (!is_array($cachedData) || count($cachedData) < 6) {
                throw new ApiException('授权已过期，请重新登录');
            }
            [$openid, $routineInfo, $spid, $agent_id, $login_type, $userType] = $cachedData;
            $routineInfo['phone'] = $phone;
            $createData = [$openid, $routineInfo, $spid, $agent_id, $login_type, $userType];
        } else {
            $userInfoConfig = app()->make(MiniProgram::class)->oauth($code, ['silence' => true]);
            if (!isset($userInfoConfig['openid'])) {
                throw new ApiException('静默授权失败');
            }
            $routineInfo = ['unionid' => $userInfoConfig['unionid'] ?? ''];
            $info = app()->make(QrcodeServices::class)->getOne(['id' => $spread, 'status' => 1]);
            if ($spread && $info) {
                $spid = $info['third_id'];
            }
            $openid = $userInfoConfig['openid'];
            $routineInfo['openid'] = $openid;
            $routineInfo['spid'] = $spid;
            $routineInfo['code'] = $spread;
            $routineInfo['session_key'] = $userInfoConfig['session_key'];
            $routineInfo['headimgurl'] = sys_config('h5_avatar');
            $routineInfo['phone'] = $phone;
            $createData = [$openid, $routineInfo, $spid, $agent_id, 'routine', 'routine'];
        }
        //写入用户信息
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->wechatOauthAfter($createData);
        if (!$user || !isset($user['uid'])) {
            throw new ApiException('用户认证失败，请重新登录');
        }
        $token = app()->make(\app\services\user\LoginServices::class)->createToken((int)$user['uid'], 'api', '', 'wechat_mini', 'mobile');
        if ($token) {
            app()->make(UserVisitServices::class)->loginSaveVisit($user);
            return [
                'token' => $token['token'],
                'expires_time' => $token['params']['exp'],
                'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
            ];
        } else {
            throw new ApiException('登录失败');
        }
    }

    /**
     * 小程序绑定手机号
     * @param $code
     * @param $iv
     * @param $encryptedData
     * @return bool
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/24
     */
    public function bindingPhone($code, $iv, $encryptedData)
    {
        [$userInfoCong, $userInfo] = app()->make(MiniProgram::class)->oauth($code, [
            'iv' => $iv,
            'encryptedData' => $encryptedData
        ]);
        if (!$userInfo || !isset($userInfo['purePhoneNumber'])) {
            throw new ApiException('获取用户信息失败');
        }
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $uid = $wechatAuthServices->openidToUid($userInfoCong['openid'], 'openid', 'routine');
        $userServices = app()->make(UserServices::class);
        if ($userServices->count(['phone' => $userInfo['purePhoneNumber'], 'is_del' => 0])) {
            throw new ApiException('手机号已注册');
        }
        $res = $userServices->update(['uid' => $uid], ['phone' => $userInfo['purePhoneNumber']]);
        if ($res) return true;
        throw new ApiException('绑定失败');
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
     * 获取小程序订阅消息id（从 eb_notice_template.extra 读取）
     * @return array ['mark' => 'tempid']
     */
    public function tempIds(): array
    {
        $templates = CacheService::remember('TEMP_IDS_LIST', function () {
            /** @var \app\dao\message\NoticeTemplateDao $templateDao */
            $templateDao = app()->make(\app\dao\message\NoticeTemplateDao::class);
            $channels = $templateDao->getEnabledRoutineTemplates();

            $result = [];
            foreach ($channels as $channel) {
                $extra = !empty($channel['extra']) ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra']) : [];
                if (!empty($extra['tempid'])) {
                    $result[$channel['mark']] = $extra['tempid'];
                }
            }
            return $result;
        }, 300);
        // 缓存封装在查询失败时可能返回 null，订阅模板缺失不应中断页面。
        if (!is_array($templates)) {
            \think\facade\Log::error('[RoutineServices] 小程序订阅模板读取失败，请检查通知配置表及缓存连接');
            return [];
        }
        return $templates;
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
            throw new ApiException('数据不存在');
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
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $updateRes = $wechatAuthServices->update(['uid' => $user['uid'], 'user_type' => 'routine'], $userInfo);
        if (!$updateRes) {
            throw new ApiException('更新失败');
        }
        return true;
    }
}
