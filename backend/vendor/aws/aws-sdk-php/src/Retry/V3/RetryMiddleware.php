<?php
namespace Aws\Retry\V3;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\MonitoringEventsInterface;
use Aws\ResultInterface;
use Aws\Retry\ConfigurationInterface;
use Aws\Retry\RateLimiter;
use Aws\Retry\RetryHelperTrait;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Retry middleware for the AWS_NEW_RETRIES_2026 opt-in path. Implements
 * the spec-defined behavior for 'standard' and 'adaptive' modes:
 * throttling-aware quota, retry-after header support, max-attempts
 * before quota in the decision order, supplemental customer deciders,
 * and long-polling sleep on quota exhaustion.
 *
 * @internal
 */
class RetryMiddleware
{
    use RetryHelperTrait;

    private const THROTTLING_BASE_DELAY_MS = 1_000;
    private const DEFAULT_BASE_DELAY_MS = 50;
    private const DEFAULT_MAX_BACKOFF_MS = 20_000;
    private const RETRY_AFTER_HEADER = 'x-amz-retry-after';

    private static array $standardThrottlingErrors = [
        'Throttling'                                => true,
        'ThrottlingException'                       => true,
        'ThrottledException'                        => true,
        'RequestThrottledException'                 => true,
        'TooManyRequestsException'                  => true,
        'ProvisionedThroughputExceededException'    => true,
        'TransactionInProgressException'            => true,
        'RequestLimitExceeded'                      => true,
        'BandwidthLimitExceeded'                    => true,
        'LimitExceededException'                    => true,
        'RequestThrottled'                          => true,
        'SlowDown'                                  => true,
        'PriorRequestNotComplete'                   => true,
        'EC2ThrottledException'                     => true,
    ];

    private static array $standardTransientErrors = [
        'RequestTimeout'            => true,
        'RequestTimeoutException'   => true,
    ];

    private static array $standardTransientStatusCodes = [
        500 => true,
        502 => true,
        503 => true,
        504 => true,
    ];

    private float $baseDelayMs;
    private bool $collectStats;
    private ?\Closure $customDecider;
    private ?\Closure $delayer;
    private ?float $exponentialBase;
    private int $maxAttempts;
    private int $maxBackoffMs;
    private string $mode;
    private \Closure $nextHandler;
    private array $options;
    private QuotaManager $quotaManager;
    private ?RateLimiter $rateLimiter = null;
    private ?string $service;

    public static function wrap(ConfigurationInterface $config, array $options): \Closure
    {
        if (!isset($options['quota_manager'])) {
            $options['quota_manager'] = new QuotaManager();
        }

        if ($config->getMode() === 'adaptive'
            && !isset($options['rate_limiter'])
        ) {
            $options['rate_limiter'] = new RateLimiter();
        }

        return function (callable $handler) use ($config, $options) {
            return new static($config, $handler, $options);
        };
    }

    /**
     * Returns a closure that decides retryability for a given result based
     * on the standard error codes and status codes. Quota and max-attempts
     * decisions are handled by the middleware itself, not by this closure.
     */
    public static function createDefaultDecider(array $options = []): \Closure
    {
        return function (
            int $attempts,
            CommandInterface $command,
            mixed $result
        ) use ($options): bool {
            return self::isRetryable($result, $options);
        };
    }

    public function __construct(
        ConfigurationInterface $config,
        callable $handler,
        array $options = []
    ) {
        $this->options = $options;
        $this->maxAttempts = $config->getMaxAttempts();
        $this->mode = $config->getMode();
        $this->nextHandler = $handler(...);
        $this->service = $options['service'] ?? null;
        $this->quotaManager = $options['quota_manager'] ?? new QuotaManager();
        $this->maxBackoffMs = $options['max_backoff'] ?? self::DEFAULT_MAX_BACKOFF_MS;
        $this->baseDelayMs = $options['base_delay'] ?? self::DEFAULT_BASE_DELAY_MS;
        $this->exponentialBase = $options['exponential_base'] ?? null;
        $this->collectStats = (bool) ($options['collect_stats'] ?? false);

        $this->customDecider = isset($options['decider'])
            ? ($options['decider'])(...)
            : null;

        $this->delayer = isset($options['delayer'])
            ? ($options['delayer'])(...)
            : null;

        if ($this->mode === 'adaptive') {
            $this->rateLimiter = $options['rate_limiter'] ?? new RateLimiter();
        }
    }

