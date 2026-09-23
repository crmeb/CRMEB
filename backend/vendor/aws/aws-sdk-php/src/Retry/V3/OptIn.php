<?php
namespace Aws\Retry\V3;

/**
 * Source of truth for the AWS_NEW_RETRIES_2026 opt-in flag. The env var
 * is read once per process and memoized so retry decisions do not pay a
 * getenv() cost on every attempt.
 *
 * @internal
 */
final class OptIn
{
    public const ENV = 'AWS_NEW_RETRIES_2026';

    private static ?bool $enabled = null;

    public static function isEnabled(): bool
    {
        if (self::$enabled === null) {
            $value = getenv(self::ENV);
            if (is_string($value) && trim($value) === 'true') {
                self::$enabled = true;
            } else {
                self::$enabled = false;
            }
        }

        return self::$enabled;
    }

    /**
     * Clears the memoized value. Test hook only.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$enabled = null;
    }
}
