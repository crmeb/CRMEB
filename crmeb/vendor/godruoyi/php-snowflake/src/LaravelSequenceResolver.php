<?php

/*
 * This file is part of the godruoyi/php-snowflake.
 *
 * (c) Godruoyi <g@godruoyi.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\Snowflake;

use Illuminate\Contracts\Cache\Repository;

class LaravelSequenceResolver implements SequenceResolver
{
    /**
     * The laravel cache instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Init resolve instance, must connectioned.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     *  {@inheritdoc}
     */
    public function sequence(int $currentTime)
    {
        $store = $this->cache->getStore();

        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('psetex',KEYS[1],ARGV[2],ARGV[1])";
            if ($store->connection()->eval($lua, 1, $key = $currentTime, 1, 1000)) {
                return 0;
            }

            return $store->connection()->incrby($key, 1);
        }

        // Currently we only implement the redis driver, other drivers
        // are waiting for your implementation ~_~.
        throw new \Exception('Unsupported laravel cache driver.');
    }
}
