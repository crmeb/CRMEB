<?php

namespace cweagans\Composer\Patcher;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use cweagans\Composer\Patch;

class GitInitPatcher extends GitPatcher
{
    public function apply(Patch $patch, string $path): bool
    {
        // If the target is already a git repo, the standard Git patcher can handle it.
        if (is_dir($path . '/.git')) {
            return false;
        }

        $this->io->write("Creating temporary git repo in $path to apply patch", true, IOInterface::VERBOSE);

        // Create a temporary git repository.
        $status = $this->executeCommand(
            '%s -C %s init',
            $this->patchTool(),
            $path
        );

        // If we couldn't create the Git repo, bail out.
        if (!$status) {
            return false;
        }

        // Use the git patcher to apply the patch.
        $status = parent::apply($patch, $path);

        // Clean up the git repo.
        (new Filesystem($this->executor))->removeDirectory($path . '/.git');

        return $status;
    }
}
