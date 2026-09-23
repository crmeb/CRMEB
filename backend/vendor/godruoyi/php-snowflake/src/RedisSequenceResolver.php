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

use Redis;
use RedisException;

class RedisSequenceResolver implements SequenceResolver
{
    /**
     * The cache prefix.
     */
    protected string $prefix = '';

    /**
     * The default redis lua script
     */
    protected static string $script = <<<'LUA'
        if redis.call('set', KEYS[1], ARGV[1], "EX", ARGV[2], "NX") then
            return 0
        else
            return redis.call('incr', KEYS[1])
        end
        LUA;

    /**
     * Init resolve instance, must be connected.
     *
     * @throws RedisException
     */
    public function __construct(protected Redis $redis)
    {
        if (! $redis->ping()) {
            throw new RedisException('Redis server went away');
        }
    }

    /**
     * @throws RedisException
     */
    public function sequence(int $currentTime): int
    {
        // 10 seconds
        return $this->redis->eval(self::$script, [$this->prefix.$currentTime, '0', '10'], 1) | 0;
    }

    /**
     * Set cache prefix.
     */
    public function setCachePrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
