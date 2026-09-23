<?php

declare(strict_types=1);

/*
 * This file is part of the godruoyi/php-snowflake.
 *
 * (c) Godruoyi <g@godruoyi.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\Snowflake;

class SwooleSequenceResolver implements SequenceResolver
{
    /**
     * The last timestamp.
     */
    protected ?int $lastTimeStamp = -1;

    /**
     * The sequence.
     */
    protected int $sequence = 0;

    /**
     * The swoole lock.
     */
    protected \Swoole\Lock $lock;

    /**
     * The cycle count.
     */
    protected int $count = 0;

    /**
     * Init swoole lock.
     */
    public function __construct()
    {
        $this->lock = new \Swoole\Lock(SWOOLE_MUTEX);
    }

    /**
     * @throws SnowflakeException
     */
    public function sequence(int $currentTime): int
    {
        // Swoole removed "tryLock" method in 6.1.0
        if (method_exists($this->lock, 'tryLock')) {
            $acquiredLock = $this->lock->tryLock();
        } else {
            $acquiredLock = $this->lock->lock(LOCK_EX | LOCK_NB);
        }

        // If swoole lock failure，we will return a big number, and recall this method when next millisecond.
        if (! $acquiredLock) {
            if ($this->count >= 10) {
                throw new SnowflakeException('Swoole lock failure, Unable to get the program lock after many attempts.');
            }

            $this->count++;

            // return a big number
            return 999999;
        }

        if ($this->lastTimeStamp === $currentTime) {
            $this->sequence++;
        } else {
            $this->sequence = 0;
        }

        $this->lastTimeStamp = $currentTime;

        $this->lock->unlock();

        return $this->sequence;
    }

    public function resetLock(\Swoole\Lock $lock): void
    {
        $this->lock = $lock;
    }
}
