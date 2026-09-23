<?php

namespace cweagans\Composer\Capability\Downloader;

use cweagans\Composer\Downloader\DownloaderInterface;

/**
 * Downloader provider interface.
 *
 * This capability will receive an array with 'composer' and 'io' keys as
 * constructor arguments. It also contains a 'plugin' key containing the
 * plugin instance that declared the capability.
 */
interface DownloaderProvider
{
    /**
     * Retrieves an array of Downloaders.
     *
     * @return DownloaderInterface[]
     */
    public function getDownloaders(): array;
}
