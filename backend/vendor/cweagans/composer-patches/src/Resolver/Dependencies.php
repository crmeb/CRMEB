<?php

/**
 * @file
 * Contains \cweagans\Composer\Resolvers\Dependencies.
 */

namespace cweagans\Composer\Resolver;

use cweagans\Composer\Patch;
use cweagans\Composer\PatchCollection;

class Dependencies extends ResolverBase
{
    /**
     * {@inheritDoc}
     */
    public function resolve(PatchCollection $collection): void
    {
        $locker = $this->composer->getLocker();
        if (!$locker->isLocked()) {
            $this->io->write('  - <info>Composer lock file does not exist.</info>');
            $this->io->write('  - <info>Patches defined in dependencies will not be resolved.</info>');
            return;
        }

        $this->io->write('  - <info>Resolving patches from dependencies.</info>');

        $ignored_dependencies = $this->plugin->getConfig('ignore-dependency-patches');

        $lockdata = $locker->getLockData();
        foreach ($lockdata['packages'] as $p) {
            // If we're supposed to skip gathering patches from a dependency, do that.
            if (in_array($p['name'], $ignored_dependencies)) {
                continue;
            }

            // Find patches in the composer.json for dependencies.
            if (!isset($p['extra']) || !isset($p['extra']['patches'])) {
                continue;
            }
            foreach ($this->findPatchesInJson($p['extra']['patches']) as $package => $patches) {
                foreach ($patches as $patch) {
                    $patch->extra['provenance'] = "dependency:" . $package;

                    /** @var Patch $patch */
                    $collection->addPatch($patch);
                }
            }

            // TODO: Also find patches in a configured patches.json for the dependency.
        }
    }
}
