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

namespace think\queue\connector;

use Closure;
use Exception;
use think\helper\Str;
use think\queue\Connector;
use think\queue\InteractsWithTime;
use think\queue\job\Redis as RedisJob;

class Redis extends Connector
{
    use InteractsWithTime;

    /** @var  \Redis */
    protected $redis;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The expiration time of a job.
     *
     * @var int|null
     */
    protected $retryAfter = 60;

    /**
     * The maximum number of seconds to block for a job.
     *
     * @var int|null
     */
    protected $blockFor = null;

    public function __construct(\Redis $redis, $default = 'default', $retryAfter = 60, $blockFor = null)
    {
        $this->redis      = $redis;
        $this->default    = $default;
        $this->retryAfter = $retryAfter;
        $this->blockFor   = $blockFor;
    }

    public static function __make($config)
    {
        if (!extension_loaded('redis')) {
            throw new Exception('redis扩展未安装');
        }

        $func = $config['persistent'] ? 'pconnect' : 'connect';

        $redis = new \Redis;
        $redis->$func($config['host'], $config['port'], $config['timeout']);

        if ('' != $config['password']) {
            $redis->auth($config['password']);
        }

        if (0 != $config['select']) {
            $redis->select($config['select']);
        }

        return new self($redis, $config['queue'], $config['retry_after'] ?? 60, $config['block_for'] ?? null);
    }

    public function size($queue)
    {
        $queue = $this->getQueue($queue);

        return $this->redis->lLen($queue) + $this->redis->zCard("{$queue}:delayed") + $this->redis->zCard("{$queue}:reserved");
    }

    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->redis->rPush($this->getQueue($queue), $payload);

        return json_decode($payload, true)['id'] ?? null;
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->laterRaw($delay, $this->createPayload($job, $data), $queue);
    }

    protected function laterRaw($delay, $payload, $queue = null)
    {
        $this->redis->zadd(
            $this->getQueue($queue) . ':delayed', $this->availableAt($delay), $payload
        );

        return json_decode($payload, true)['id'] ?? null;
    }

    public function pop($queue = null)
    {
        $this->migrate($prefixed = $this->getQueue($queue));

        if (empty($nextJob = $this->retrieveNextJob($prefixed))) {
            return;
        }

        [$job, $reserved] = $nextJob;

        if ($reserved) {
            return new RedisJob($this->app, $this, $job, $reserved, $this->connection, $queue);
        }
    }

    /**
     * Migrate any delayed or expired jobs onto the primary queue.
     *
     * @param string $queue
     * @return void
     */
    protected function migrate($queue)
    {
        $this->migrateExpiredJobs($queue . ':delayed', $queue);

        if (!is_null($this->retryAfter)) {
            $this->migrateExpiredJobs($queue . ':reserved', $queue);
        }
    }

    /**
     * 移动延迟任务
     *
     * @param string $from
     * @param string $to
     * @param bool   $attempt
     */
    public function migrateExpiredJobs($from, $to, $attempt = true)
    {
        $this->redis->watch($from);

        $jobs = $this->redis->zRangeByScore($from, '-inf', $this->currentTime());

        if (!empty($jobs)) {
            $this->transaction(function () use ($from, $to, $jobs, $attempt) {

                $this->redis->zRemRangeByRank($from, 0, count($jobs) - 1);

                for ($i = 0; $i < count($jobs); $i += 100) {

                    $values = array_slice($jobs, $i, 100);

                    $this->redis->rPush($to, ...$values);
                }
            });
        }

        $this->redis->unwatch();
    }

    /**
     * Retrieve the next job from the queue.
     *
     * @param string $queue
     * @return array
     */
    protected function retrieveNextJob($queue)
    {
        if (!is_null($this->blockFor)) {
            return $this->blockingPop($queue);
        }

        $job      = $this->redis->lpop($queue);
        $reserved = false;

        if ($job) {
            $reserved = json_decode($job, true);
            $reserved['attempts']++;
            $reserved = json_encode($reserved);
            $this->redis->zAdd($queue . ':reserved', $this->availableAt($this->retryAfter), $reserved);
        }

        return [$job, $reserved];
    }

    /**
     * Retrieve the next job by blocking-pop.
     *
     * @param string $queue
     * @return array
     */
    protected function blockingPop($queue)
    {
        $rawBody = $this->redis->blpop($queue, $this->blockFor);

        if (!empty($rawBody)) {
            $payload = json_decode($rawBody[1], true);

            $payload['attempts']++;

            $reserved = json_encode($payload);

            $this->redis->zadd($queue . ':reserved', $this->availableAt($this->retryAfter), $reserved);

            return [$rawBody[1], $reserved];
        }

        return [null, null];
    }

    /**
     * 删除任务
     *
     * @param string   $queue
     * @param RedisJob $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        $this->redis->zRem($this->getQueue($queue) . ':reserved', $job->getReservedJob());
    }

    /**
     * Delete a reserved job from the reserved queue and release it.
     *
     * @param string   $queue
     * @param RedisJob $job
     * @param int      $delay
     * @return void
     */
    public function deleteAndRelease($queue, $job, $delay)
    {
        $queue = $this->getQueue($queue);

        $reserved = $job->getReservedJob();

        $this->redis->zRem($queue . ':reserved', $reserved);

        $this->redis->zAdd($queue . ':delayed', $this->availableAt($delay), $reserved);
    }

    /**
     * redis事务
     * @param Closure $closure
     */
    protected function transaction(Closure $closure)
    {
        $this->redis->multi();
        try {
            call_user_func($closure);
            if (!$this->redis->exec()) {
                $this->redis->discard();
            }
        } catch (Exception $e) {
            $this->redis->discard();
        }
    }

    protected function createPayloadArray($job, $data = '')
    {
        return array_merge(parent::createPayloadArray($job, $data), [
            'id'       => $this->getRandomId(),
            'attempts' => 0,
        ]);
    }

    /**
     * 随机id
     *
     * @return string
     */
    protected function getRandomId()
    {
        return Str::random(32);
    }

    /**
     * 获取队列名
     *
     * @param string|null $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return 'queues:' . ($queue ?: $this->default);
    }
}
