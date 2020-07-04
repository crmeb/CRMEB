<?php

namespace crmeb\repositories;


use app\admin\model\sms\SmsRecord;
use crmeb\services\sms\Sms;
use think\facade\Log;

/**
 * 短信发送
 * Class ShortLetterRepositories
 * @package crmeb\repositories
 */
class ShortLetterRepositories
{
    /**
     * 发送短信
     * @param $switch 发送开关
     * @param $phone 手机号码
     * @param array $data 模板替换内容
     * @param string $template 模板编号
     * @param string $logMsg 错误日志记录
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function send($switch, $phone, array $data, string $template, $logMsg = '')
    {
        if ($switch && $phone) {
            $sms = new Sms([
                'sms_account' => sys_config('sms_account'),
                'sms_token' => sys_config('sms_token'),
                'site_url' => sys_config('site_url')
            ]);
            $res = $sms->send($phone, $template, $data);
            if ($res === false) {
                $errorSmg = $sms->getError();
                Log::info($logMsg ?? $errorSmg);
                return $errorSmg;
            } else {
                SmsRecord::sendRecord($phone, $res['data']['content'], $res['data']['template'], $res['data']['id']);
            }
            return true;
        } else {
            return false;
        }
    }
}