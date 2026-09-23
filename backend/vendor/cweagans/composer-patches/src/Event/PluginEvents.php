<?php

/**
 * @file
 * Dispatch events when capabilities are discovered.
 */

namespace cweagans\Composer\Event;

class PluginEvents
{
    /**
     * The POST_DISCOVER_DOWNLOADERS event occurs after Downloader capabilities are discovered.
     *
     * The event listener method receives a cweagans\Composer\Event\PluginEvent instance.
     *
     * @var string
     */
    public const POST_DISCOVER_DOWNLOADERS = 'post-discover-downloaders';

    /**
     * The POST_DISCOVER_PATCHERS event occurs after Patcher capabilities are discovered.
     *
     * The event listener method receives a cweagans\Composer\Event\PluginEvent instance.
     *
     * @var string
     */
    public const POST_DISCOVER_PATCHERS = 'post-discover-patchers';

    /**
     * The POST_DISCOVER_RESOLVERS event occurs after Resolver capabilities are discovered.
     *
     * The event listener method receives a cweagans\Composer\Event\PluginEvent instance.
     *
     * @var string
     */
    public const POST_DISCOVER_RESOLVERS = 'post-discover-resolvers';
}
