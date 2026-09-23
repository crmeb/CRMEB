<?php

namespace cweagans\Composer\Capability\Resolver;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\Capability;
use Composer\Plugin\PluginInterface;

abstract class BaseResolverProvider implements Capability, ResolverProvider
{
    /**
     * @var Composer
     */
    protected Composer $composer;

    /**
     * @var IOInterface
     */
    protected IOInterface $io;

    /**
     * @var PluginInterface
     */
    protected PluginInterface $plugin;

    /**
     * BaseResolverProvider constructor.
     *
     * Store values passed by the plugin manager for later use.
     *
     * @param array $args
     *   An array of args passed by the plugin manager.
     */
    public function __construct(array $args)
    {
        $this->composer = $args['composer'];
        $this->io = $args['io'];
        $this->plugin = $args['plugin'];
    }

    /**
     * @inheritDoc
     */
    abstract public function getResolvers(): array;
}
