<?php

namespace cweagans\Composer\Patcher;

use cweagans\Composer\Patch;
use Composer\IO\IOInterface;

class GitPatcher extends PatcherBase
{
    protected string $tool = 'git';

    public function apply(Patch $patch, string $path): bool
    {
        // If the path isn't a git repo, don't even try.
        // @see https://stackoverflow.com/a/27283285
        if (!is_dir($path . '/.git')) {
            $this->io->write("$path is not a git repo. Skipping Git patcher.", true, IOInterface::VERBOSE);
            return false;
        }

        // Dry run first.
        $status = $this->executeCommand(
            '%s -C %s apply --check --verbose -p%s %s',
            $this->patchTool(),
            $path,
            $patch->depth,
            $patch->localPath
        );
        if (str_starts_with($this->executor->getErrorOutput(), 'Skipped')) {
            // Git will indicate success but silently skip patches in some scenarios.
            //
            // @see https://github.com/cweagans/composer-patches/pull/165
            $status = false;
        }

        // If the check failed, then don't proceed with actually applying the patch.
        if (!$status) {
            return false;
        }

        // Otherwise, we can try to apply the patch.
        return $this->executeCommand(
            '%s -C %s apply -p%s --verbose %s',
            $this->patchTool(),
            $path,
            $patch->depth,
            $patch->localPath
        );
    }

    public function canUse(): bool
    {
        $output = "";
        $result = $this->executor->execute($this->patchTool() . ' --version', $output);
        return ($result === 0);
    }
}
