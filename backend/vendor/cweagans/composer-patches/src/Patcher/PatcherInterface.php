<?php

namespace cweagans\Composer\Patcher;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\Patch;

interface PatcherInterface
{
    /**
     * ResolverInterface constructor.
     *
     * @param Composer $composer
     *   The current composer object from the main plugin. Used to locate/read
     *   package metadata and configuration.
     * @param IOInterface $io
     *   IO object to use for resolver input/output.
     * @param PluginInterface $plugin
     *   The main plugin class.
     */
    public function __construct(Composer $composer, IOInterface $io, PluginInterface $plugin);

    /**
     * Apply a patch.
     *
     * @param Patch $patch
     *   The patch to apply.
     * @param string $path
     *   The path to the package to patch.
     */
    public function apply(Patch $patch, string $path): bool;

    /**
     * Check to see if this patcher is usable.
     *
     * For CLI tool-based patchers (all Patchers included in this plugin), this is usually sanity-checking the CLI tool.
     *
     * @return bool
     */
    public function canUse(): bool;
}
