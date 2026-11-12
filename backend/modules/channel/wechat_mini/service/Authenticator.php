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
declare(strict_types=1);

namespace modules\channel\wechat_mini\service;

use app\services\other\QrcodeServices;
use app\services\user\UserWechatAuthServices;
use crmeb\channel\BaseChannelAuthenticator;
use crmeb\exceptions\ApiException;

/**
 * 小程序渠道认证器
 * 封装小程序所有登录方式的认证逻辑，返回 uid 由统一入口 AuthController 调用 createToken
 * 不调用现有 RoutineServices 的登录方法（避免重复 createToken），直接调用底层组件完成认证
 * 继承渠道认证器统一契约基类（crmeb\channel\BaseChannelAuthenticator）
 * @package modules\channel\wechat_mini\service
 */
class Authenticator extends BaseChannelAuthenticator
{
    /**
     * 渠道登录认证（统一分发入口）
     * 按登录方式分发到对应能力方法，注册/注销无小程序差异化逻辑，走基类默认实现
     * @param string $loginType 登录方式（wechat_code_init/wechat_code/wechat_code_with_phone/mobile）
     * @param array $credentials 凭证
     * @return array 认证结果 ['uid'=>int, 'user_info'=>array, 'isbind'=>bool, 'bind_key'=>string, 'extra'=>array]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(string $loginType, array $credentials): array
    {
        return match ($loginType) {
            // 授权登录：两步式第一步（code 换 session）/ 第二步（凭缓存 key 认证）
            'wechat_code_init', 'wechat_code' => $this->oauthLogin($loginType, $credentials),
            // 授权 + 绑定手机号一步到位
            'wechat_code_with_phone' => $this->bindMobile($loginType, $credentials),
            // 手机号登录（验证码）
            'mobile' => $this->accountLogin($loginType, $credentials),
            default => throw new ApiException('不支持的登录方式: ' . $loginType),
        };
    }

    /**
     * 授权登录
     * 小程序 code 授权，含两步式授权的先后两个阶段
     * @param string $loginType 授权登录方式（wechat_code_init=第一步换session/wechat_code=第二步凭key认证）
     * @param array $credentials 凭证
     * @return array 统一认证结果
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function oauthLogin(string $loginType, array $credentials): array
    {
        return match ($loginType) {
            'wechat_code_init' => $this->authType($credentials),
            'wechat_code' => $this->authLogin($credentials),
            default => throw new ApiException('不支持的登录方式: ' . $loginType),
        };
    }

    /**
     * 绑定手机号
     * 授权 + 绑定手机号一步到位
     * @param string $loginType 绑定流程标识（wechat_code_with_phone）
     * @param array $credentials 凭证
     * @return array 统一认证结果
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function bindMobile(string $loginType, array $credentials): array
    {
        return $this->authBindingPhone($credentials);
    }

    /**
     * 账号登录
     * 手机号验证码登录
     * @param string $loginType 账号登录方式（mobile）
     * @param array $credentials 凭证
     * @return array 统一认证结果
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function accountLogin(string $loginType, array $credentials): array
    {
        return $this->phoneLogin($credentials);
    }

    /**
     * 两步式第一步：code 换 session，缓存用户数据，返回是否需要绑定手机号
     * 复用 RoutineServices::authType（该方法不调 createToken）
     * @param array $credentials ['code'=>string, 'spread'=>int, 'spid'=>string]
     * @return array
     */
    protected function authType(array $credentials): array
    {
        $code = $credentials['code'] ?? '';
        $spread = $credentials['spread'] ?? 0;
        $spid = $credentials['spid'] ?? '';
        if (!$code) {
            throw new ApiException('缺少授权code');
        }
        // 复用现有 authType 方法（不调 createToken，只返回 key + bindPhone）
        $result = app()->make(RoutineServices::class)->authType($code, $spread, $spid);
        return $this->authResult(0, [], (bool)$result['bindPhone'], (string)($result['key'] ?? ''));
    }

