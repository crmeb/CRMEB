<?php

namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemConfig;
use crmeb\services\HttpService;
use crmeb\services\JsonService;
use crmeb\services\SMSService;
use crmeb\services\UtilService;

/**
 * 短信账号
 * Class SmsAdmin
 * @package app\admin\controller\sms
 */
class SmsAdmin extends AuthController
{
    /**
     * @return string
     */
    public function index()
    {
        return $this->fetch();
    }

    public function captcha()
    {
        if (!request()->isPost()) return JsonService::fail('发生失败');
        $phone = request()->param('phone');
        if (!trim($phone)) return JsonService::fail('请填写手机号');

        $res = json_decode(HttpService::getRequest(SMSService::code(), compact('phone')), true);
        if (!isset($res['status']) && $res['status'] !== 200)
            return JsonService::fail(isset($res['data']['message']) ? $res['data']['message'] : $res['msg']);
        return JsonService::success(isset($res['data']['message']) ? $res['data']['message'] : $res['msg']);
    }

    /**
     * 修改/注册短信平台账号
     */
    public function save()
    {
        list($account, $password, $phone, $code, $url, $sign) = UtilService::postMore([
            ['account', ''],
            ['password', ''],
            ['phone', ''],
            ['code', ''],
            ['url', ''],
            ['sign', ''],
        ], null, true);
        $signLen = mb_strlen(trim($sign));
        if (!strlen(trim($account))) return JsonService::fail('请填写账号');
        if (!strlen(trim($password))) return JsonService::fail('请填写密码');
        if (!$signLen) return JsonService::fail('请填写短信签名');
        if ($signLen > 8) return JsonService::fail('短信签名最长为8位');
        if (!strlen(trim($code))) return JsonService::fail('请填写验证码');
        if (!strlen(trim($url))) return JsonService::fail('请填写域名');
        $status = SMSService::register($account, md5(trim($password)), $url, $phone, $code, $sign);
        if ($status['status'] == 400) return JsonService::fail('短信平台：' . $status['msg']);
        SystemConfig::setConfigSmsInfo($account, $password);
        return JsonService::success('短信平台：' . $status['msg']);
    }
}