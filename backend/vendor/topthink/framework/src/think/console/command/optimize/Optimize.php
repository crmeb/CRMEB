<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\console\command\optimize;

use think\console\Command;
use think\console\command\CommandCallable;
use think\console\Input;
use think\console\Output;

class Optimize extends Command
{
    use CommandCallable;

    protected function configure()
    {
        $this->setName('optimize')
            ->setDescription('Build cache.');
    }

    protected function execute(Input $input, Output $output)
    {
        $commands = [
            Config::class,
            Route::class,
            Schema::class,
        ];
        foreach ($commands as $class) {
            $command = $this->callCommand($class);
            $this->output->info($command->getName() . ' run succeed!');
        }
    }
}