    public function __invoke(CommandInterface $cmd, RequestInterface $req): PromiseInterface
    {
        $handler = $this->nextHandler;

        $attempts = 1;
        $monitoringEvents = [];
        $requestStats = [];
        $capacityUsed = null;

        $req = $this->addRetryHeader($req, 0, $this->resolveMaxAttempts($cmd));

        $callback = function ($value) use (
            $handler,
            $cmd,
            $req,
            &$attempts,
            &$requestStats,
            &$monitoringEvents,
            &$callback,
            &$capacityUsed
        ) {
            if ($this->mode === 'adaptive') {
                $this->rateLimiter->updateSendingRate($this->isThrottlingError($value));
            }

            $this->updateHttpStats($value, $requestStats);

            if ($value instanceof MonitoringEventsInterface) {
                $reversedEvents = array_reverse($monitoringEvents);
                $monitoringEvents = array_merge($monitoringEvents, $value->getMonitoringEvents());
                foreach ($reversedEvents as $event) {
                    $value->prependMonitoringEvent($event);
                }
            }

            $isError = $value instanceof \Throwable;

            $isSuccess = false;
            if (!$isError && $value instanceof ResultInterface) {
                $statusCode = isset($value['@metadata']['statusCode'])
                    ? (int) $value['@metadata']['statusCode']
                    : null;
                if (!empty($statusCode) && $statusCode >= 200 && $statusCode < 300) {
                    $isSuccess = true;
                }
            }

            if ($isSuccess) {
                $this->quotaManager->releaseQuota($capacityUsed);
                $callback = null;
                return $this->bindStatsToReturn($value, $requestStats);
            }

            $isRetryable = self::isRetryable(
                $value,
                $this->options
            );

            // Custom decider supplements default retryability
            if (!$isRetryable && $this->customDecider !== null) {
                $isRetryable = ($this->customDecider)($attempts, $cmd, $value);
            }

            if (!$isRetryable) {
                if ($isError) {
                    $callback = null;
                    return Promise\Create::rejectionFor(
                        $this->bindStatsToReturn($value, $requestStats)
                    );
                }
                $callback = null;
                return $this->bindStatsToReturn($value, $requestStats);
            }

            // Max attempts is checked before retry quota.
            $maxAttempts = $this->resolveMaxAttempts($cmd);

            if ($attempts >= $maxAttempts) {
                if ($value instanceof AwsException) {
                    $value->setMaxRetriesExceeded();
                }
                if ($isError) {
                    $callback = null;
                    return Promise\Create::rejectionFor(
                        $this->bindStatsToReturn($value, $requestStats)
                    );
                }
                $callback = null;
                return $this->bindStatsToReturn($value, $requestStats);
            }

            $isThrottling = $this->isThrottlingError($value);
            $attemptIndex = $attempts - 1;
            $delayByMs = $this->computeRetryDelay(
                $attemptIndex,
                $isThrottling,
                $value
            );

            $acquired = $this->quotaManager->acquireRetryQuota($isThrottling);
            if ($acquired === false) {
                // Long-polling: sleep and surface the error rather than retry.
                if (LongPolling::isLongPolling($this->service, $cmd->getName())) {
                    $cmd['@http']['delay'] = $delayByMs;
                    usleep((int) ($delayByMs * 1000));
                }

                if ($isError) {
                    $callback = null;
                    return Promise\Create::rejectionFor(
                        $this->bindStatsToReturn($value, $requestStats)
                    );
                }
                $callback = null;
                return $this->bindStatsToReturn($value, $requestStats);
            }
            $capacityUsed = $acquired;

            if ($this->delayer !== null) {
                $delayByMs = ($this->delayer)($attempts);
            }

            $attempts++;
            $cmd['@http']['delay'] = $delayByMs;

            if ($this->collectStats) {
                $this->updateStats($attempts - 1, $delayByMs, $requestStats);
            }

            $req = $this->addRetryHeader(
                $req,
                $attempts - 1,
                $maxAttempts
            );

            if ($this->mode === 'adaptive') {
                $this->rateLimiter->getSendToken();
            }

            return $handler($cmd, $req)->then($callback, $callback);
        };

        if ($this->mode === 'adaptive') {
            $this->rateLimiter->getSendToken();
        }

        return $handler($cmd, $req)->then($callback, $callback);
    }

