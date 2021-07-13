<?php

/*
 * This file is part of the godruoyi/php-snowflake.
 *
 * (c) Godruoyi <g@godruoyi.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Godruoyi\Snowflake;

interface SequenceResolver
{
    /**
     * The snowflake.
     *
     * @param int|string $currentTime current request ms
     *
     * @return int
     */
    public function sequence(int $currentTime);
}
