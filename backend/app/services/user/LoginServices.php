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

namespace app\services\user;

use app\dao\user\UserDao;
use crmeb\basic\BaseService;
use app\services\message\channel\SmsChannel;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\utils\Http;
use crmeb\utils\JwtAuth;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

/**
 *
 * Class LoginServices
 * @package app\services\user
 */
class LoginServices extends BaseService
{
    /**
     * @var UserDao
     */
    protected $dao;

    /**
     * LoginServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 创建token
     * 统一汇聚点：token 生成成功后触发 UserLogin 事件，各渠道监听器处理登录后置业务
     * @param int $id 用户ID
     * @param string $type token 类型（api/admin）
     * @param string $pwd 密码（用于 token 签名）
     * @param string $channel 登录渠道（h5/wechat_mini/wechat_mp/app），传入则触发 UserLogin 事件
     * @param string $loginType 登录方式（password/mobile/wechat_code/wechat_auth/scan/apple）
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function createToken(int $id, $type, $pwd = '', string $channel = '', string $loginType = '')
    {
        /** @var JwtAuth $jwtAuth */
        $jwtAuth = app()->make(JwtAuth::class);
        if ($type == 'api' && !$this->dao->value(['uid' => $id], 'status')) {
            throw new ApiException('您已被禁止登录，请联系管理员');
        }
        if ($type == 'api') {
            $user = $this->dao->get($id);
            // pwd 在 User::$hidden 中，toArray()/toJson 会丢弃；必须读模型属性，
            // 否则指纹变成 md5('')，parseToken 比对必失败并提示「登录状态已变更」
            $pwd = (string)($user->pwd ?? '');
        }
        $token = $jwtAuth->createToken($id, $type, ['pwd' => md5($pwd)]);
        // 传入渠道才触发登录事件，避免内部 token 刷新等场景误触发
        if ($channel) {
            event('UserLogin', [['uid' => $id, 'channel' => $channel, 'login_type' => $loginType]]);
        }
        return $token;
    }

    /**
     * 退出登录
     * 统一触发 UserLogout 事件，各渠道监听器清理渠道登录态
     * 注意：token 缓存清理由调用方负责（需从 request 获取 token）
     * @param int $uid 用户ID
     * @param string $channel 登录渠道（h5/wechat_mini/wechat_mp/app）
     * @return void
     */
    public function logout(int $uid, string $channel = ''): void
    {
        if (!$uid) {
            return;
        }
        // 统一触发注销事件，渠道模块监听清理登录态
        event('UserLogout', [['uid' => $uid, 'channel' => $channel]]);
    }

    /**
     * H5账号登陆
     * @param $account
     * @param $password
     * @param $spread
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login($account, $password, $spread, $agent_id)
    {
        $user = $this->dao->getOne(['account|phone' => $account, 'is_del' => 0]);
        if ($user) {
            if ($user->pwd !== md5((string)$password))
                throw new ApiException('账号或密码错误');
            if ($user->pwd === md5('123456'))
                throw new ApiException('请修改您的初始密码，再尝试登录');
        } else {
            throw new ApiException('账号或密码错误');
        }
        if (!$user['status'])
            throw new ApiException('您已被禁止登录，请联系管理员');

        //更新用户信息
        if ($agent_id) {
            $this->updateUserInfo(['code' => $agent_id, 'is_staff' => 1], $user);
        } else {
            $this->updateUserInfo(['code' => $spread], $user);
        }
        $token = $this->createToken((int)$user['uid'], 'api', '', 'h5', 'password');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else
            throw new ApiException('登录失败');
    }

    /**
     * 更新用户信息
     * @param $user
     * @param $userInfo
     * @param false $is_new
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateUserInfo($user, $userInfo, $is_new = false)
    {
        $legacyUserInfo = $userInfo->toArray();
        $profileResult = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
            'args' => [(int)$userInfo['uid'], $legacyUserInfo],
        ]);
        $relationResult = event('DistributionServiceProxy', [
            'service' => 'DistributionRelationServices',
            'action' => 'relation',
            'args' => [(int)$userInfo['uid'], $legacyUserInfo],
        ]);
        // 分销模块未启用或历史用户没有扩展档案时，按未绑定分销关系处理。
        $agentUserInfo = array_replace(
            [
                'spread_uid' => 0,
                'spread_time' => 0,
                'is_agent' => 0,
                'is_division' => 0,
                'agent_id' => 0,
                'division_id' => 0,
                'staff_id' => 0,
            ],
            $legacyUserInfo,
            is_array($profileResult[0] ?? null) ? $profileResult[0] : [],
            is_array($relationResult[0] ?? null) ? $relationResult[0] : []
        );
        $data = [];
        $data['phone'] = !isset($user['phone']) || !$user['phone'] ? $userInfo->phone : $user['phone'];
        $data['last_time'] = time();
        $data['last_ip'] = app()->request->ip();
        $spreadUid = $user['code'] ?? 0;
        //如果扫了员工邀请码，上级，代理商，区域代理都会改动。
        if (isset($user['is_staff']) && !$agentUserInfo['is_agent'] && !$agentUserInfo['is_division']) {
            $spreadInfo = $this->dao->get($spreadUid);
            $spreadProfile = event('DistributionServiceProxy', [
                'service' => 'DistributionUserServices',
                'action' => 'profile',
                'args' => [(int)$spreadUid, $spreadInfo ? $spreadInfo->toArray() : []],
            ]);
            $spreadData = is_array($spreadProfile[0] ?? null) ? $spreadProfile[0] : ($spreadInfo ? $spreadInfo->toArray() : []);
            if ($userInfo['uid'] != $spreadUid) {
                $data['spread_uid'] = $spreadUid;
                $data['spread_time'] = $userInfo->last_time;
            }
            $data['agent_id'] = $spreadData['agent_id'] ?? 0;
            $data['division_id'] = $spreadData['division_id'] ?? 0;
            $data['staff_id'] = $userInfo['uid'];
            $data['is_staff'] = $user['is_staff'] ?? 0;
            $data['division_type'] = 3;
            $data['division_status'] = 1;
            $data['division_change_time'] = time();
            $data['division_end_time'] = $spreadData['division_end_time'] ?? 0;
            //如果店员切换代理商，则店员在之前代理商下推广的用户，他们的直接上级从当前店员变为之前代理商
            if ($agentUserInfo['agent_id'] != 0 && $agentUserInfo['agent_id'] != ($spreadData['agent_id'] ?? 0)) {
                event('DistributionServiceProxy', [
                    'service' => 'DistributionUserServices',
                    'action' => 'updateProfiles',
                    'args' => [['staff_id' => (int)$userInfo['uid']], ['staff_id' => 0]],
                ]);
            }
            //绑定用户后置事件（包含消息通知和绑定关系记录）
            event('UserRegister', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], $is_new]);

        } else {
            if ($is_new) {
                if ($spreadUid) {
                    $spreadInfo = $this->dao->get($spreadUid);
                    $spreadProfile = event('DistributionServiceProxy', [
                        'service' => 'DistributionUserServices',
                        'action' => 'profile',
                        'args' => [$spreadUid, $spreadInfo ? $spreadInfo->toArray() : []],
                    ]);
                    $spreadData = is_array($spreadProfile[0] ?? null) ? $spreadProfile[0] : ($spreadInfo ? $spreadInfo->toArray() : []);
                    $spreadUid = (int)$spreadUid;
                    $data['spread_uid'] = $spreadUid;
                    $data['spread_time'] = time();
                    $data['agent_id'] = $spreadData['agent_id'] ?? 0;
                    $data['division_id'] = $spreadData['division_id'] ?? 0;
                    $data['staff_id'] = $spreadData['staff_id'] ?? 0;
                    //绑定用户后置事件（包含消息通知和绑定关系记录）
                    event('UserRegister', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], 1]);
                }
            } else {
                // 已有上级时保持原关系；没有上级时允许建立一次推广关系。
                if ($agentUserInfo['spread_uid'] && !isset($user['is_staff'])) {
                    $data['login_type'] = $user['login_type'] ?? $userInfo->login_type;
                } else {
                    $upperRelation = event('DistributionServiceProxy', [
                        'service' => 'DistributionRelationServices',
                        'action' => 'relation',
                        'args' => [(int)$spreadUid, []],
                    ]);
                    $upperSpreadUid = (int)($upperRelation[0]['spread_uid'] ?? $this->dao->value(['uid' => $spreadUid], 'spread_uid'));
                    if ($spreadUid && $user['code'] != $userInfo->uid && $userInfo->uid != $upperSpreadUid) {
                        $spreadInfo = $this->dao->get($spreadUid);
                        $spreadProfile = event('DistributionServiceProxy', [
                            'service' => 'DistributionUserServices',
                            'action' => 'profile',
                            'args' => [(int)$spreadUid, $spreadInfo ? $spreadInfo->toArray() : []],
                        ]);
                        $spreadData = is_array($spreadProfile[0] ?? null) ? $spreadProfile[0] : ($spreadInfo ? $spreadInfo->toArray() : []);
                        $spreadUid = (int)$spreadUid;
                        $data['spread_uid'] = $spreadUid;
                        $data['spread_time'] = time();
                        $data['agent_id'] = $spreadData['agent_id'] ?? 0;
                        $data['division_id'] = $spreadData['division_id'] ?? 0;
                        $data['staff_id'] = $spreadData['staff_id'] ?? 0;
                        //绑定用户后置事件（包含消息通知和绑定关系记录）
                        event('UserRegister', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], 0]);
                    }
                }
            }
        }
        $agentFields = [
            'agent_id', 'division_id', 'staff_id', 'is_staff', 'division_type',
            'division_status', 'division_change_time', 'division_end_time',
        ];
        $agentData = array_intersect_key($data, array_flip($agentFields));
        if ($agentData) {
            // 事业部/代理/员工字段已从用户表移除：插件接管走插件档案，未接管时丢弃，不再写用户表
            event('DistributionServiceProxy', [
                'service' => 'DistributionUserServices',
                'action' => 'saveProfile',
                'args' => [(int)$userInfo['uid'], $agentData],
            ]);
            foreach ($agentFields as $field) {
                unset($data[$field]);
            }
        }
        if (array_key_exists('spread_uid', $data)) {
            $handled = event('DistributionServiceProxy', [
                'service' => 'DistributionRelationServices',
                'action' => 'bind',
                'args' => [(int)$userInfo['uid'], (int)$data['spread_uid'], (int)($data['spread_time'] ?? time())],
            ]);
            if (is_array($handled) && ($handled[0] ?? null) !== null) {
                unset($data['spread_uid'], $data['spread_time']);
            }
        }
        if (!$this->dao->update($userInfo['uid'], $data, 'uid')) {
            throw new ApiException('修改失败');
        }
        return true;
    }

    public function verify(SmsChannel $services, $phone, $type, $time)
    {
        if ($this->dao->getOne(['account' => $phone, 'is_del' => 0]) && $type == 'register') {
            throw new ApiException('手机号已注册');
        }
        $code = rand(100000, 999999);
        $data['code'] = $code;
        $data['time'] = $time;
        $res = $services->pushSms($phone, $data, 'verify_code');
        if ($res !== true)
            throw new ApiException('短信平台验证码发送失败');
        return $code;
    }

    /**
     * H5用户注册
     * @param $account
     * @param $password
     * @param $spread
     * @param string $user_type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register($account, $password, $spread, $user_type = 'h5')
    {
        if ($this->dao->getOne(['account|phone' => $account, 'is_del' => 0])) {
            throw new ApiException('手机号已注册');
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5((string)$password);
        $data['phone'] = $phone;
        $agentData = [];
        $pluginAgentEnabled = false;
        if ($spread) {
            $data['spread_uid'] = $spread;
            $data['spread_time'] = time();
            $spreadInfo = $userServices->get($spread);
            $spreadProfile = event('DistributionServiceProxy', [
                'service' => 'DistributionUserServices', 'action' => 'profile',
                'args' => [(int)$spread, $spreadInfo ? $spreadInfo->toArray() : []],
            ]);
            $pluginAgentEnabled = is_array($spreadProfile[0] ?? null);
            $profile = $pluginAgentEnabled ? $spreadProfile[0] : ($spreadInfo ? $spreadInfo->toArray() : []);
            if ($pluginAgentEnabled) {
                $data['division_id'] = $profile['division_id'] ?? 0;
                $data['agent_id'] = $profile['agent_id'] ?? 0;
                $data['staff_id'] = $profile['staff_id'] ?? 0;
                $agentData = array_intersect_key($data, array_flip(['division_id', 'agent_id', 'staff_id']));
                unset($data['spread_uid'], $data['spread_time'], $data['division_id'], $data['agent_id'], $data['staff_id']);
            }
        }
        $data['real_name'] = '';
        $data['birthday'] = 0;
        $data['card_id'] = '';
        $data['mark'] = '';
        $data['addres'] = '';
        $data['user_type'] = $user_type;
        $data['add_time'] = time();
        $data['add_ip'] = app('request')->ip();
        $data['last_time'] = time();
        $data['last_ip'] = app('request')->ip();
        $data['nickname'] = substr_replace($account, '****', 3, 4);
        $data['avatar'] = sys_config('h5_avatar');
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        $data['status'] = 1;
        if (!$re = $this->dao->save($data)) {
            throw new ApiException('注册失败');
        } else {
            if ($pluginAgentEnabled && $agentData) {
                event('DistributionServiceProxy', ['service' => 'DistributionUserServices', 'action' => 'saveProfile', 'args' => [(int)$re->uid, $agentData]]);
            }
            //用户生成后置事件
            event('UserRegister', [$spread, $user_type, $data['nickname'], $re->uid, 1]);

            return $re;
        }
    }

    /**
     * 重置密码
     * @param $account
     * @param $password
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function reset($account, $password)
    {
        $user = $this->dao->getOne(['account|phone' => $account, 'is_del' => 0], 'uid');
        if (!$user) {
            throw new ApiException('用户不存在');
        }
        if (!$this->dao->update($user['uid'], ['pwd' => md5((string)$password)], 'uid')) {
            throw new ApiException('修改密码失败');
        }
        return true;
    }

    /**
     * 手机号登录
     * @param $phone
     * @param $spread
     * @param string $user_type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function mobile($phone, $spread, string $user_type = 'h5', $agent_id = 0)
    {
        //数据库查询
        $user = $this->dao->getOne(['account|phone' => $phone, 'is_del' => 0]);
        if (!$user) {
            $user = $this->register($phone, '123456', $spread, $user_type);
            if (!$user) {
                throw new ApiException('用户登录失败,无法生成新用户,请稍后再试');
            }
        }

        if (!$user->status)
            throw new ApiException('您已被禁止登录，请联系管理员');

        // 设置推广关系
        if ($agent_id) {
            $this->updateUserInfo(['code' => $agent_id, 'is_staff' => 1], $user);
        } else {
            $this->updateUserInfo(['code' => $spread], $user);
        }

        $token = $this->createToken((int)$user['uid'], 'api', '', $user_type, 'mobile');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException('登录失败');
        }
    }

    /**
     * 切换登录
     * @param $user
     * @param $from
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function switchAccount($user, $from)
    {
        if ($from === 'h5') {
            $where = [['phone', '=', $user['phone']], ['user_type', '<>', 'h5'], ['is_del', '=', 0]];
            $login_type = 'wechat';
        } else {
            //数据库查询
            $where = [['account|phone', '=', $user['phone']], ['user_type', '=', 'h5'], ['is_del', '=', 0]];
            $login_type = 'h5';
        }
        $switch_user = $this->dao->getOne($where);
        if (!$switch_user) {
            return app('json')->fail('用户不存在,无法切换');
        }
        if (!$switch_user->status) {
            return app('json')->fail('您已被禁止登录，请联系管理员');
        }
        $edit_data = ['login_type' => $login_type];
        if (!$this->dao->update($switch_user['uid'], $edit_data, 'uid')) {
            throw new ApiException('修改新用户登录类型出错');
        }
        $token = $this->createToken((int)$switch_user['uid'], 'api', '', $login_type === 'wechat' ? 'wechat_mp' : 'h5', 'switch');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException('登录失败');
        }
    }

    /**
     * 绑定手机号(静默还没写入用户信息)
     * @param $phone
     * @param string $key
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bindind_phone($phone, string $key = '')
    {
        if (!$key) {
            throw new ApiException('请刷新页面或者重新授权');
        }
        $createData = CacheService::get($key);
        if (!is_array($createData) || count($createData) < 6) {
            throw new ApiException('请刷新页面或者重新授权');
        }
        [$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType] = $createData;
        $wechatInfo['phone'] = $phone;
        /** @var UserWechatAuthServices $wechatAuthServices */
        $wechatAuthServices = app()->make(UserWechatAuthServices::class);
        $user = $wechatAuthServices->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType]);
        if (!$user || !isset($user['uid'])) {
            throw new ApiException('用户认证失败，请重新登录');
        }
        $token = $this->createToken((int)$user['uid'], 'api', '', $userType ?: 'wechat_mp', 'bind_phone');
        if ($token) {
            return [
                'token' => $token['token'],
                'userInfo' => $user,
                'expires_time' => $token['params']['exp'],
            ];
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 用户绑定手机号
     * @param int $uid
     * @param $phone
     * @param $step
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userBindindPhone(int $uid, $phone, $step)
    {
        $userInfo = $this->dao->get($uid);
        if (!$userInfo) {
            throw new ApiException('用户不存在');
        }
        if ($this->dao->getOne([['phone', '=', $phone], ['user_type', '<>', 'h5'], ['is_del', '=', 0]])) {
            throw new ApiException('此手机已经绑定，无法多次绑定');
        }
        if ($userInfo->phone) {
            throw new ApiException('您的账号已经绑定过手机号码');
        }
        $data = [];
        if ($this->dao->getOne(['account' => $phone, 'phone' => $phone, 'user_type' => 'h5', 'is_del' => 0])) {
            if (!$step) return ['msg' => 'H5已有账号是否绑定此账号上', 'data' => ['is_bind' => 1]];
        } else {
            $data['account'] = $phone;
        }
        $data['phone'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid') || $userInfo->phone == $phone)
            return ['msg' => '绑定成功', 'data' => []];
        else
            throw new ApiException('绑定失败');
    }

    /**
     * 用户绑定手机号
     * @param int $uid
     * @param $phone
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateBindindPhone(int $uid, $phone)
    {
        $userInfo = $this->dao->get(['uid' => $uid, 'is_del' => 0]);
        if (!$userInfo) {
            throw new ApiException('用户不存在');
        }
        if ($userInfo->phone == $phone) {
            throw new ApiException('新手机号和原手机号相同，无需修改');
        }
        if ($this->dao->getOne([['phone', '=', $phone], ['is_del', '=', 0]])) {
            throw new ApiException('此手机已经注册');
        }
        $data = [];
        $data['phone'] = $phone;
        $data['account'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid'))
            return ['msg' => '修改成功', 'data' => []];
        else
            throw new ApiException('修改失败');
    }

    /**
     * 远程注册登录
     * @param string $out_token
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/21
     */
    public function remoteRegister(string $out_token = '')
    {
        // 远程登录令牌格式：urlsafe_base64(json) + '.' + hash_hmac('sha256', urlsafe_base64(json), APP_KEY)
        // 仅接受持有本站 APP_KEY 的可信系统签发，杜绝伪造 uid 登录/注册（安全审计 A3）
        $parts = explode('.', $out_token);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new ApiException('远程登录令牌无效');
        }
        [$payloadB64, $remoteSign] = $parts;
        $expected = hash_hmac('sha256', $payloadB64, JwtAuth::secretKey());
        if (!hash_equals($expected, (string)$remoteSign)) {
            throw new ApiException('远程登录令牌无效');
        }
        try {
            $info = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadB64));
        } catch (\Throwable $e) {
            throw new ApiException('远程登录令牌无效');
        }
        if (!$info instanceof \stdClass || !isset($info->uid) || (int)$info->uid <= 0) {
            throw new ApiException('远程登录令牌无效');
        }
        // 仅允许「注册新用户」：已存在 uid 一律拒绝，避免 debug 下凭 HMAC 冒充任意账号登录
        $userInfo = $this->dao->get(['uid' => $info->uid]);
        if ($userInfo) {
            throw new ApiException('远程登录仅支持注册新用户，禁止登录已有账号');
        }
        $data = [];
        $data['uid'] = $info->uid;
        $data['account'] = ($info->phone ?? '') !== '' ? $info->phone : 'out_' . $info->uid;
        $data['phone'] = $info->phone ?? '';
        $data['pwd'] = md5('123456');
        $data['real_name'] = $info->nickname ?? '';
        $data['birthday'] = 0;
        $data['card_id'] = '';
        $data['mark'] = '';
        $data['addres'] = '';
        $data['user_type'] = 'h5';
        $data['add_time'] = time();
        $data['add_ip'] = app('request')->ip();
        $data['last_time'] = time();
        $data['last_ip'] = app('request')->ip();
        $data['nickname'] = $info->nickname ?? '';
        $data['avatar'] = $info->avatar ?? '';
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        $data['status'] = 1;
        $data['now_money'] = $info->now_money ?? 0;
        $data['integral'] = $info->integral ?? 0;
        $data['exp'] = $info->exp ?? 0;
        $this->dao->save($data);
        $token = $this->createToken((int)$info->uid, 'api', '', 'h5', 'remote_register');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException('登录失败');
        }
    }

    /**
     * 校验 Apple identityToken，返回经 Apple 公钥验签后的用户身份
     * 仅信任 JWT 中的 sub，禁止使用客户端自报 openId
     * @param string $identityToken Apple 授权返回的 identityToken（JWT）
     * @return array{openId:string,email:string}
     */
    public function verifyAppleIdentityToken(string $identityToken): array
    {
        $identityToken = trim($identityToken);
        if ($identityToken === '') {
            throw new ApiException('缺少 Apple 授权凭证');
        }

        $clientId = trim((string)sys_config('apple_client_id'));
        if ($clientId === '') {
            throw new ApiException('请先在后台「应用 → APP → APP配置」填写 Apple Client ID（iOS Bundle ID）');
        }

        $jwks = CacheService::get('apple_auth_jwks');
        if (!is_array($jwks) || empty($jwks['keys'])) {
            $body = Http::getRequest('https://appleid.apple.com/auth/keys');
            if (!is_string($body) || $body === '') {
                throw new ApiException('获取 Apple 公钥失败');
            }
            $jwks = json_decode($body, true);
            if (!is_array($jwks) || empty($jwks['keys'])) {
                throw new ApiException('Apple 公钥无效');
            }
            CacheService::set('apple_auth_jwks', $jwks, 86400);
        }

        $prevLeeway = JWT::$leeway;
        try {
            JWT::$leeway = 60;
            $payload = JWT::decode($identityToken, JWK::parseKeySet($jwks), ['RS256']);
        } catch (\Throwable $e) {
            throw new ApiException('Apple 授权校验失败，请重新登录');
        } finally {
            JWT::$leeway = $prevLeeway;
        }

        $iss = (string)($payload->iss ?? '');
        $aud = (string)($payload->aud ?? '');
        $sub = trim((string)($payload->sub ?? ''));
        if ($iss !== 'https://appleid.apple.com' || $aud !== $clientId || $sub === '') {
            throw new ApiException('Apple 授权校验失败，请重新登录');
        }

        return [
            'openId' => $sub,
            'email' => trim((string)($payload->email ?? '')),
        ];
    }
}
