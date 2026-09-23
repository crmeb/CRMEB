<?php

namespace AlibabaCloud\Credentials\Utils;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait MockTrait
 *
 * @package AlibabaCloud\Credentials\Utils
 */
trait MockTrait
{
    /**
     * @var array
     */
    private static $mockQueue = [];

    /**
     * @var array
     */
    private static $history = [];

    /**
     * @var MockHandler
     */
    private static $mock;

    /**
     * @param integer             $status
     * @param array               $headers
     * @param array|string|object $body
     */
    public static function mockResponse($status = 200, array $headers = [], $body = null)
    {
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body);
        }

        self::$mockQueue[] = new Response($status, $headers, $body);
        self::createHandlerStack();
    }

    private static function createHandlerStack()
    {
        self::$mock = new MockHandler(self::$mockQueue);
    }

    /**
     * @return MockHandler
     */
    public static function getHandlerHistory()
    {
         return Middleware::history(self::$history);
    }

    /**
     * @param string                 $message
     * @param RequestInterface       $request
     * @param ResponseInterface|null $response
     * @param Exception|null         $previous
     * @param array                  $handlerContext
     */
    public static function mockRequestException(
        $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        Exception $previous = null,
        array $handlerContext = []
    ) {
        self::$mockQueue[] = new RequestException(
            $message,
            $request,
            $response,
            $previous,
            $handlerContext
        );

        self::createHandlerStack();
    }

    /**
     * @return void
     */
    public static function cancelMock()
    {
        self::$mockQueue = [];
        self::$mock      = null;
    }

    /**
     * @return bool
     */
    public static function hasMock()
    {
        return (bool)self::$mockQueue;
    }

    /**
     * @return MockHandler
     */
    public static function getMock()
    {
        return self::$mock;
    }

    /**
     * @return array
     */
    public static function getHistroy()
    {
        return self::$history;
    }
}
