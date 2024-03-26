<?php

namespace app\listener\notice;

use app\jobs\TemplateJob;
use app\services\message\MessageSystemServices;
use app\services\message\SystemNotificationServices;
use app\services\serve\ServeServices;
use app\services\user\UserServices;
use app\services\wechat\WechatUserServices;
use crmeb\interfaces\ListenerInterface;
use crmeb\services\HttpService;
use think\facade\Log;

class CustomNoticeListener implements ListenerInterface
{

    public function handle($event): void
    {
        [$uid, $infoData, $customTrigger] = $event;
        $notificationServices = app()->make(SystemNotificationServices::class);
        $noticeList = $notificationServices->getNotList(['custom_trigger' => $customTrigger]);
        foreach ($noticeList as $item) {
            if ($item['is_system'] == 1) $this->sendSystem($uid, $item, $infoData);
            if ($item['is_sms'] == 1) $this->sendSms($uid, $item, $infoData);
            if ($item['is_wechat'] == 1) $this->sendWechat($uid, $item, $infoData);
            if ($item['is_routine'] == 1) $this->sendRoutine($uid, $item, $infoData);
            if ($item['is_ent_wechat'] == 1) $this->sendEntWechat($uid, $item, $infoData);
        }
    }

    public function sendSystem($uid, $noticeData, $infoData)
    {
        try {
            $str = $noticeData['system_text'];
            preg_match_all('/\{(\w+)\}/', $str, $matches);
            $sendData = $matches[1];
            foreach ($sendData as $sendItem) {
                $str = str_replace("{" . $sendItem . "}", $infoData[$sendItem], $str);
            }
            $data = [];
            $data['mark'] = 'custom_notice';
            $data['uid'] = $uid;
            $data['content'] = $str;
            $data['title'] = $noticeData['system_title'];
            $data['type'] = 1;
            $data['add_time'] = time();
            $data['data'] = json_encode($sendData);
            /** @var MessageSystemServices $MessageSystemServices */
            $MessageSystemServices = app()->make(MessageSystemServices::class);
            $MessageSystemServices->save($data);
        } catch (\Exception $e) {
            Log::error('发送站内信失败,失败原因:' . $e->getMessage());
            return true;
        }
    }

    public function sendSms($uid, $noticeData, $infoData)
    {
        try {
            $smsType = ['yihaotong', 'aliyun', 'tencent'];
            $type = $smsType[sys_config('sms_type', 0)];
            $str = $noticeData['sms_text'];
            preg_match_all('/\{(\w+)\}/', $str, $matches);
            $smsData = $matches[1];
            $sendData = [];
            foreach ($smsData as $smsItem) {
                $sendData[$smsItem] = $infoData[$smsItem];
            }
            if ($type == 'tencent') {
                $sendData = array_values($sendData);
            }
            app()->make(ServeServices::class)->sms($type)->send($infoData['phone'], $noticeData['sms_id'], $sendData);
            return true;
        } catch (\Exception $e) {
            Log::error('发送短信失败,失败原因:' . $e->getMessage());
            return true;
        }
    }

    public function sendWechat($uid, $noticeData, $infoData)
    {
        try {
            $openid = '';
            $isDel = app()->make(UserServices::class)->value(['uid' => $uid], 'is_del');
            if (!$isDel) $openid = app()->make(WechatUserServices::class)->uidToOpenid($uid, 'wechat');
            if ($openid) {
                $wechatData = json_decode($noticeData['wechat_data'], true);
                $sendData = [];
                foreach ($wechatData as $wechatItem) {
                    $sendData[$wechatItem['key']] = $infoData[$wechatItem['value']];
                }
                $link = $noticeData['wechat_link'];
                preg_match_all('/\{(\w+)\}/', $link, $matches);
                $linkData = $matches[1];
                foreach ($linkData as $linkItem) {
                    $link = str_replace("{" . $linkItem . "}", $infoData[$linkItem], $link);
                }
                TemplateJob::dispatch('doJob', ['wechat', $openid, $noticeData['wechat_tempid'], $sendData, $link, null, $noticeData['wechat_to_routine']]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('发送微信模版消息失败,失败原因:' . $e->getMessage());
            return true;
        }
    }

    public function sendRoutine($uid, $noticeData, $infoData)
    {
        try {
            $openid = '';
            $isDel = app()->make(UserServices::class)->value(['uid' => $uid], 'is_del');
            if (!$isDel) $openid = app()->make(WechatUserServices::class)->uidToOpenid($uid, 'routine');
            if ($openid) {
                $routineData = json_decode($noticeData['routine_data'], true);
                $sendData = [];
                foreach ($routineData as $routineItem) {
                    $sendData[$routineItem['key']] = $infoData[$routineItem['value']];
                }
                $link = $noticeData['routine_link'];
                preg_match_all('/\{(\w+)\}/', $link, $matches);
                $linkData = $matches[1];
                foreach ($linkData as $linkItem) {
                    $link = str_replace("{" . $linkItem . "}", $infoData[$linkItem], $link);
                }
                TemplateJob::dispatch('doJob', ['subscribe', $openid, $noticeData['routine_tempid'], $sendData, $link, null]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('发送小程序订阅消息失败,失败原因:' . $e->getMessage());
            return true;
        }
    }

    public function sendEntWechat($uid, $noticeData, $infoData)
    {
        try {
            $str = $noticeData['ent_wechat_text'];
            preg_match_all('/\{(\w+)\}/', $str, $matches);
            $sendData = $matches[1];
            foreach ($sendData as $sendItem) {
                $str = str_replace("{" . $sendItem . "}", $infoData[$sendItem], $str);
            }

            $s = explode('\n', $str);
            $d = '';
            foreach ($s as $item) {
                $d .= $item . "\n>";
            }
            $d = substr($d, 0, strlen($d) - 2);
            HttpService::postRequest($noticeData['url'], json_encode([
                'msgtype' => 'markdown',
                'markdown' => ['content' => $d]
            ]));
        } catch (\Throwable $e) {
            Log::error('发送企业群消息失败,失败原因:' . $e->getMessage());
        }
    }
}