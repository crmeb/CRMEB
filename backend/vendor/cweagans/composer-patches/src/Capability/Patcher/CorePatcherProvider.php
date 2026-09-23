<?php

namespace cweagans\Composer\Capability\Patcher;

use cweagans\Composer\Patcher\FreeformPatcher;
use cweagans\Composer\Patcher\GitPatcher;
use cweagans\Composer\Patcher\GitInitPatcher;

class CorePatcherProvider extends BasePatcherProvider
{
    /**
     * @inheritDoc
     */
    public function getPatchers(): array
    {
        return [
            new GitPatcher($this->composer, $this->io, $this->plugin),
            new GitInitPatcher($this->composer, $this->io, $this->plugin),
            new FreeformPatcher($this->composer, $this->io, $this->plugin)
        ];
    }
}
