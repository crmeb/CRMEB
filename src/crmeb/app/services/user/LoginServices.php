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
declare (strict_types=1);

namespace app\services\user;

use app\services\BaseServices;
use app\dao\user\UserDao;
use app\services\coupon\StoreCouponIssueServices;
use app\services\message\sms\SmsRecordServices;
use app\services\message\sms\SmsSendServices;
use app\services\wechat\WechatUserServices;
use crmeb\jobs\RoutineTemplateJob;
use crmeb\jobs\WechatTemplateJob as TemplateJob;
use crmeb\services\CacheService;
use crmeb\utils\Queue;
use think\exception\ValidateException;
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
     * @param LoginDao $dao
     */
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * H5账号登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login($account, $password, $spread)
    {
        $user = $this->dao->getOne(['account|phone' => $account]);
        if ($user) {
            if ($user->pwd !== md5((string)$password))
                throw new ValidateException('账号或密码错误');
            if ($user->pwd === md5('123456'))
                throw new ValidateException('请修改您的初始密码，再尝试登录！');
        } else {
            throw new ValidateException('账号或密码错误');
        }
        if (!$user['status'])
            throw new ValidateException('已被禁止，请联系管理员');

        //更新用户信息
        $this->updateUserInfo(['code' => $spread], $user);
        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else
            throw new ValidateException('登录失败');
    }

    /**
     * 更新用户信息
     * @param $user
     * @param $uid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateUserInfo($user, $userInfo)
    {
        $data = [];
        $data['nickname'] = !isset($user['nickname']) || !$user['nickname'] ? $userInfo->nickname : $user['nickname'];
        $data['avatar'] = !isset($user['headimgurl']) || !$user['headimgurl'] ? $userInfo->avatar : $user['headimgurl'];
        $data['phone'] = !isset($user['phone']) || !$user['phone'] ? $userInfo->phone : $user['phone'];
        $data['last_time'] = time();
        $data['last_ip'] = app()->request->ip();
        if ($userInfo->spread_uid) {
            $data['login_type'] = $user['login_type'] ?? $userInfo->login_type;
        } else {
            //绑定分销关系 = 所有用户
            if (sys_config('brokerage_bindind', 1) == 1) {
                $spreadUid = isset($user['code']) && $user['code'] && $user['code'] != $userInfo->uid ? $user['code'] : 0;
                if ($spreadUid && $userInfo->uid == $this->dao->value(['uid' => $spreadUid], 'spread_uid')) {
                    $spreadUid = 0;
                }
                if ($spreadUid) {
                    $spreadUid = (int)$spreadUid;
                    $data['spread_uid'] = $spreadUid;
                    $data['spread_time'] = $userInfo->last_time;
                    $this->dao->incField($spreadUid, 'spread_count', 1);
                    //绑定成功发送消息
                    /** @var  WechatUserServices $wechatServices */
                    $wechatServices = app()->make(WechatUserServices::class);
                    if (strtolower($userInfo->user_type) == 'routine') {
                        $openid = $wechatServices->uidToOpenid($spreadUid, 'routine');
                        Queue::instance()->do('sendBindSpreadUidSuccess')->job(RoutineTemplateJob::class)->data($openid, $user['nickname'] ?? '')->push();
                    } else {
                        $openid = $wechatServices->uidToOpenid($spreadUid, 'wechat');
                        Queue::instance()->do('sendBindSpreadUidSuccess')->job(TemplateJob::class)->data($openid, $user['nickname'] ?? '')->push();
                    }
                }
            }
        }
        if (!$this->dao->update($userInfo['uid'], $data, 'uid')) {
            throw new ValidateException('修改信息失败');
        }
        return true;
    }

    public function verify(SmsSendServices $services, $phone, $type, $time, $ip)
    {
        if ($this->dao->getOne(['account' => $phone]) && $type == 'register') {
            throw new ValidateException('手机号已注册');
        }
        $default = Config::get('sms.default', 'yunxin');
        $defaultMaxPhoneCount = Config::get('sms.maxPhoneCount', 10);
        $defaultMaxIpCount = Config::get('sms.maxIpCount', 50);
        $maxPhoneCount = Config::get('sms.stores.' . $default . '.maxPhoneCount', $defaultMaxPhoneCount);
        $maxIpCount = Config::get('sms.stores.' . $default . '.maxIpCount', $defaultMaxIpCount);
        /** @var SmsRecordServices $smsRecord */
        $smsRecord = app()->make(SmsRecordServices::class);
        if ($smsRecord->count(['phone' => $phone, 'add_ip' => $ip, 'time' => 'today']) >= $maxPhoneCount) {
            throw new ValidateException('您今日发送得短信次数已经达到上限');
        }
        if ($smsRecord->count(['add_ip' => $ip, 'time' => 'today']) >= $maxIpCount) {
            throw new ValidateException('此IP今日发送次数已经达到上限');
        }
        if (CacheService::get('code_' . $phone))
            throw new ValidateException($time . '分钟内有效');
        $code = rand(100000, 999999);
        $data['code'] = $code;
        $data['time'] = $time;
        $res = $services->send(true, $phone, $data, 'VERIFICATION_CODE_TIME');
        if ($res !== true)
            throw new ValidateException('短信平台验证码发送失败' . $res);
        return $code;
    }

    /**
     * H5用户注册
     * @param $account
     * @param $password
     * @param $spread
     * @return User|\think\Model
     */
    public function register($account, $password, $spread, $user_type = 'h5')
    {
        if ($this->dao->getOne(['account|phone' => $account])) {
            throw new ValidateException('用户已存在,请去修改密码');
        }
        $phone = $account;
        $data['account'] = $account;
        $data['pwd'] = md5((string)$password);
        $data['phone'] = $phone;
        if ($spread) {
            $data['spread_uid'] = $spread;
            $data['spread_time'] = time();
            /** @var UserBillServices $userBill */
            $userBill = app()->make(UserBillServices::class);
            //邀请新用户增加经验
            $userBill->inviteUserIncExp((int)$spread);
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
        $data['nickname'] = substr(md5($account . time()), 0, 12);
        $data['avatar'] = $data['headimgurl'] = sys_config('h5_avatar');
        $data['city'] = '';
        $data['language'] = '';
        $data['province'] = '';
        $data['country'] = '';
        $data['status'] = 1;
        if (!$re = $this->dao->save($data)) {
            throw new ValidateException('注册失败');
        } else {
            /** @var StoreCouponIssueServices $storeCoupon */
            $storeCoupon = app()->make(StoreCouponIssueServices::class);
            $storeCoupon->userFirstSubGiveCoupon((int)$re->uid);
            return $re;
        }
    }

    /**
     * 重置密码
     * @param $account
     * @param $password
     */
    public function reset($account, $password)
    {
        $user = $this->dao->getOne(['account|phone' => $account]);
        if (!$user) {
            throw new ValidateException('用户不存在');
        }
        if (!$this->dao->update($user['uid'], ['pwd' => md5((string)$password)], 'uid')) {
            throw new ValidateException('修改密码失败');
        }
        return true;
    }

    /**
     * 手机号登录
     * @param $phone
     * @param $spread
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function mobile($phone, $spread, $user_type = 'h5')
    {
        //数据库查询
        $user = $this->dao->getOne(['phone' => $phone]);
        if (!$user) {
            $user = $this->register($phone, '123456', $spread, $user_type);
            if (!$user) {
                throw new ValidateException('用户登录失败,无法生成新用户,请稍后再试!');
            }
        }

        if (!$user->status)
            throw new ValidateException('已被禁止，请联系管理员');

        // 设置推广关系
        $this->updateUserInfo(['code' => $spread], $user);

        $token = $this->createToken((int)$user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ValidateException('登录失败');
        }
    }

    /**
     * 切换登录
     * @param $user
     * @param $from
     */
    public function switchAccount($user, $from)
    {
        if ($from === 'h5') {
            $where = [['phone', '=', $user['phone']], ['user_type', '<>', 'h5']];
            $login_type = 'wechat';
        } else {
            //数据库查询
            $where = [['account|phone', '=', $user['phone']], ['user_type', '=', 'h5']];
            $login_type = 'h5';
        }
        $switch_user = $this->dao->getOne($where);
        if (!$switch_user) {
            return app('json')->fail('用户不存在,无法切换');
        }
        if (!$switch_user->status) {
            return app('json')->fail('已被禁止，请联系管理员');
        }
        $edit_data = ['login_type' => $login_type];
        if (!$this->dao->update($switch_user['uid'], $edit_data, 'uid')) {
            throw new ValidateException('修改新用户登录类型出错');
        }
        $token = $this->createToken((int)$switch_user['uid'], 'api');
        if ($token) {
            return ['token' => $token['token'], 'expires_time' => $token['params']['exp']];
        } else {
            throw new ValidateException('切换失败');
        }
    }

    /**
     * 绑定手机号(静默还没写入用户信息)
     * @param $user
     * @param $phone
     * @param $step
     * @return mixed
     */
    public function bindind_phone($phone, $key = '')
    {
        if (!$key) {
            throw new ValidateException('请刷新页面或者重新授权');
        }
        [$openid, $wechatInfo, $spreadId, $login_type, $userType] = $createData = CacheService::getTokenBucket($key);
        if (!$createData) {
            throw new ValidateException('请刷新页面或者重新授权');
        }
        $wechatInfo['phone'] = $phone;
        /** @var WechatUserServices $wechatUser */
        $wechatUser = app()->make(WechatUserServices::class);
        //更新用户信息
        $user = $wechatUser->wechatOauthAfter([$openid, $wechatInfo, $spreadId, $login_type, $userType]);
        $token = $this->createToken((int)$user['uid'], $userType);
        if ($token) {
            return [
                'token' => $token['token'],
                'userInfo' => $user,
                'expires_time' => $token['params']['exp'],
            ];
        } else
            return app('json')->fail('获取用户访问token失败!');
    }

    /**
     * 用户绑定手机号
     * @param $user
     * @param $phone
     * @param $step
     * @return mixed
     */
    public function userBindindPhone(int $uid, $phone, $step)
    {
        $userInfo = $this->dao->get($uid);
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        if ($this->dao->getOne([['phone', '=', $phone], ['user_type', '<>', 'h5']])) {
            throw new ValidateException('此手机已经绑定，无法多次绑定！');
        }
        if ($userInfo->phone) {
            throw new ValidateException('您的账号已经绑定过手机号码！');
        }
        $data = [];
        if ($this->dao->getOne(['account' => $phone, 'phone' => $phone, 'user_type' => 'h5'])) {
            if (!$step) return ['msg' => 'H5已有账号是否绑定此账号上', 'data' => ['is_bind' => 1]];
        } else {
            $data['account'] = $phone;
        }
        $data['phone'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid') || $userInfo->phone == $phone)
            return ['msg' => '绑定成功', 'data' => []];
        else
            throw new ValidateException('绑定失败');
    }

    /**
     * 用户绑定手机号
     * @param $user
     * @param $phone
     * @param $step
     * @return mixed
     */
    public function updateBindindPhone(int $uid, $phone)
    {
        $userInfo = $this->dao->get($uid);
        if (!$userInfo) {
            throw new ValidateException('用户不存在');
        }
        if ($userInfo->phone == $phone) {
            throw new ValidateException('新手机号和原手机号相同，无需修改');
        }
        if ($this->dao->getOne([['phone', '=', $phone]])) {
            throw new ValidateException('此手机已经注册');
        }
        $data = [];
        $data['phone'] = $phone;
        if ($this->dao->update($userInfo['uid'], $data, 'uid'))
            return ['msg' => '修改成功', 'data' => []];
        else
            throw new ValidateException('修改失败');
    }
}
