<?php

namespace AlibabaCloud\Tea;

use AlibabaCloud\Tea\Exception\TeaError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Tea.
 */
class Tea
{
    /**
     * @var array
     */
    private static $config = [];

    public static function config(array $config)
    {
        self::$config = $config;
    }

    /**
     * @throws GuzzleException
     *
     * @return Response
     */
    public static function send(Request $request, array $config = [])
    {
        if (method_exists($request, 'getPsrRequest')) {
            $request = $request->getPsrRequest();
        }

        $config['http_errors'] = false;

        $res = self::client()->send(
            $request,
            $config
        );

        return new Response($res);
    }

    /**
     * @return PromiseInterface
     */
    public static function sendAsync(RequestInterface $request, array $config = [])
    {
        if (method_exists($request, 'getPsrRequest')) {
            $request = $request->getPsrRequest();
        }

        $config['http_errors'] = false;

        return self::client()->sendAsync(
            $request,
            $config
        );
    }

    /**
     * @return Client
     */
    public static function client(array $config = [])
    {
        if (isset(self::$config['handler'])) {
            $stack = self::$config['handler'];
        } else {
            $stack = HandlerStack::create();
            $stack->push(Middleware::mapResponse(static function (ResponseInterface $response) {
                return new Response($response);
            }));
        }

        self::$config['handler'] = $stack;

        if (!isset(self::$config['on_stats'])) {
            self::$config['on_stats'] = function (TransferStats $stats) {
                Response::$info = $stats->getHandlerStats();
            };
        }

        $new_config = Helper::merge([self::$config, $config]);

        return new Client($new_config);
    }

    /**
     * @param string              $method
     * @param string|UriInterface $uri
     * @param array               $options
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public static function request($method, $uri, $options = [])
    {
        return self::client()->request($method, $uri, $options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @throws GuzzleException
     *
     * @return string
     */
    public static function string($method, $uri, $options = [])
    {
        return (string) self::client()->request($method, $uri, $options)
            ->getBody();
    }

    /**
     * @param string              $method
     * @param string|UriInterface $uri
     * @param array               $options
     *
     * @return PromiseInterface
     */
    public static function requestAsync($method, $uri, $options = [])
    {
        return self::client()->requestAsync($method, $uri, $options);
    }

    /**
     * @param string|UriInterface $uri
     * @param array               $options
     *
     * @throws GuzzleException
     *
     * @return null|mixed
     */
    public static function getHeaders($uri, $options = [])
    {
        return self::request('HEAD', $uri, $options)->getHeaders();
    }

    /**
     * @param string|UriInterface $uri
     * @param string              $key
     * @param null|mixed          $default
     *
     * @throws GuzzleException
     *
     * @return null|mixed
     */
    public static function getHeader($uri, $key, $default = null)
    {
        $headers = self::getHeaders($uri);

        return isset($headers[$key][0]) ? $headers[$key][0] : $default;
    }

    /**
     * @param int   $retryTimes
     * @param float $now
     *
     * @return bool
     */
    public static function allowRetry(array $runtime, $retryTimes, $now)
    {
        unset($now);
        if (empty($runtime) || !isset($runtime['maxAttempts'])) {
            return false;
        }
        $maxAttempts = $runtime['maxAttempts'];
        $retry       = empty($maxAttempts) ? 0 : (int) $maxAttempts;

        return $retry >= $retryTimes;
    }

    /**
     * @param int $retryTimes
     *
     * @return int
     */
    public static function getBackoffTime(array $runtime, $retryTimes)
    {
        $backOffTime = 0;
        $policy      = isset($runtime['policy']) ? $runtime['policy'] : '';

        if (empty($policy) || 'no' == $policy) {
            return $backOffTime;
        }

        $period = isset($runtime['period']) ? $runtime['period'] : '';
        if (null !== $period && '' !== $period) {
            $backOffTime = (int) $period;
            if ($backOffTime <= 0) {
                return $retryTimes;
            }
        }

        return $backOffTime;
    }

    public static function sleep($time)
    {
        sleep($time);
    }

    public static function isRetryable($retry, $retryTimes = 0)
    {
        if ($retry instanceof TeaError) {
            return true;
        }
        if (\is_array($retry)) {
            $max = isset($retry['maxAttempts']) ? (int) ($retry['maxAttempts']) : 3;

            return $retryTimes <= $max;
        }

        return false;
    }

    /**
     * @param mixed|Model[] ...$item
     *
     * @return mixed
     */
    public static function merge(...$item)
    {
        $tmp = [];
        $n   = 0;
        foreach ($item as $i) {
            if (\is_object($i)) {
                if ($i instanceof Model) {
                    $i = $i->toMap();
                } else {
                    $i = json_decode(json_encode($i), true);
                }
            }
            if (null === $i) {
                continue;
            }
            if (!\is_array($i)) {
                throw new \InvalidArgumentException($i);
            }
            $tmp[$n++] = $i;
        }

        return \call_user_func_array('array_merge', $tmp);
    }
}
