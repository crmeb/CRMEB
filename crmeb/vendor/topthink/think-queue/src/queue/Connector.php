<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace think\queue;

use DateTimeInterface;
use InvalidArgumentException;
use think\App;

abstract class Connector
{
    /** @var App */
    protected $app;

    /**
     * The connector name for the queue.
     *
     * @var string
     */
    protected $connection;

    protected $options = [];

    abstract public function size($queue);

    abstract public function push($job, $data = '', $queue = null);

    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    abstract public function pushRaw($payload, $queue = null, array $options = []);

    abstract public function later($delay, $job, $data = '', $queue = null);

    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }

    abstract public function pop($queue = null);

    protected function createPayload($job, $data = '')
    {
        $payload = $this->createPayloadArray($job, $data);

        $payload = json_encode($payload);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }

        return $payload;
    }

    protected function createPayloadArray($job, $data = '')
    {
        return is_object($job)
            ? $this->createObjectPayload($job)
            : $this->createPlainPayload($job, $data);
    }

    protected function createPlainPayload($job, $data)
    {
        return [
            'job'      => $job,
            'maxTries' => null,
            'timeout'  => null,
            'data'     => $data,
        ];
    }

    protected function createObjectPayload($job)
    {
        $payload = [
            'job'       => 'think\queue\CallQueuedHandler@call',
            'maxTries'  => $job->tries ?? null,
            'timeout'   => $job->timeout ?? null,
            'timeoutAt' => $this->getJobExpiration($job),
            'data'      => [
                'commandName' => $job,
                'command'     => $job,
            ],
        ];

        return array_merge($payload, [
            'data' => [
                'commandName' => get_class($job),
                'command'     => serialize(clone $job),
            ],
        ]);
    }

    public function getJobExpiration($job)
    {
        if (!method_exists($job, 'retryUntil') && !isset($job->timeoutAt)) {
            return;
        }

        $expiration = $job->timeoutAt ?? $job->retryUntil();

        return $expiration instanceof DateTimeInterface
            ? $expiration->getTimestamp() : $expiration;
    }

    protected function setMeta($payload, $key, $value)
    {
        $payload       = json_decode($payload, true);
        $payload[$key] = $value;
        $payload       = json_encode($payload);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }

        return $payload;
    }

    public function setApp(App $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Get the connector name for the queue.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the connector name for the queue.
     *
     * @param string $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }
}
