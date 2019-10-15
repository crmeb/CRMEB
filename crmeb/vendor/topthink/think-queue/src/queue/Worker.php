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

use Carbon\Carbon;
use Exception;
use RuntimeException;
use think\Cache;
use think\Event;
use think\exception\Handle;
use think\Queue;
use think\queue\event\JobExceptionOccurred;
use think\queue\event\JobFailed;
use think\queue\event\JobProcessed;
use think\queue\event\JobProcessing;
use think\queue\event\WorkerStopping;
use think\queue\exception\MaxAttemptsExceededException;
use Throwable;

class Worker
{
    /** @var Event */
    protected $event;
    /** @var Handle */
    protected $handle;
    /** @var Queue */
    protected $queue;

    /** @var Cache */
    protected $cache;

    /**
     * Indicates if the worker should exit.
     *
     * @var bool
     */
    public $shouldQuit = false;

    /**
     * Indicates if the worker is paused.
     *
     * @var bool
     */
    public $paused = false;

    public function __construct(Queue $queue, Event $event, Handle $handle, Cache $cache = null)
    {
        $this->queue  = $queue;
        $this->event  = $event;
        $this->handle = $handle;
        $this->cache  = $cache;
    }

    /**
     * @param string $connection
     * @param string $queue
     * @param int    $delay
     * @param int    $sleep
     * @param int    $maxTries
     * @param int    $memory
     * @param int    $timeout
     */
    public function daemon($connection, $queue, $delay = 0, $sleep = 3, $maxTries = 0, $memory = 128, $timeout = 60)
    {
        if ($this->supportsAsyncSignals()) {
            $this->listenForSignals();
        }

        $lastRestart = $this->getTimestampOfLastQueueRestart();

        while (true) {

            $job = $this->getNextJob(
                $this->queue->connection($connection), $queue
            );

            if ($this->supportsAsyncSignals()) {
                $this->registerTimeoutHandler($job, $timeout);
            }

            if ($job) {
                $this->runJob($job, $connection, $maxTries, $delay);
            } else {
                $this->sleep($sleep);
            }

            $this->stopIfNecessary($job, $lastRestart, $memory);
        }
    }

    protected function stopIfNecessary($job, $lastRestart, $memory)
    {
        if ($this->shouldQuit || $this->queueShouldRestart($lastRestart)) {
            $this->stop();
        } elseif ($this->memoryExceeded($memory)) {
            $this->stop(12);
        }
    }

    /**
     * Determine if the queue worker should restart.
     *
     * @param int|null $lastRestart
     * @return bool
     */
    protected function queueShouldRestart($lastRestart)
    {
        return $this->getTimestampOfLastQueueRestart() != $lastRestart;
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param int $memoryLimit
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * 获取队列重启时间
     * @return mixed
     */
    protected function getTimestampOfLastQueueRestart()
    {
        if ($this->cache) {
            return $this->cache->get('think:queue:restart');
        }
    }

    /**
     * Register the worker timeout handler.
     *
     * @param Job|null $job
     * @param int      $timeout
     * @return void
     */
    protected function registerTimeoutHandler($job, $timeout)
    {
        pcntl_signal(SIGALRM, function () {
            $this->kill(1);
        });

        pcntl_alarm(
            max($this->timeoutForJob($job, $timeout), 0)
        );
    }

    /**
     * Stop listening and bail out of the script.
     *
     * @param int $status
     * @return void
     */
    public function stop($status = 0)
    {
        $this->event->trigger(new WorkerStopping($status));

        exit($status);
    }

    /**
     * Kill the process.
     *
     * @param int $status
     * @return void
     */
    public function kill($status = 0)
    {
        $this->event->trigger(new WorkerStopping($status));

        if (extension_loaded('posix')) {
            posix_kill(getmypid(), SIGKILL);
        }

        exit($status);
    }

    /**
     * Get the appropriate timeout for the given job.
     *
     * @param Job|null $job
     * @param int      $timeout
     * @return int
     */
    protected function timeoutForJob($job, $timeout)
    {
        return $job && !is_null($job->timeout()) ? $job->timeout() : $timeout;
    }

    /**
     * Determine if "async" signals are supported.
     *
     * @return bool
     */
    protected function supportsAsyncSignals()
    {
        return extension_loaded('pcntl');
    }

    /**
     * Enable async signals for the process.
     *
     * @return void
     */
    protected function listenForSignals()
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () {
            $this->shouldQuit = true;
        });

        pcntl_signal(SIGUSR2, function () {
            $this->paused = true;
        });

