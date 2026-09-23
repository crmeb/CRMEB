<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\command;

use app\services\message\pipeline\SendNoticeService;
use crmeb\services\CacheService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 消息通知缓存清理命令
 * 清理消息配置缓存与渠道列表缓存
 */
class NoticeCache extends Command
{
    protected function configure()
    {
        $this->setName('notice:clear')
            ->setDescription('清理消息通知相关缓存');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('正在清理消息通知缓存...');

        try {
            app()->make(SendNoticeService::class)->clearCache();

            $output->writeln('✅ 消息配置缓存已清理');
            $output->writeln('✅ 渠道列表缓存已清理');
            $output->writeln('');
            $output->writeln('清理完成！缓存键名：');
            $output->writeln('  - ' . SendNoticeService::CACHE_KEY);
            $output->writeln('  - ' . SendNoticeService::CHANNEL_CACHE_KEY);
        } catch (\Exception $e) {
            $output->error('缓存清理失败: ' . $e->getMessage());
        }
    }
}
