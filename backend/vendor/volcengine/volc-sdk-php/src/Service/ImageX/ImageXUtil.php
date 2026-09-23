<?php

namespace Volc\Service\ImageX;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RetryMiddleware;

class ImageXUtil
{
    static public function retryDecider(): \Closure
    {
        return function ($retries,
                         $request,
                         $response = null,
                         $exception = null) {
            return $retries < 3;
        };
    }

    static public function retryDelay(): \Closure
    {
        return function ($num) {
            return RetryMiddleware::exponentialDelay($num);
        };
    }

    static public function escapePath($originalPath): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $originalPath)));
    }
}