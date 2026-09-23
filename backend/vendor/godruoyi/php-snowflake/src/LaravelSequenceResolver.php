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

use Illuminate\Contracts\Cache\Repository;

class LaravelSequenceResolver implements SequenceResolver
{
    /**
     * The cache prefix.
     */
    protected string $prefix = '';

    /**
     * Init resolve instance, must be connected.
     */
    public function __construct(protected Repository $cache)
    {
    }

    public function sequence(int $currentTime): int
    {
        $key = $this->prefix.$currentTime;

        if ($this->cache->add($key, 1, 10)) {
            return 0;
        }

        return $this->cache->increment($key) | 0;
    }

    public function setCachePrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
