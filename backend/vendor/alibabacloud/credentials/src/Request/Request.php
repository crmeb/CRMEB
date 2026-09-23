<?php

namespace AlibabaCloud\Credentials\Request;

use AlibabaCloud\Credentials\Credentials;
use AlibabaCloud\Credentials\Utils\Helper;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use AlibabaCloud\Tea\Response;
use Psr\Http\Message\ResponseInterface;

use Exception;
use InvalidArgumentException;

/**
 * RESTful RPC Request.
 */
class Request
{

    /**
     * Request Connect Timeout
     */
    const CONNECT_TIMEOUT = 5;

    /**
     * Request Read Timeout
     */
    const READ_TIMEOUT = 5;

    /**
     * @var array
     */
    private static $config = [];


    /**
     *
     * @return array
     */
    public static function commonOptions()
    {
        $options = [];
        $options['http_errors'] = false;
        $options['connect_timeout'] = self::CONNECT_TIMEOUT;
        $options['read_timeout'] = self::READ_TIMEOUT;
        $options['headers']['User-Agent'] = Helper::getUserAgent();

        // Turn on debug mode based on environment variable.
        if (strtolower(Helper::env('DEBUG')) === 'sdk') {
            $options['debug'] = true;
        }
        return $options;
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    public static function uuid($salt)
    {
        return md5($salt . uniqid(md5(microtime(true)), true));
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return string
     */
    public static function signString($method, array $parameters)
    {
        ksort($parameters);
        $canonicalized = '';
        foreach ($parameters as $key => $value) {
            $canonicalized .= '&' . self::percentEncode($key) . '=' . self::percentEncode($value);
        }

        return $method . '&%2F&' . self::percentEncode(substr($canonicalized, 1));
    }

    /**
     * @param string $string
     * @param string $accessKeySecret
     *
     * @return string
     */
    public static function shaHmac1sign($string, $accessKeySecret)
    {
        return base64_encode(hash_hmac('sha1', $string, $accessKeySecret, true));
    }

    /**
     * @param string $string
     * @param string $accessKeySecret
     *
     * @return string
     */
    public static function shaHmac256sign($string, $accessKeySecret)
    {
        return base64_encode(hash_hmac('sha256', $string, $accessKeySecret, true));
    }

    /**
     * @param string $string
     * @param string $privateKey
     *
     * @return string
     */
    public static function shaHmac256WithRsasign($string, $privateKey)
    {
        $binarySignature = '';
        try {
            openssl_sign(
                $string,
                $binarySignature,
                $privateKey,
                \OPENSSL_ALGO_SHA256
            );
        } catch (Exception $exception) {
            throw new InvalidArgumentException(
                $exception->getMessage()
            );
        }

        return base64_encode($binarySignature);
    }

    /**
     * @param string $string
     *
     * @return null|string|string[]
     */
    private static function percentEncode($string)
    {
        $result = rawurlencode($string);
        $result = str_replace(['+', '*'], ['%20', '%2A'], $result);
        $result = preg_replace('/%7E/', '~', $result);

        return $result;
    }

    /**
     * @return Client
     * @throws Exception
     */
    public static function createClient()
    {
        if (Credentials::hasMock()) {
            $stack = HandlerStack::create(Credentials::getMock());
            $history = Credentials::getHandlerHistory();
            $stack->push($history);
        } else {
            $stack = HandlerStack::create();
        }

        $stack->push(Middleware::mapResponse(static function (ResponseInterface $response) {
            return new Response($response);
        }));

        self::$config['handler'] = $stack;

        return new Client(self::$config);
    }
}
