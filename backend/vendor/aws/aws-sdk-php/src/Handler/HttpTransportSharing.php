<?php
namespace Aws\Handler;

use GuzzleHttp\TransportSharing;

/**
 * @internal
 */
final class HttpTransportSharing
{
    private const NONE = 'none';
    private const HANDLER_PREFER = 'handler_prefer';
    private const HANDLER_REQUIRE = 'handler_require';
    private const PERSISTENT_PREFER = 'persistent_prefer';
    private const PERSISTENT_REQUIRE = 'persistent_require';

    private const MODES = [
        self::NONE,
        self::HANDLER_PREFER,
        self::HANDLER_REQUIRE,
        self::PERSISTENT_PREFER,
        self::PERSISTENT_REQUIRE,
    ];

    public static function isRequired(?string $mode): bool
    {
        return $mode === self::HANDLER_REQUIRE
            || $mode === self::PERSISTENT_REQUIRE;
    }

    /**
     * Validates a requested transport sharing mode without resolving it
     * against the capabilities of the installed version of Guzzle.
     */
    public static function validate(?string $mode): void
    {
        if ($mode !== null && !in_array($mode, self::MODES, true)) {
            throw new \InvalidArgumentException('The provided transport'
                . ' sharing mode "' . $mode . '" is invalid. Valid modes are:'
                . ' "none", "handler_prefer", "handler_require",'
                . ' "persistent_prefer", "persistent_require".');
        }
    }

    /**
     * Resolves a requested transport sharing mode to the mode that should be
     * passed to the installed version of Guzzle, or null when no mode should
     * be passed. The "*_prefer" modes degrade gracefully when the installed
     * version of Guzzle cannot honor them, and the "*_require" modes throw.
     */
    public static function resolve(?string $mode): ?string
    {
        self::validate($mode);

        if ($mode === null || $mode === self::NONE) {
            return null;
        }

        // Guzzle 8: all modes are understood, and Guzzle enforces the
        // runtime requirements of the "*_require" modes itself.
        if (self::supportsPersistentSharing()) {
            return $mode;
        }

        // Guzzle 7.11+: handler-lifetime sharing only.
        if (self::supportsHandlerSharing()) {
            if ($mode === self::PERSISTENT_PREFER) {
                return self::HANDLER_PREFER;
            }

            if ($mode === self::PERSISTENT_REQUIRE) {
                throw new \RuntimeException('The "persistent_require"'
                    . ' transport sharing mode requires guzzlehttp/guzzle'
                    . ' ^8.0.');
            }

            return $mode;
        }

        // Guzzle < 7.11: no transport sharing support.
        if ($mode === self::PERSISTENT_REQUIRE) {
            throw new \RuntimeException('The "persistent_require" transport'
                . ' sharing mode requires guzzlehttp/guzzle ^8.0.');
        }

        if ($mode === self::HANDLER_REQUIRE) {
            throw new \RuntimeException('The "handler_require" transport'
                . ' sharing mode requires guzzlehttp/guzzle ^7.11 || ^8.0.');
        }

        return null;
    }

    /**
     * Resolves a requested transport sharing mode to a Guzzle client
     * constructor configuration array.
     */
    public static function toClientConfig(?string $mode): array
    {
        $mode = self::resolve($mode);

        return $mode === null ? [] : ['transport_sharing' => $mode];
    }

    private static function supportsPersistentSharing(): bool
    {
        static $supported;

        return $supported ??= defined(TransportSharing::class . '::PERSISTENT_PREFER');
    }

    private static function supportsHandlerSharing(): bool
    {
        static $supported;

        return $supported ??= class_exists(TransportSharing::class);
    }
}
