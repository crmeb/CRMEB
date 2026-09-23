<?php

/**
 * @file
 * Contains \cweagans\Composer\Resolvers\ResolverInterface.
 */

namespace cweagans\Composer\Resolver;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\PatchCollection;

interface ResolverInterface
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
     * Find and add patches to the supplied PatchCollection.
     *
     * @param PatchCollection $collection
     *   A collection of patches that will eventually be applied.
     */
    public function resolve(PatchCollection $collection): void;
}
