<?php

namespace cweagans\Composer\Capability\Patcher;

use cweagans\Composer\Patcher\PatcherInterface;

/**
 * Patcher provider interface.
 *
 * This capability will receive an array with 'composer' and 'io' keys as
 * constructor arguments. It also contains a 'plugin' key containing the
 * plugin instance that declared the capability.
 */
interface PatcherProvider
{
    /**
     * Retrieves an array of Patchers.
     *
     * @return PatcherInterface[]
     */
    public function getPatchers(): array;
}
