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

if (version_compare(phpversion('relay'), '0.11.1', '>=')) {
    /**
     * @internal
     */
    trait IsTrackedTrait
    {
        public function isTracked($key): bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->isTracked(...\func_get_args());
        }
    }
} else {
    /**
     * @internal
     */
    trait IsTrackedTrait
    {
    }
}
