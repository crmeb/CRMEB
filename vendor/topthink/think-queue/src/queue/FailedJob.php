<?php

namespace think\queue;

abstract class FailedJob
{
    /**
     * Log a failed job into storage.
     *
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     * @return int|null
     */
    abstract public function log($connection, $queue, $payload, $exception);

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    abstract public function all();

    /**
     * Get a single failed job.
     *
     * @param mixed $id
     * @return object|null
     */
    abstract public function find($id);

    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
     * @return bool
     */
    abstract public function forget($id);

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    abstract public function flush();
}
