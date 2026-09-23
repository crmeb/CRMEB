<?php

namespace cweagans\Composer\Capability\Patcher;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\Capability;
use Composer\Plugin\PluginInterface;

/**
 * Patcher provider interface.
 *
 * This capability will receive an array with 'composer' and 'io' keys as
 * constructor arguments. It also contains a 'plugin' key containing the
 * plugin instance that declared the capability.
 */
abstract class BasePatcherProvider implements Capability, PatcherProvider
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
     * BasePatcherProvider constructor.
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
    abstract public function getPatchers(): array;
}
