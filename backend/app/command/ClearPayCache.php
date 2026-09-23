<?php
namespace app\command;

use app\services\pay\PayServices;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 支付配置缓存清理命令
 * 清理支付渠道信息缓存
 */
class ClearPayCache extends Command
{
    protected function configure()
    {
        $this->setName('pay:clear-cache')
            ->setDescription('清除支付配置缓存');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('正在清理支付配置缓存...');

        try {
            /** @var PayServices $payServices */
            $payServices = app()->make(PayServices::class);
            $result = $payServices->clearChannelInfoCache();

            if ($result) {
                $output->writeln('✅ 支付配置缓存已清理');
                $output->writeln('');
                $output->writeln('清理完成！缓存键名：');
                $output->writeln('  - pay_channel_info');
            } else {
                $output->writeln('⚠️ 缓存清理失败或缓存不存在');
            }
        } catch (\Exception $e) {
            $output->error('缓存清理失败: ' . $e->getMessage());
        }
    }
}
