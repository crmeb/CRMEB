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

if (version_compare(phpversion('relay'), '0.9.0', '>=')) {
    /**
     * @internal
     */
    trait SwapdbTrait
    {
        public function swapdb($index1, $index2): \Relay\Relay|bool
        {
            return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->swapdb(...\func_get_args());
        }
    }
} else {
    /**
     * @internal
     */
    trait SwapdbTrait
    {
    }
}
