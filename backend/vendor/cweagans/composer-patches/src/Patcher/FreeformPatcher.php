<?php

namespace cweagans\Composer\Patcher;

use cweagans\Composer\Patch;
use Composer\IO\IOInterface;

class FreeformPatcher extends PatcherBase
{
    public function apply(Patch $patch, string $path): bool
    {
        // Required.
        $patchTool = $patch->extra['freeform']['executable'] ?? '';
        $args = $patch->extra['freeform']['args'] ?? '';

        // Optional.
        $dryRunArgs = $patch->extra['freeform']['dry_run_args'] ?? '';

        // If we don't have what we need, exit.
        if (empty($patchTool) || empty($args)) {
            $this->io->write(
                'Required configuration for FreeformPatcher not present. Skipping.',
                true,
                IOInterface::VERBOSE
            );
            return false;
        }

        // If we have dry-run args, do a dry-run.
        if (!empty($dryRunArgs)) {
            $status = $this->executeCommand(
                '%s ' . $dryRunArgs,
                $patchTool,
                $patch->depth,
                $path,
                $patch->localPath,
            );
            if (!$status) {
                return false;
            }
        }

        // Apply the patch.
        return $this->executeCommand(
            '%s ' . $args,
            $patchTool,
            $patch->depth,
            $path,
            $patch->localPath,
        );
    }

    public function canUse(): bool
    {
        // Hardcoded to true because apply() will bail out if the freeform args are not set on the patch (or globally).
        // Users who opt-in to this patcher are responsible for providing a valid executable and arguments.
        return true;
    }
}
