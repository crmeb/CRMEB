<?php

/**
 * @file
 * Dispatch events when patches are applied or downloaded.
 */

namespace cweagans\Composer\Event;

class PatchEvents
{
    /**
     * The PRE_GUESS_DEPTH event occurs before a depth is guessed for a patch.
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const PRE_PATCH_GUESS_DEPTH = 'pre-patch-guess-depth';

    /**
     * The PRE_PATCH_DOWNLOAD event occurs before a patch is downloaded
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const PRE_PATCH_DOWNLOAD = 'pre-patch-download';

    /**
     * The POST_PATCH_DOWNLOAD event occurs after a patch is downloaded
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const POST_PATCH_DOWNLOAD = 'post-patch-download';

    /**
     * The PRE_PATCH_APPLY event occurs before a patch is applied.
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const PRE_PATCH_APPLY = 'pre-patch-apply';

    /**
     * The POST_PATCH_APPLY event occurs after a patch is applied.
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const POST_PATCH_APPLY = 'post-patch-apply';


    /**
     * The POST_PATCH_APPLY_ERROR event occurs when a patch could not be applied by any patcher.
     *
     * The event listener method receives a cweagans\Composer\Event\PatchEvent instance.
     *
     * @var string
     */
    public const POST_PATCH_APPLY_ERROR = 'post-patch-apply-error';
}
