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

use Exception;
use think\App;
use think\helper\Arr;
use think\helper\Str;

abstract class Job
{

    /**
     * The job handler instance.
     * @var object
     */
    private $instance;

    /**
     *  The JSON decoded version of "$job".
     * @var array
     */
    private $payload;

    /**
     * @var App
     */
    protected $app;

    /**
     * The name of the queue the job belongs to.
     * @var string
     */
    protected $queue;

    /**
     * The name of the connection the job belongs to.
     */
    protected $connection;

    /**
     * Indicates if the job has been deleted.
     * @var bool
     */
    protected $deleted = false;

    /**
     * Indicates if the job has been released.
     * @var bool
     */
    protected $released = false;

    /**
     * Indicates if the job has failed.
     *
     * @var bool
     */
    protected $failed = false;

    /**
     * Get the decoded body of the job.
     *
     * @return mixed
     */
    public function payload($name = null, $default = null)
    {
        if (empty($this->payload)) {
            $this->payload = json_decode($this->getRawBody(), true);
        }
        if (empty($name)) {
            return $this->payload;
        }
        return Arr::get($this->payload, $name, $default);
    }

    /**
     * Fire the job.
     * @return void
     */
    public function fire()
    {
        $instance = $this->getResolvedJob();

        [, $method] = $this->getParsedJob();

        $instance->{$method}($this, $this->payload('data'));
    }

    /**
     * Process an exception that caused the job to fail.
     *
     * @param Exception $e
     * @return void
     */
    public function failed($e)
    {
        $instance = $this->getResolvedJob();

        if (method_exists($instance, 'failed')) {
            $instance->failed($this->payload('data'), $e);
        }
    }

    /**
     * Delete the job from the queue.
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }

    /**
     * Determine if the job has been deleted.
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Release the job back into the queue.
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }

    /**
     * Determine if the job was released back into the queue.
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }

    /**
     * Determine if the job has been deleted or released.
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    abstract public function getJobId();

    /**
     * Get the number of times the job has been attempted.
     * @return int
     */
    abstract public function attempts();

    /**
     * Get the raw body string for the job.
     * @return string
     */
    abstract public function getRawBody();

    /**
     * Parse the job declaration into class and method.
     * @return array
     */
    protected function getParsedJob()
    {
        $job      = $this->payload('job');
        $segments = explode('@', $job);

        return count($segments) > 1 ? $segments : [$segments[0], 'fire'];
    }

    /**
     * Resolve the given job handler.
     * @param string $name
     * @return mixed
     */
    protected function resolve($name, $param)
    {
        $namespace = $this->app->getNamespace() . '\\job\\';

        $class = false !== strpos($name, '\\') ? $name : $namespace . Str::studly($name);

        return $this->app->make($class, [$param], true);
    }

    public function getResolvedJob()
    {
        if (empty($this->instance)) {
            [$class] = $this->getParsedJob();

            $this->instance = $this->resolve($class, $this->payload('data'));
        }

        return $this->instance;
    }

    /**
     * Determine if the job has been marked as a failure.
     *
     * @return bool
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * Mark the job as "failed".
     *
     * @return void
     */
    public function markAsFailed()
    {
        $this->failed = true;
    }

    /**
     * Get the number of times to attempt a job.
     *
     * @return int|null
     */
    public function maxTries()
    {
        return $this->payload('maxTries');
    }

    /**
     * Get the number of seconds the job can run.
     *
     * @return int|null
     */
    public function timeout()
    {
        return $this->payload('timeout');
    }

    /**
     * Get the timestamp indicating when the job should timeout.
     *
     * @return int|null
     */
    public function timeoutAt()
    {
        return $this->payload('timeoutAt');
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->payload('job');
    }

    /**
     * Get the name of the connection the job belongs to.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the name of the queue the job belongs to.
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
