<?php

namespace cweagans\Composer\Capability\Resolver;

use cweagans\Composer\Resolver\Dependencies;
use cweagans\Composer\Resolver\PatchesFile;
use cweagans\Composer\Resolver\RootComposer;

class CoreResolverProvider extends BaseResolverProvider
{
    /**
     * @inheritDoc
     */
    public function getResolvers(): array
    {
        return [
            new RootComposer($this->composer, $this->io, $this->plugin),
            new PatchesFile($this->composer, $this->io, $this->plugin),
            new Dependencies($this->composer, $this->io, $this->plugin),
        ];
    }
}
