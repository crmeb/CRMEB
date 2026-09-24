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

use app\services\system\crontab\SystemCrontabServices;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

/**
 * 定时任务命令
 * 基于 Workerman 启动系统定时任务调度
 */
class Timer extends Command
{
    /**
     * 定时器实例
     * @var int
     */
    protected $timer;

    /**
     * 执行间隔（秒）
     * @var int|float
     */
    protected $interval = 1;

    protected function configure()
    {
        // 指令配置
        $this->setName('timer')
            ->addArgument('status', Argument::REQUIRED, 'start/stop/reload/status/connections')
            ->addOption('d', null, Option::VALUE_NONE, 'daemon（守护进程）方式启动')
            ->addOption('i', null, Option::VALUE_OPTIONAL, '多长时间执行一次,可以精确到0.001')
            ->setDescription('start/stop/restart 定时任务');
    }

    protected function init(Input $input, Output $output)
    {
        global $argv;

        if ($input->hasOption('i'))
            $this->interval = floatval($input->getOption('i'));

        $argv[1] = $input->getArgument('status') ?: 'start';
        if ($input->hasOption('d')) {
            $argv[2] = '-d';
        } else {
            unset($argv[2]);
        }
    }

    protected function execute(Input $input, Output $output)
    {
        $this->init($input, $output);
        Worker::$pidFile = app()->getRootPath() . 'runtime/timer.pid';
        Worker::$logFile = app()->getRootPath() . 'runtime/timer.log';
        Worker::$statusFile = app()->getRootPath() . 'runtime/timer.status';
        $task = new Worker();
        date_default_timezone_set('PRC');
        $task->count = 1;
        $task->onWorkerStart = function () {
            app()->make(SystemCrontabServices::class)->crontabCommandRun();
        };
        $task->runAll();
    }
}
