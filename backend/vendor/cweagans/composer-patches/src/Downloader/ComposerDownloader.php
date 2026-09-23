<?php

namespace cweagans\Composer\Downloader;

use Composer\Util\HttpDownloader;
use cweagans\Composer\Downloader\Exception\HashMismatchException;
use cweagans\Composer\Patch;

class ComposerDownloader extends DownloaderBase
{
    /**
     * @inheritDoc
     */
    public function download(Patch $patch): void
    {
        static $downloader;
        if (is_null($downloader)) {
            $downloader = new HttpDownloader($this->io, $this->composer->getConfig());
        }

        // Don't need to re-download a patch if it has already been downloaded.
        if (isset($patch->localPath) && !empty($patch->localPath)) {
            return;
        }

        $patches_dir = sys_get_temp_dir() . '/composer-patches/';
        $filename = uniqid($patches_dir) . ".patch";
        if (!is_dir($patches_dir)) {
            mkdir($patches_dir);
        }

        $downloader->copy($patch->url, $filename);
        $patch->localPath = $filename;

        $hash = hash_file('sha256', $filename);

        if (!isset($patch->sha256)) {
            $patch->sha256 = $hash;
        } elseif ($hash !== $patch->sha256) {
            throw new HashMismatchException($patch->url, $hash, $patch->sha256);
        }
    }
}
