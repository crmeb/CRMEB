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

namespace app\services\message\sms;


use app\dao\system\config\SystemConfigDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\HttpService;
use crmeb\services\sms\Sms;

/**
 * 短信平台注册登陆
 * Class SmsAdminServices
 * @package app\services\message\sms
 */
class SmsAdminServices extends BaseServices
{
    /**
     * 构造方法
     * SmsAdminServices constructor.
     * @param SystemConfigDao $dao
     */
    public function __construct(SystemConfigDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 更新短信配置
     * @param string $account
     * @param string $password
     * @return mixed
     */
    public function updateSmsConfig(string $account, string $password)
    {
        return $this->transaction(function () use ($account, $password) {
            $this->dao->update('sms_account', ['value' => json_encode($account)], 'menu_name');
            $this->dao->update('sms_token', ['value' => json_encode($password)], 'menu_name');
        });
    }

    /**
     * 注册短信平台
     * @param string $account
     * @param string $password
     * @param string $url
     * @param string $phone
     * @param int $code
     * @param string $sign
     * @return bool
     */
    public function register(string $account, string $password, string $url, string $phone, string $code, string $sign)
    {
        /** @var Sms $sms */
        $sms = app()->make(Sms::class, ['yunxin']);
        $status = $sms->register($account, md5(trim($password)), $url, $phone, $code, $sign);
        if ($status['status'] == 400) {
            throw new AdminException('短信平台：' . $status['msg']);
        }
        $this->updateSmsConfig($account, $password);
        CacheService::clear();
        return $status;
    }

    /**
     * 发送验证码
     * @param string $phone
     * @return mixed
     */
    public function captcha(string $phone)
    {
        /** @var Sms $sms */
        $sms = app()->make(Sms::class, ['yunxin']);
        $res = json_decode(HttpService::getRequest($sms->getSmsUrl(), compact('phone')), true);
        if (!isset($res['status']) && $res['status'] !== 200) {
            throw new AdminException(isset($res['data']['message']) ? $res['data']['message'] : $res['msg']);
        }
        return isset($res['data']['message']) ? $res['data']['message'] : $res['msg'];
    }

    /**
     * 短信登陆
     * @param string $account
     * @param string $token
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function login(string $account, string $token)
    {
        /** @var Sms $sms */
        $sms = app()->make(Sms::class, [
            'yunxin', [
                'sms_account' => $account,
                'sms_token' => $token,
                'site_url' => sys_config('site_url')
            ]
        ]);

        $this->updateSmsConfig($account, $token);

        //添加公共短信模板
        $templateList = $sms->publictemp([]);
        if ($templateList['status'] != 400) {
            if ($templateList['data']['data']) {
                foreach ($templateList['data']['data'] as $v) {
                    if ($v['is_have'] == 0)
                        $sms->use($v['id'], $v['templateid']);
                }
            }
            CacheService::clear();
            CacheService::redisHandler()->set('sms_account', $account);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取当前登陆的短信账号信息
     * @return mixed
     */
    public function getSmsData()
    {
        $account = sys_config('sms_account');
        $sms = app()->make(Sms::class, ['yunxin', [
            'sms_account' => $account,
            'sms_token' => sys_config('sms_token'),
            'site_url' => sys_config('site_url')
        ]]);
        $countInfo = $sms->count();
        if ($countInfo['status'] == 400) {
            $info['number'] = 0;
            $info['total_number'] = 0;
        } else {
            $info['number'] = $countInfo['data']['number'];
            $info['total_number'] = $countInfo['data']['send_total'];
        }
        /** @var SmsRecordServices $service */
        $service = app()->make(SmsRecordServices::class);
        $info['record_number'] = $service->count(['uid' => $account]);
        $info['sms_account'] = $account;
        return $info;
    }

}
