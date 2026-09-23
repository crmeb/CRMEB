<?php

namespace cweagans\Composer\Capability\Resolver;

use Composer\Plugin\Capability\Capability;
use cweagans\Composer\Resolver\ResolverInterface;

/**
 * Resolver provider interface.
 *
 * This capability will receive an array with 'composer' and 'io' keys as
 * constructor arguments. It also contains a 'plugin' key containing the
 * plugin instance that declared the capability.
 */
interface ResolverProvider extends Capability
{
    /**
     * Retrieves an array of PatchResolvers.
     *
     * @return ResolverInterface[]
     */
    public function getResolvers(): array;
}
