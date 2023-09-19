<?php

namespace app\jobs\notice;

use app\services\message\SystemNotificationServices;
use crmeb\basic\BaseJobs;
use crmeb\services\app\MiniProgramService;
use crmeb\services\app\WechatService;
use crmeb\traits\QueueTrait;
use think\facade\Log;

class SyncMessageJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 同步小程序订阅消息
     * @param $template
     * @return bool
     */
    public function syncSubscribe($key, $data)
    {
        $works = MiniProgramService::getSubscribeTemplateKeyWords($key);
        $kid = [];
        if ($works) {
            $works = array_combine(array_column($works, 'name'), $works);
            $content = is_array($data['routine_content']) ? $data['routine_content'] : explode("\n", $data['routine_content']);
            foreach ($content as $c) {
                $name = explode('{{', $c)[0] ?? '';
                if ($name && isset($works[$name])) {
                    $kid[] = $works[$name]['kid'];
                }
            }
        }
        if ($kid) {
            try {
                $tempid = MiniProgramService::addSubscribeTemplate($key, $kid, $data['name']);
            } catch (\Throwable $e) {
                Log::error('同步订阅消息失败：' . $e->getMessage());
                return true;
            }
            app()->make(SystemNotificationServices::class)->update(['routine_tempkey' => $key], ['routine_tempid' => $tempid, 'routine_kid' => json_encode($kid)]);
            return true;
        }
        return true;
    }

    /**
     * 同步公众号模版消息
     * @param $key
     * @param $content
     * @return bool
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/16
     */
    public function syncWechat($key, $content)
    {
        $content = is_array($content) ? $content : explode("\n", $content);
        $name = [];
        foreach ($content as $c) {
            $name[] = explode('{{', $c)[0] ?? '';
        }
        try {
            $res = WechatService::addTemplateId($key, $name);
        } catch (\Throwable $e) {
            Log::error('同步模版消息失败：' . $e->getMessage());
            return true;
        }
        if (!$res->errcode && $res->template_id) {
            app()->make(SystemNotificationServices::class)->update(['wechat_tempkey' => $key], ['wechat_tempid' => $res->template_id]);
        }
        return true;
    }
}