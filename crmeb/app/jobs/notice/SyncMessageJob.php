<?php

namespace app\jobs\notice;

use app\services\message\TemplateMessageServices;
use crmeb\basic\BaseJobs;
use crmeb\exceptions\AdminException;
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
    public function syncSubscribe($template)
    {
        $errCode = [-1, 40001, 40002, 40013, 40125, 41002, 41004, 43104, 45009, 200011, 200012, 200014];
        /** @var TemplateMessageServices $templateMessageServices */
        $templateMessageServices = app()->make(TemplateMessageServices::class);
        if ($template['tempkey']) {
            if ($template['tempid']) {
                try {
                    MiniProgramService::delSubscribeTemplate($template['tempid']);
                } catch (\Throwable $e) {
                    $wechatErr = $e->getMessage();
                    if (is_string($wechatErr)) {
                        Log::error('删除旧订阅消息模版失败：' . $wechatErr);
                        return true;
                    }
                    if (in_array($wechatErr->getCode(), $errCode)) {
                        Log::error('删除旧订阅消息模版失败：' . $wechatErr->getCode());
                        return true;
                    }
                    Log::error('删除旧订阅消息模版失败：' . $wechatErr->getMessage());
                    return true;
                }
            }
            try {
                $works = MiniProgramService::getSubscribeTemplateKeyWords($template['tempkey']);
            } catch (\Throwable $e) {
                $wechatErr = $e->getMessage();
                if (is_string($wechatErr)) {
                    Log::error('获取关键词列表失败：' . $wechatErr);
                    return true;
                }
                if (in_array($wechatErr->getCode(), $errCode)) {
                    Log::error('获取关键词列表失败：' . $wechatErr->getCode());
                    return true;
                }
                Log::error('获取关键词列表失败：' . $wechatErr->getMessage());
                return true;
            }
            $kid = [];
            if ($works) {
                $works = array_combine(array_column($works, 'name'), $works);
                $content = is_array($template['content']) ? $template['content'] : explode("\n", $template['content']);
                foreach ($content as $c) {
                    $name = explode('{{', $c)[0] ?? '';
                    if ($name && isset($works[$name])) {
                        $kid[] = $works[$name]['kid'];
                    }
                }
            }
            if ($kid) {
                try {
                    $tempid = MiniProgramService::addSubscribeTemplate($template['tempkey'], $kid, $template['name']);
                } catch (\Throwable $e) {
                    $wechatErr = $e->getMessage();
                    if (is_string($wechatErr)) {
                        Log::error('添加订阅消息模版失败：' . $wechatErr);
                        return true;
                    }
                    if (in_array($wechatErr->getCode(), $errCode)) {
                        Log::error('添加订阅消息模版失败：' . $wechatErr->getCode());
                        return true;
                    }
                    Log::error('添加订阅消息模版失败：' . $wechatErr->getMessage());
                    return true;
                }
                $templateMessageServices->update($template['id'], ['tempid' => $tempid, 'kid' => json_encode($kid), 'add_time' => time()], 'id');
                return true;
            }
        }
        return true;
    }

    /**
     * 同步公众号模版消息
     * @param $template
     * @return bool
     */
    public function syncWechat($template)
    {
        /** @var TemplateMessageServices $templateMessageServices */
        $templateMessageServices = app()->make(TemplateMessageServices::class);
        try {
            $res = WechatService::addTemplateId($template['tempkey']);
        } catch (\Throwable $e) {
            Log::error('同步模版消息失败：' . $e->getMessage());
            return true;
        }
        if(!$res->errcode && $res->template_id){
            $templateMessageServices->update($template['id'],['tempid'=>$res->template_id]);
        }
        return true;
    }
}