    /**
     * 两步式第二步：根据缓存 key 取用户数据，调 wechatOauthAfter 完成认证
     * @param array $credentials ['key'=>string]
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function authLogin(array $credentials): array
    {
        $key = $credentials['key'] ?? '';
        if (!$key) {
            throw new ApiException('缺少缓存key');
        }
        $createData = $this->getAuthCache($key);
        $user = $this->wechatOauthAfter($createData);
        return $this->authResult((int)$user['uid'], $user, false, '', [
            'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
        ]);
    }

    /**
     * 授权 + 绑定手机号一步到位
     * 用 code 换 session + 解密手机号，调 wechatOauthAfter 完成认证
     * @param array $credentials ['code'=>string, 'iv'=>string, 'encryptedData'=>string, 'spread'=>int, 'spid'=>string, 'key'=>string]
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function authBindingPhone(array $credentials): array
    {
        $code = $credentials['code'] ?? '';
        $iv = $credentials['iv'] ?? '';
        $encryptedData = $credentials['encryptedData'] ?? '';
        $spread = $credentials['spread'] ?? 0;
        $spid = $credentials['spid'] ?? '';
        $key = $credentials['key'] ?? '';

        if (!$code || !$iv || !$encryptedData) {
            throw new ApiException('缺少授权参数');
        }

        $wechatInfo = [];
        $agent_id = 0;
        $userType = $login_type = 'routine';
        // 若有缓存 key，从缓存恢复已授权数据
        if ($key) {
            $cachedData = $this->getAuthCache($key);
            [$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType] = $cachedData;
        }

        // 用 code 换 session + 解密手机号
        /** @var MiniProgram $oauth */
        $oauth = app()->make(MiniProgram::class);
        [$userInfoCong, $userInfo] = $oauth->oauth($code, [
            'iv'            => $iv,
            'encryptedData' => $encryptedData,
        ]);
        if (!$userInfo || !isset($userInfo['purePhoneNumber'])) {
            throw new ApiException('获取用户信息失败');
        }

        // 处理推广关系
        $spreadId = $spid ?? 0;
        if ($spread && ($info = app()->make(QrcodeServices::class)->getOne(['id' => $spread, 'status' => 1]))) {
            $spreadId = $info['third_id'];
        }

        $openid = $userInfoCong['openid'];
        $wechatInfo['openid'] = $openid;
        $wechatInfo['unionid'] = $userInfoCong['unionid'] ?? '';
        $wechatInfo['spid'] = $spreadId;
        $wechatInfo['code'] = $spread;
        $wechatInfo['session_key'] = $userInfoCong['session_key'];
        $wechatInfo['phone'] = $userInfo['purePhoneNumber'];

        $user = $this->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType]);
        return $this->authResult((int)$user['uid'], $user, false, '', [
            'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
        ]);
    }

    /**
     * 手机号登录（验证码）
     * @param array $credentials ['key'=>string, 'phone'=>string, 'spread'=>int, 'agent_id'=>int, 'spid'=>string, 'code'=>string]
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function phoneLogin(array $credentials): array
    {
        $key = $credentials['key'] ?? '';
        $phone = $credentials['phone'] ?? '';
        $spread = $credentials['spread'] ?? '';
        $agent_id = $credentials['agent_id'] ?? '';
        $spid = $credentials['spid'] ?? '';
        $code = $credentials['code'] ?? '';

        if (!$phone) {
            throw new ApiException('缺少手机号');
        }

        if ($code === '') {
            // 从缓存取已授权数据
            if (!$key) {
                throw new ApiException('缺少缓存key或授权code');
            }
            $cachedData = $this->getAuthCache($key);
            [$openid, $routineInfo, $spid, $agent_id, $login_type, $userType] = $cachedData;
            $routineInfo['phone'] = $phone;
            $createData = [$openid, $routineInfo, $spid, $agent_id, $login_type, $userType];
        } else {
            // 用 code 重新换 session
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

        $user = $this->wechatOauthAfter($createData);
        return $this->authResult((int)$user['uid'], $user, false, '', [
            'bindName' => (int)sys_config('get_avatar') && $user['avatar'] == sys_config('h5_avatar'),
        ]);
    }

    /**
     * 完成小程序用户创建/更新，返回 user 数组。
     * @param array $createData [$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType]
     * @return array
     */
    protected function wechatOauthAfter(array $createData): array
    {
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->wechatOauthAfter($createData);
        if (!$user || !isset($user['uid'])) {
            throw new ApiException('用户认证失败');
        }
        return $user;
    }
}
