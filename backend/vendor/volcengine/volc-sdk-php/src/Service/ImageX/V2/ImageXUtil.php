<?php

namespace Volc\Service\ImageX\V2;

use GuzzleHttp\RetryMiddleware;

class ImageXUtil
{
    static public function retryDecider(): \Closure
    {
        return function ($retries, $request, $response = null, $exception = null) {
            return $retries < 3;
        };
    }

    static public function retryDelay(): \Closure
    {
        return function ($num) {
            return RetryMiddleware::exponentialDelay($num);
        };
    }
}