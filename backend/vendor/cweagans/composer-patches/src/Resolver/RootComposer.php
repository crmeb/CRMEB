<?php

/**
 * @file
 * Contains \cweagans\Composer\Resolvers\RootComposer.
 */

namespace cweagans\Composer\Resolver;

use cweagans\Composer\Patch;
use cweagans\Composer\PatchCollection;

class RootComposer extends ResolverBase
{
    /**
     * {@inheritDoc}
     */
    public function resolve(PatchCollection $collection): void
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (empty($extra['patches'])) {
            return;
        }

        $this->io->write('  - <info>Resolving patches from root package.</info>');

        foreach ($this->findPatchesInJson($extra['patches']) as $package => $patches) {
            foreach ($patches as $patch) {
                /** @var Patch $patch */
                $patch->extra['provenance'] = "root";
                $collection->addPatch($patch);
            }
        }
    }
}
