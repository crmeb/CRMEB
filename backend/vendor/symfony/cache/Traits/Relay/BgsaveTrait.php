<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Traits\Relay;

if (version_compare(phpversion('relay'), '0.11', '>=')) {
    /**
     * @internal
     */
    trait BgsaveTrait
    {
        public function bgsave($arg = null): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgsave(...\func_get_args());
        }
    }
} else {
    /**
     * @internal
     */
    trait BgsaveTrait
    {
        public function bgsave($schedule = false): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgsave(...\func_get_args());
        }
    }
}
