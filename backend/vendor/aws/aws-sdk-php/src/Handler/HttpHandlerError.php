<?php
namespace Aws\Handler;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\NetworkException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ResponseException;
use GuzzleHttp\Exception\ResponseTransferException;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
final class HttpHandlerError
{
    private const CURLE_RECV_ERROR = 56;

    public static function isConnectionError(\Throwable $exception): bool
    {
        // Guzzle 8: transfer failures have dedicated exception classes.
        if ($exception instanceof NetworkException || $exception instanceof ResponseTransferException) {
            return true;
        }

        // Guzzle 7: connection establishment failures use ConnectException.
        if ($exception instanceof ConnectException) {
            return true;
        }

        // Guzzle 7: mid-response receive failures identifiable by cURL handler context.
        if ($exception instanceof RequestException && is_callable([$exception, 'getHandlerContext'])
        ) {
            $context = $exception->getHandlerContext();

            return !empty($context['errno']) && $context['errno'] === self::CURLE_RECV_ERROR;
        }

        return false;
    }

    public static function getResponse(\Throwable $exception): ?ResponseInterface
    {
        // Guzzle 8: response-aware failures expose the response through ResponseException.
        if ($exception instanceof ResponseException) {
            return $exception->getResponse();
        }

        // Guzzle 7: RequestException directly carried an optional response.
        if ($exception instanceof RequestException && is_callable([$exception, 'getResponse'])
        ) {
            return $exception->getResponse();
        }

        return null;
    }
}
