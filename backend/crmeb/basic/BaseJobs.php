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

namespace crmeb\basic;

use crmeb\interfaces\JobInterface;
use think\facade\Log;
use think\queue\Job;
use crmeb\utils\Queue;
use think\facade\Env;

/**
 * 消息队列基类
 * Class BaseJobs
 * @package crmeb\basic
 */
abstract class BaseJobs implements JobInterface
{
    /**
     * 当前正在执行的 Job 类名列表（防止同步模式下的递归调用）
     * 使用类名作为键，值为 true，提升查找性能
     * @var array
     */
    protected static $executingJobs = [];

    /**
     * 队列名称
     * @return null|string
     */
    protected static function queueName()
    {
        return null;
    }

    /**
     * 构建队列实例
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @param int|null $secs
     * @return mixed
     */
    protected static function buildQueue($action, array $data = [], ?string $queueName = null, ?int $secs = null)
    {
        $queue = Queue::instance()->job(static::class);
        if ($secs !== null) {
            $queue->secs($secs);
        }
        if (is_array($action)) {
            $queue->data(...$action);
        } else if (is_string($action)) {
            $queue->do($action)->data(...$data);
        }
        $setQueueName = !empty($queueName) ? $queueName : static::queueName();
        if (!empty($setQueueName)) {
            $queue->setQueueName($setQueueName);
        }
        return $queue->push();
    }

    /**
     * 同步模式执行任务（带死循环防护）
     * @param $action
     * @param array $data
     * @return mixed
     */
    protected static function executeSync($action, array $data = [])
    {
        $className = static::class;
        if (isset(static::$executingJobs[$className])) {
            Log::warning("Job {$className} is already executing, skipping to prevent infinite loop");
            return true;
        }
        try {
            static::$executingJobs[$className] = true;
            $instance = new static();
            if (is_array($action)) {
                return $instance->doJob(...$action);
            } else {
                return $instance->$action(...$data);
            }
        } finally {
            unset(static::$executingJobs[$className]);
        }
    }

    /**
     * 可靠支付任务由持久化记录直接执行，失败不能因普通队列重试耗尽而丢失。
     * @param string|array $action 任务方法或参数
     * @param array $data 任务参数
     * @return mixed
     */
    public static function runPaymentTask($action, array $data = [])
    {
        $method = is_array($action) ? 'doJob' : $action;
        if (!is_string($method) || !method_exists(static::class, $method)) throw new \RuntimeException('支付任务方法不存在');
        return static::executeSync($action, $data);
    }

    /**
     * 加入队列
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatch($action, array $data = [], ?string $queueName = null)
    {
        if (\crmeb\services\pay\PaymentDispatch::defer(['type' => 'job', 'class' => static::class,
            'action' => $action, 'data' => $data, 'queue' => $queueName, 'secs' => null])) return true;
        if (\crmeb\services\pay\PaymentDispatch::replaying()) return static::runPaymentTask($action, $data);
        //判断是否开启队列
        if (sys_config('queue_open', 0) == 1 && Env::get('cache.driver', 'file') == 'redis') {
            //加入队列
            return static::buildQueue($action, $data, $queueName);
        } else {
            //同步模式执行任务
            return static::executeSync($action, $data);
        }
    }

    /**
     * 延迟加入消息队列
     * @param int $secs
     * @param $action
     * @param array $data
     * @param string|null $queueName
     * @return mixed
     */
    public static function dispatchSecs(int $secs, $action, array $data = [], ?string $queueName = null)
    {
        if (\crmeb\services\pay\PaymentDispatch::defer(['type' => 'job', 'class' => static::class,
            'action' => $action, 'data' => $data, 'queue' => $queueName, 'secs' => $secs])) return true;
        if (\crmeb\services\pay\PaymentDispatch::replaying()) return static::runPaymentTask($action, $data);
        //判断是否开启队列
        if (sys_config('queue_open', 0) == 1 && Env::get('cache.driver', 'file') == 'redis') {
            //加入队列
            return static::buildQueue($action, $data, $queueName, $secs);
        } else {
            //同步模式执行任务
            return static::executeSync($action, $data);
        }
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $this->fire(...$arguments);
    }

    /**
     * 运行消息队列
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data): void
    {
        try {
            $action = $data['do'] ?? 'doJob';//任务名
            $infoData = $data['data'] ?? [];//执行数据
            $errorCount = $data['errorCount'] ?? 0;//最大错误次数
            $this->runJob($action, $job, $infoData, $errorCount);
        } catch (\Throwable $e) {
            //异常时按已尝试次数决定重试还是放弃，避免一出错立即丢弃任务
            $maxAttempts = $errorCount > 0 ? $errorCount : 3;
            $currentAttempts = $job->attempts();
            if ($currentAttempts < $maxAttempts) {
                //未达上限，延迟 60 秒后重新放入队列重试
                Log::warning('队列异常将重试（第 ' . $currentAttempts . '/' . $maxAttempts . ' 次）：' . $e->getMessage());
                $job->release(60);
            } else {
                //已达上限，删除任务并记录错误日志（死信兜底）
                Log::error('队列错误已达上限，任务被丢弃（共尝试 ' . $currentAttempts . ' 次）：' . $e->getMessage());
                $job->delete();
            }
        }
    }

    /**
     * 执行队列
     * @param string $action
     * @param Job $job
     * @param array $infoData
     * @param int $errorCount
     */
    protected function runJob(string $action, Job $job, array $infoData, int $errorCount = 3)
    {
        $action = method_exists($this, $action) ? $action : 'handle';
        if (!method_exists($this, $action)) {
            $job->delete();
            return;
        }

        if ($this->{$action}(...$infoData)) {
            //删除任务
            $job->delete();
        } else {
            if ($job->attempts() >= $errorCount && $errorCount) {
                //删除任务
                $job->delete();
            } else {
                //从新放入队列
                $job->release();
            }
        }
    }
}
