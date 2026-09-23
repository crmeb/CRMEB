<?php
declare(strict_types=1);

namespace modules\channel\wechat_mini\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\channel\wechat_mini\service\LiveGoodsServices;
use think\facade\Log;

class LiveProductStatusTask implements CrontabTaskInterface
{
    public function getMark(): string
    {
        return 'liveProductStatus';
    }

    public function getName(): string
    {
        return '自动更新直播商品状态';
    }

    public function getDescription(): string
    {
        return '同步直播商品在微信小程序后台的上架/下架状态';
    }

    public function handle(): void
    {
        try {
            app()->make(LiveGoodsServices::class)->syncGoodStatus();
            $this->log('自动更新直播商品状态执行成功');
        } catch (\Throwable $e) {
            $this->log('自动更新直播商品状态失败,失败原因:' . $e->getMessage(), 'error');
            throw $e;
        }
    }

    protected function log(string $msg, string $level = 'info'): void
    {
        if (config("log.timer_log", false)) {
            Log::$level(date('Y-m-d H:i:s') . $msg);
        }
    }
}
