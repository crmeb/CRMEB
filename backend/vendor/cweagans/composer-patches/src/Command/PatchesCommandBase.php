<?php

declare(strict_types=1);

namespace cweagans\Composer\Command;

use Composer\Command\BaseCommand;
use cweagans\Composer\Plugin\Patches;

abstract class PatchesCommandBase extends BaseCommand
{
    /**
     * Get the Patches plugin
     *
     * @return Patches|null
     */
    protected function getPatchesPluginInstance(): ?Patches
    {
        foreach ($this->requireComposer()->getPluginManager()->getPlugins() as $plugin) {
            if ($plugin instanceof Patches) {
                return $plugin;
            }
        }

        return null;
    }
}
