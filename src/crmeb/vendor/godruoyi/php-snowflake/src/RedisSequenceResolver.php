<?php

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
     * The redis client instance.
     *
     * @var \Redis
     */
    protected $redis;

    /**
     * The cache prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Init resolve instance, must connectioned.
     *
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        if ($redis->ping()) {
            $this->redis = $redis;

            return;
        }

        throw new RedisException('Redis server went away');
    }

    /**
     *  {@inheritdoc}
     */
    public function sequence(int $currentTime)
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('psetex',KEYS[1],ARGV[2],ARGV[1])";

        if ($this->redis->eval($lua, [($key = $this->prefix.$currentTime), 1, 1000], 1)) {
            return 0;
        }

        return $this->redis->incrby($key, 1);
    }

    /**
     * Set cacge prefix.
     *
     * @param string $prefix
     */
    public function setCachePrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }
}
