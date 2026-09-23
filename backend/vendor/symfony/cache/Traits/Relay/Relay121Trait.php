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

if (version_compare(phpversion('relay'), '0.12.1', '>=')) {
    /**
     * @internal
     */
    trait Relay121Trait
    {
        public function hgetWithMeta($hash, $member): \Relay\Relay|array|false
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hgetWithMeta(...\func_get_args());
        }

        public function select($db): \Relay\Relay|bool|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->select(...\func_get_args());
        }

        public function watch($key, ...$other_keys): \Relay\Relay|bool|string
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->watch(...\func_get_args());
        }
    }
} else {
    /**
     * @internal
     */
    trait Relay121Trait
    {
        public function select($db): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->select(...\func_get_args());
        }

        public function watch($key, ...$other_keys): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->watch(...\func_get_args());
        }
    }
}