        pcntl_signal(SIGCONT, function () {
            $this->paused = false;
        });
    }

    /**
     * 执行下个任务
     * @param string $connection
     * @param string $queue
     * @param int    $delay
     * @param int    $sleep
     * @param int    $maxTries
     * @return void
     * @throws Exception
     */
    public function runNextJob($connection, $queue, $delay = 0, $sleep = 3, $maxTries = 0)
    {

        $job = $this->getNextJob($this->queue->connection($connection), $queue);

        if ($job) {
            $this->runJob($job, $connection, $maxTries, $delay);
        } else {
            $this->sleep($sleep);
        }
    }

    /**
     * 执行任务
     * @param Job    $job
     * @param string $connection
     * @param int    $maxTries
     * @param int    $delay
     * @return void
     */
    protected function runJob($job, $connection, $maxTries, $delay)
    {
        try {
            $this->process($connection, $job, $maxTries, $delay);
        } catch (Exception | Throwable $e) {
            $this->handle->report($e);
        }
    }

    /**
     * 获取下个任务
     * @param Connector $connector
     * @param string    $queue
     * @return Job
     */
    protected function getNextJob($connector, $queue)
    {
        try {
            foreach (explode(',', $queue) as $queue) {
                if (!is_null($job = $connector->pop($queue))) {
                    return $job;
                }
            }
        } catch (Exception | Throwable $e) {
            $this->handle->report($e);
            $this->sleep(1);
        }
    }

    /**
     * Process a given job from the queue.
     * @param string $connection
     * @param Job    $job
     * @param int    $maxTries
     * @param int    $delay
     * @return void
     * @throws Exception
     */
    public function process($connection, $job, $maxTries = 0, $delay = 0)
    {
        try {
            $this->event->trigger(new JobProcessing($connection, $job));

            $this->markJobAsFailedIfAlreadyExceedsMaxAttempts(
                $connection, $job, (int) $maxTries
            );

            $job->fire();

            $this->event->trigger(new JobProcessed($connection, $job));
        } catch (Exception | Throwable $e) {
            try {
                if (!$job->hasFailed()) {
                    $this->markJobAsFailedIfWillExceedMaxAttempts($connection, $job, (int) $maxTries, $e);
                }

                $this->event->trigger(new JobExceptionOccurred($connection, $job, $e));
            } finally {
                if (!$job->isDeleted() && !$job->isReleased() && !$job->hasFailed()) {
                    $job->release($delay);
                }
            }

            throw $e;
        }
    }

    /**
     * @param string $connection
     * @param Job    $job
     * @param int    $maxTries
     */
    protected function markJobAsFailedIfAlreadyExceedsMaxAttempts($connection, $job, $maxTries)
    {
        $maxTries = !is_null($job->maxTries()) ? $job->maxTries() : $maxTries;

        $timeoutAt = $job->timeoutAt();

        if ($timeoutAt && Carbon::now()->getTimestamp() <= $timeoutAt) {
            return;
        }

        if (!$timeoutAt && (0 === $maxTries || $job->attempts() <= $maxTries)) {
            return;
        }

        $this->failJob($connection, $job, $e = new MaxAttemptsExceededException(
            $job->getName() . ' has been attempted too many times or run too long. The job may have previously timed out.'
        ));

        throw $e;
    }

    /**
     * @param string    $connection
     * @param Job       $job
     * @param int       $maxTries
     * @param Exception $e
     */
    protected function markJobAsFailedIfWillExceedMaxAttempts($connection, $job, $maxTries, $e)
    {
        $maxTries = !is_null($job->maxTries()) ? $job->maxTries() : $maxTries;

        if ($job->timeoutAt() && $job->timeoutAt() <= Carbon::now()->getTimestamp()) {
            $this->failJob($connection, $job, $e);
        }

        if ($maxTries > 0 && $job->attempts() >= $maxTries) {
            $this->failJob($connection, $job, $e);
        }
    }

    /**
     * @param string    $connection
     * @param Job       $job
     * @param Exception $e
     */
    protected function failJob($connection, $job, $e)
    {
        $job->markAsFailed();

        if ($job->isDeleted()) {
            return;
        }

        try {
            $job->delete();

            $job->failed($e);
        } finally {
            $this->event->trigger(new JobFailed(
                $connection, $job, $e ?: new RuntimeException('ManuallyFailed')
            ));
        }
    }

    /**
     * Sleep the script for a given number of seconds.
     * @param int $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }

}