    public function exponentialDelayWithJitter(int $attempts): int
    {
        return $this->computeRetryDelay($attempts - 1, false, null);
    }

    private function computeRetryDelay(int $attemptIndex, bool $isThrottling, mixed $value): int
    {
        $baseMs = $isThrottling
            ? self::THROTTLING_BASE_DELAY_MS
            : $this->baseDelayMs;

        if ($this->exponentialBase !== null) {
            $jitter = $this->exponentialBase;
        } else {
            $max = mt_getrandmax();
            try {
                $jitter = random_int(0, $max) / $max;
            } catch (\Exception $_) {
                $jitter = mt_rand(0, $max) / $max;
            }
        }

        $delayMs = $jitter * min(
            $baseMs * pow(2, $attemptIndex),
            $this->maxBackoffMs
        );

        $retryAfterHeader = null;
        if ($value instanceof AwsException) {
            $response = $value->getResponse();
            $retryAfterHeader = $response?->getHeaderLine(
                self::RETRY_AFTER_HEADER
            ) ?? null;
        } elseif ($value instanceof ResultInterface) {
            $retryAfterHeader = $value['@metadata']['headers'][
                self::RETRY_AFTER_HEADER
            ] ?? null;
        }

        if (ctype_digit((string) $retryAfterHeader)) {
            $retryAfterMs = (int) $retryAfterHeader;
            $retryAfterMs = max($retryAfterMs, $delayMs);
            $retryAfterMs = min($retryAfterMs, 5000 + $delayMs);
            $delayMs = $retryAfterMs;
        }

        return (int) $delayMs;
    }

    private static function isRetryable(
        mixed $result,
        array $options = []
    ): bool
    {
        $errorCodes = self::$standardThrottlingErrors + self::$standardTransientErrors;
        if (!empty($options['transient_error_codes'])
            && is_array($options['transient_error_codes'])
        ) {
            foreach ($options['transient_error_codes'] as $code) {
                $errorCodes[$code] = true;
            }
        }
        if (!empty($options['throttling_error_codes'])
            && is_array($options['throttling_error_codes'])
        ) {
            foreach ($options['throttling_error_codes'] as $code) {
                $errorCodes[$code] = true;
            }
        }

        $statusCodes = self::$standardTransientStatusCodes;
        if (!empty($options['status_codes'])
            && is_array($options['status_codes'])
        ) {
            foreach ($options['status_codes'] as $code) {
                $statusCodes[$code] = true;
            }
        }

        $isError = $result instanceof \Throwable;

        if (!$isError) {
            if (!isset($result['@metadata']['statusCode'])) {
                return false;
            }
            return isset($statusCodes[$result['@metadata']['statusCode']]);
        }

        if (!($result instanceof AwsException)) {
            return false;
        }

        if ($result->isConnectionError()) {
            return true;
        }

        $awsCode = $result->getAwsErrorCode();
        if ($awsCode !== null && isset($errorCodes[$awsCode])) {
            return true;
        }

        $status = $result->getStatusCode();
        if ($status !== null && isset($statusCodes[$status])) {
            return true;
        }

        if (!empty($errorShape = $result->getAwsErrorShape())) {
            $definition = $errorShape->toArray();
            if (!empty($definition['retryable'])) {
                return true;
            }
        }

        return false;
    }

    private function isThrottlingError(mixed $result): bool
    {
        if ($result instanceof AwsException) {
            $throttlingErrors = self::$standardThrottlingErrors;
            if (!empty($this->options['throttling_error_codes'])
                && is_array($this->options['throttling_error_codes'])
            ) {
                foreach ($this->options['throttling_error_codes'] as $code) {
                    $throttlingErrors[$code] = true;
                }
            }
            if (!empty($result->getAwsErrorCode())
                && !empty($throttlingErrors[$result->getAwsErrorCode()])
            ) {
                return true;
            }

            if (!empty($errorShape = $result->getAwsErrorShape())) {
                $definition = $errorShape->toArray();
                if (!empty($definition['retryable']['throttling'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveMaxAttempts(CommandInterface $cmd): int
    {
        return ($cmd['@retries'] !== null)
            ? $cmd['@retries'] + 1
            : $this->maxAttempts;
    }
}
