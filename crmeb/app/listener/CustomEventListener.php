<?php

namespace app\listener;

use app\services\system\SystemEventServices;
use think\facade\Log;

class CustomEventListener
{
    public function handle($event)
    {
        [$mark, $data] = $event;
        try {
            $list = app()->make(SystemEventServices::class)->selectList(['mark' => $mark, 'is_del' => 0, 'is_open' => 1])->toArray();
            foreach ($list as $item) {
                eval(json_decode($item['customCode']));
            }
        } catch (\Throwable $e) {
            $listener_log_open = config("log.listener_log", false);
            if ($listener_log_open) {
                $date = date('Y-m-d H:i:s', time());
                Log::write($date . '自定义事件错误:' . $e->getMessage(), 'listener');
            }
        }
    }
}