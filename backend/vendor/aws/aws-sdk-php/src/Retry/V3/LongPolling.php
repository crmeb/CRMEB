<?php
namespace Aws\Retry\V3;

/**
 * Operations that use server-side long polling and should not be retried
 * when the retry quota is exhausted; instead the middleware sleeps for
 * the computed backoff and lets the next request proceed.
 *
 * @internal
 */
final class LongPolling
{
    private const OPERATIONS = [
        'sqs'    => ['ReceiveMessage' => true],
        'states' => ['GetActivityTask' => true],
        'swf'    => [
            'PollForActivityTask' => true,
            'PollForDecisionTask' => true,
        ],
    ];

    public static function isLongPolling(?string $service, string $operation): bool
    {
        return $service !== null
            && isset(self::OPERATIONS[$service][$operation]);
    }
}
