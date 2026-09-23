<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class PluginInstall extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\plugininstall')
            ->setDescription('the app\command\plugininstall command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('app\command\plugininstall');
    }
}
