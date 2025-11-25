<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\user;

use app\dao\user\UserDao;
use app\services\BaseServices;
use app\services\yihaotong\SmsRecordServices;
use app\services\message\notice\SmsService;
use app\services\wechat\WechatUserServices;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use Firebase\JWT\JWT;
use think\facade\Config;

/**
 *
 * Class LoginServices
 * @package app\services\user
 */
class LoginServices extends BaseServices
{

    /**
     * LoginServices constructor.
     * @param UserDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
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
                throw new ApiException(410025);
            if ($user->pwd === md5('123456'))
                throw new ApiException(410026);
        } else {
            throw new ApiException(410025);
        }
        if (!$user['status'])
            throw new ApiException(410027);

        //更新用户信息
        if ($agent_id) {
            $this->updateUserInfo(['code' => $agent_id, 'is_staff' => 1], $user);
        } else {
            $this->updateUserInfo(['code' => $spread], $user);
        }
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else
            throw new ApiException(410019);
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
        $data = [];
        $data['phone'] = !isset($user['phone']) || !$user['phone'] ? $userInfo->phone : $user['phone'];
        $data['last_time'] = time();
        $data['last_ip'] = app()->request->ip();
        $spreadUid = $user['code'] ?? 0;
        //如果扫了员工邀请码，上级，代理商，区域代理都会改动。
        if (isset($user['is_staff']) && !$userInfo['is_agent'] && !$userInfo['is_division']) {
            $spreadInfo = $this->dao->get($spreadUid);
            if ($userInfo['uid'] != $spreadUid) {
                $data['spread_uid'] = $spreadUid;
                $data['spread_time'] = $userInfo->last_time;
            }
            $data['agent_id'] = $spreadInfo->agent_id;
            $data['division_id'] = $spreadInfo->division_id;
            $data['staff_id'] = $userInfo['uid'];
            $data['is_staff'] = $user['is_staff'] ?? 0;
            $data['division_type'] = 3;
            $data['division_status'] = 1;
            $data['division_change_time'] = time();
            $data['division_end_time'] = $spreadInfo->division_end_time;
            //如果店员切换代理商，则店员在之前代理商下推广的用户，他们的直接上级从当前店员变为之前代理商
            if ($userInfo->agent_id != 0 && $userInfo->agent_id != $spreadInfo->agent_id) {
                $this->dao->update(['staff_id' => $userInfo['uid'], 'spread_uid' => $userInfo['uid']], ['spread_uid' => $spreadInfo['agent_id'], 'staff_id' => 0]);
                $this->dao->update(['staff_id' => $userInfo['uid'], 'not_spread_uid' => $userInfo['uid']], ['staff_id' => 0]);
            }
            //绑定用户后置事件
            event('UserRegisterListener', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], $is_new]);
            //推送消息
            event('NoticeListener', [['spreadUid' => $spreadUid, 'user_type' => $userInfo['user_type'], 'nickname' => $userInfo['nickname']], 'bind_spread_uid']);

            //自定义事件-绑定关系
            event('CustomEventListener', ['user_spread', [
                'uid' => $userInfo['uid'],
                'nickname' => $userInfo['nickname'],
                'spread_uid' => $spreadUid,
                'spread_time' => date('Y-m-d H:i:s'),
                'user_type' => $userInfo['user_type'],
            ]]);

        } else {
            if ($is_new) {
                if ($spreadUid) {
                    $spreadInfo = $this->dao->get($spreadUid);
                    $spreadUid = (int)$spreadUid;
                    $data['spread_uid'] = $spreadUid;
                    $data['spread_time'] = time();
                    $data['agent_id'] = $spreadInfo->agent_id;
                    $data['division_id'] = $spreadInfo->division_id;
                    $data['staff_id'] = $spreadInfo->staff_id;
                    //绑定用户后置事件
                    event('UserRegisterListener', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], 1]);
                    //推送消息
                    event('NoticeListener', [['spreadUid' => $spreadUid, 'user_type' => $userInfo['user_type'], 'nickname' => $userInfo['nickname']], 'bind_spread_uid']);

                    //自定义事件-绑定关系
                    event('CustomEventListener', ['user_spread', [
                        'uid' => $userInfo['uid'],
                        'nickname' => $userInfo['nickname'],
                        'spread_uid' => $spreadUid,
                        'spread_time' => date('Y-m-d H:i:s'),
                        'user_type' => $userInfo['user_type'],
                    ]]);
                }
            } else {
                //永久绑定
                $store_brokerage_binding_status = sys_config('store_brokerage_binding_status', 1);
                if ($userInfo->spread_uid && $store_brokerage_binding_status == 1 && !isset($user['is_staff'])) {
                    $data['login_type'] = $user['login_type'] ?? $userInfo->login_type;
                } else {
                    //绑定分销关系 = 所有用户
                    if (sys_config('brokerage_bindind', 1) == 1) {
                        //分销绑定类型为时间段且过期 ｜｜临时
                        $store_brokerage_binding_time = sys_config('store_brokerage_binding_time', 30);
                        if (!$userInfo['spread_uid'] || $store_brokerage_binding_status == 3 || ($store_brokerage_binding_status == 2 && ($userInfo['spread_time'] + $store_brokerage_binding_time * 24 * 3600) < time())) {
                            if ($spreadUid && $user['code'] != $userInfo->uid && $userInfo->uid != $this->dao->value(['uid' => $spreadUid], 'spread_uid')) {
                                $spreadInfo = $this->dao->get($spreadUid);
                                $spreadUid = (int)$spreadUid;
                                $data['spread_uid'] = $spreadUid;
                                $data['spread_time'] = time();
                                $data['agent_id'] = $spreadInfo->agent_id;
                                $data['division_id'] = $spreadInfo->division_id;
                                $data['staff_id'] = $spreadInfo->staff_id;
                                //绑定用户后置事件
                                event('UserRegisterListener', [$spreadUid, $userInfo['user_type'], $userInfo['nickname'], $userInfo['uid'], 0]);
                                //推送消息
                                event('NoticeListener', [['spreadUid' => $spreadUid, 'user_type' => $userInfo['user_type'], 'nickname' => $userInfo['nickname']], 'bind_spread_uid']);

                                //自定义事件-绑定关系
                                event('CustomEventListener', ['user_spread', [
                                    'uid' => $userInfo['uid'],
                                    'nickname' => $userInfo['nickname'],
                                    'spread_uid' => $spreadUid,
                                    'spread_time' => date('Y-m-d H:i:s'),
                                    'user_type' => $userInfo['user_type'],
                                ]]);
                            }
                        }
                    }
                }
            }
        }
        if (!$this->dao->update($userInfo['uid'], $data, 'uid')) {
            throw new ApiException(100007);
        }
        return true;
    }

    public function verify(SmsService $services, $phone, $type, $time)
    {
        if ($this->dao->getOne(['account' => $phone, 'is_del' => 0]) && $type == 'register') {
            throw new ApiException(410028);
        }
        $code = rand(100000, 999999);
        $data['code'] = $code;
        $data['time'] = $time;
        $res = $services->send(true, $phone, $data, 'verify_code');
        if ($res !== true)
            throw new ApiException(410031);
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
            throw new ApiException(410028);
        }
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5((string)$password);
        $data['phone'] = $phone;
        if ($spread) {
            $data['spread_uid'] = $spread;
            $data['spread_time'] = time();
            $spreadInfo = $userServices->get($spread);
            $data['division_id'] = $spreadInfo['division_id'];
            $data['agent_id'] = $spreadInfo['agent_id'];
            $data['staff_id'] = $spreadInfo['staff_id'];
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
            throw new ApiException(410014);
        } else {
            $userServices->rewardNewUser((int)$re->uid);
            //用户生成后置事件
            event('UserRegisterListener', [$spread, $user_type, $data['nickname'], $re->uid, 1]);

            //自定义事件-用户注册
            event('CustomEventListener', ['user_register', [
                'uid' => $re->uid,
                'nickname' => $data['nickname'],
                'phone' => $data['phone'],
                'add_time' => date('Y-m-d H:i:s'),
                'user_type' => $user_type,
            ]]);

            if ($spread) {
                //推送消息
                event('NoticeListener', [['spreadUid' => $spread, 'user_type' => $user_type, 'nickname' => $data['nickname']], 'bind_spread_uid']);

                //自定义事件-绑定关系
                event('CustomEventListener', ['user_spread', [
                    'uid' => $re->uid,
                    'nickname' => $data['nickname'],
                    'spread_uid' => $spread,
                    'spread_time' => date('Y-m-d H:i:s'),
                    'user_type' => $user_type,
                ]]);
            }
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
            throw new ApiException(410032);
        }
        if (!$this->dao->update($user['uid'], ['pwd' => md5((string)$password)], 'uid')) {
            throw new ApiException(410033);
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
                throw new ApiException(410034);
            }
        }

        if (!$user->status)
            throw new ApiException(410027);

        // 设置推广关系
        if ($agent_id) {
            $this->updateUserInfo(['code' => $agent_id, 'is_staff' => 1], $user);
        } else {
            $this->updateUserInfo(['code' => $spread], $user);
        }

        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException(410019);
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
            return app('json')->fail(410035);
        }
        if (!$switch_user->status) {
            return app('json')->fail(410027);
        }
        $edit_data = ['login_type' => $login_type];
        if (!$this->dao->update($switch_user['uid'], $edit_data, 'uid')) {
            throw new ApiException(410036);
        }
        $token = $this->createToken((int)$switch_user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException(410019);
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
            throw new ApiException(410037);
        }
        [$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType] = $createData = CacheService::get($key);
        if (!$createData) {
            throw new ApiException(410037);
        }
        $wechatInfo['phone'] = $phone;
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        //更新用户信息
        $user = $wechatUser->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $agent_id, $login_type, $userType]);
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return [
                'token' => $token['token'],
                'userInfo' => $user,
                'expires_time' => $token['params']['exp'],
            ];
        } else
            return app('json')->fail(410019);
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
            throw new ApiException(410113);
        }
        if ($this->dao->getOne([['phone', '=', $phone], ['user_type', '<>', 'h5'], ['is_del', '=', 0]])) {
            throw new ApiException(410039);
        }
        if ($userInfo->phone) {
            throw new ApiException(410040);
        }
        $data = [];
        if ($this->dao->getOne(['account' => $phone, 'phone' => $phone, 'user_type' => 'h5', 'is_del' => 0])) {
            if (!$step) return ['msg' => 410041, 'data' => ['is_bind' => 1]];
        } else {
            $data['account'] = $phone;
        }
        $data['phone'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid') || $userInfo->phone == $phone)
            return ['msg' => 410016, 'data' => []];
        else
            throw new ApiException(410017);
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
            throw new ApiException(410113);
        }
        if ($userInfo->phone == $phone) {
            throw new ApiException(410042);
        }
        if ($this->dao->getOne([['phone', '=', $phone], ['is_del', '=', 0]])) {
            throw new ApiException(410043);
        }
        $data = [];
        $data['phone'] = $phone;
        $data['account'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid'))
            return ['msg' => 100001, 'data' => []];
        else
            throw new ApiException(100007);
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
        $info = JWT::jsonDecode(JWT::urlsafeB64Decode($out_token));
        $userInfo = $this->dao->get(['uid' => $info->uid]);
        $data = [];
        if (!$userInfo) {
            $data['uid'] = $info->uid;
            $data['account'] = $info->phone != '' ? $info->phone : 'out_' . $info->uid;
            $data['phone'] = $info->phone;
            $data['pwd'] = md5('123456');
            $data['real_name'] = $info->nickname;
            $data['birthday'] = 0;
            $data['card_id'] = '';
            $data['mark'] = '';
            $data['addres'] = '';
            $data['user_type'] = 'h5';
            $data['add_time'] = time();
            $data['add_ip'] = app('request')->ip();
            $data['last_time'] = time();
            $data['last_ip'] = app('request')->ip();
            $data['nickname'] = $info->nickname;
            $data['avatar'] = $info->avatar;
            $data['city'] = '';
            $data['language'] = '';
            $data['province'] = '';
            $data['country'] = '';
            $data['status'] = 1;
            $data['now_money'] = $info->now_money;
            $data['integral'] = $info->integral;
            $data['exp'] = $info->exp;
            $this->dao->save($data);
        } else {
            $data['nickname'] = $info->nickname;
            $data['avatar'] = $info->avatar;
            $data['now_money'] = $info->now_money;
            $data['integral'] = $info->integral;
            $data['exp'] = $info->exp;
            $this->dao->update($info->uid, $data);
        }
        $token = $this->createToken((int)$info->uid, 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ApiException('登录失败');
        }
    }
}
