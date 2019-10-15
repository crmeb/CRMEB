<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\queue;

use Closure;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use think\App;

class Listener
{

    /**
     * @var string
     */
    protected $commandPath;

    /**
     * @var string
     */
    protected $workerCommand;

    /**
     * @var \Closure|null
     */
    protected $outputHandler;

    /**
     * @param string $commandPath
     */
    public function __construct($commandPath)
    {
        $this->commandPath = $commandPath;
    }

    public static function __make(App $app)
    {
        return new self($app->getRootPath());
    }

    /**
     * Get the PHP binary.
     *
     * @return string
     */
    protected function phpBinary()
    {
        return (new PhpExecutableFinder)->find(false);
    }

    /**
     * @param string $connection
     * @param string $queue
     * @param int    $delay
     * @param int    $sleep
     * @param int    $maxTries
     * @param int    $memory
     * @param int    $timeout
     * @return void
     */
    public function listen($connection, $queue, $delay = 0, $sleep = 3, $maxTries = 0, $memory = 128, $timeout = 60)
    {
        $process = $this->makeProcess($connection, $queue, $delay, $sleep, $maxTries, $memory, $timeout);

        while (true) {
            $this->runProcess($process, $memory);
        }
    }

    /**
     * @param string $connection
     * @param string $queue
     * @param int    $delay
     * @param int    $sleep
     * @param int    $maxTries
     * @param int    $memory
     * @param int    $timeout
     * @return Process
     */
    public function makeProcess($connection, $queue, $delay, $sleep, $maxTries, $memory, $timeout)
    {
        $command = array_filter([
            $this->phpBinary(),
            'think',
            'queue:work',
            $connection,
            '--once',
            "--queue={$queue}",
            "--delay={$delay}",
            "--memory={$memory}",
            "--sleep={$sleep}",
            "--tries={$maxTries}",
        ], function ($value) {
            return !is_null($value);
        });

        return new Process($command, $this->commandPath, null, null, $timeout);
    }

    /**
     * @param Process $process
     * @param int     $memory
     */
    public function runProcess(Process $process, $memory)
    {
        $process->run(function ($type, $line) {
            $this->handleWorkerOutput($type, $line);
        });

        if ($this->memoryExceeded($memory)) {
            $this->stop();
        }
    }

    /**
     * @param int    $type
     * @param string $line
     * @return void
     */
    protected function handleWorkerOutput($type, $line)
    {
        if (isset($this->outputHandler)) {
            call_user_func($this->outputHandler, $type, $line);
        }
    }

    /**
     * @param int $memoryLimit
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * @return void
     */
    public function stop()
    {
        die;
    }

    /**
     * @param \Closure $outputHandler
     * @return void
     */
    public function setOutputHandler(Closure $outputHandler)
    {
        $this->outputHandler = $outputHandler;
    }

}
