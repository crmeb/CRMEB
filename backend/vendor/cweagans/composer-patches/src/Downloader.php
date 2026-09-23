<?php

namespace cweagans\Composer;

use Composer\Composer;
use cweagans\Composer\Downloader\DownloaderInterface;
use Composer\IO\IOInterface;
use cweagans\Composer\Capability\Downloader\DownloaderProvider;
use cweagans\Composer\Event\PluginEvent;
use cweagans\Composer\Event\PluginEvents;
use UnexpectedValueException;

class Downloader
{
    protected Composer $composer;

    protected IOInterface $io;

    protected array $disabledDownloaders;

    protected string $cacheDir;

    public function __construct(Composer $composer, IOInterface $io, array $disabledDownloaders)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->disabledDownloaders = $disabledDownloaders;

        // If --no-cache is passed to Composer, we need a different location to
        // download patches to. When --no-cache is passed, $composer_cache is
        // set to /dev/null.
        $composer_cache = $composer->getConfig()->get('cache-dir');
        if (!is_dir($composer_cache)) {
            $composer_cache = sys_get_temp_dir();
        }

        // If the cache directory doesn't exist, create it.
        $this->cacheDir = $composer_cache . '/patches';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir);
        }
    }

    /**
     * Download a patch using the available downloaders.
     *
     * A downloader will update Patch->localPath if it was able to download the patch.
     *
     * @param Patch $patch
     *   The patch to download.
     */
    public function downloadPatch(Patch $patch)
    {
        if (isset($patch->sha256)) {
            $cachedPatch = $this->cacheDir . '/' . $patch->sha256 . '.patch';
            if (file_exists($cachedPatch)) {
                $this->io->write("      - Found cached patch at <info>{$cachedPatch}</info>", IOInterface::VERBOSE);
                $patch->localPath = $cachedPatch;
                return;
            }
        }

        foreach ($this->getDownloaders() as $downloader) {
            $class = "\\" . get_class($downloader);
            if (in_array($class, $this->disabledDownloaders, true)) {
                $this->io->write(
                    '<info>  - Skipping downloader ' . $class . '</info>',
                    true,
                    IOInterface::VERBOSE
                );
                continue;
            }

            $downloader->download($patch);

            if (isset($patch->localPath)) {
                $cachedPatch = $this->cacheDir . '/' . $patch->sha256 . '.patch';
                if (rename($patch->localPath, $cachedPatch)) {
                    $patch->localPath = $cachedPatch;
                }
                return;
            }
        }
    }

    /**
     * Gather a list of all patch downloaders from all enabled Composer plugins.
     *
     * @return DownloaderInterface[]
     *   A list of Downloaders that are available.
     */
    protected function getDownloaders(): array
    {
        static $downloaders;
        if (!is_null($downloaders)) {
            return $downloaders;
        }

        $downloaders = [];
        $plugin_manager = $this->composer->getPluginManager();
        $capabilities = $plugin_manager->getPluginCapabilities(
            DownloaderProvider::class,
            ['composer' => $this->composer, 'io' => $this->io]
        );
        foreach ($capabilities as $capability) {
            /** @var DownloaderProvider $capability */
            $newDownloaders = $capability->getDownloaders();
            foreach ($newDownloaders as $downloader) {
                if (!$downloader instanceof DownloaderInterface) {
                    throw new UnexpectedValueException(
                        'Plugin capability ' . get_class($capability) . ' returned an invalid value.'
                    );
                }
            }
            $downloaders = array_merge($downloaders, $newDownloaders);
        }


        $event = new PluginEvent(PluginEvents::POST_DISCOVER_DOWNLOADERS, $downloaders, $this->composer, $this->io);
        $this->composer->getEventDispatcher()->dispatch(PluginEvents::POST_DISCOVER_DOWNLOADERS, $event);
        $downloaders = $event->getCapabilities();

        return $downloaders;
    }
}
