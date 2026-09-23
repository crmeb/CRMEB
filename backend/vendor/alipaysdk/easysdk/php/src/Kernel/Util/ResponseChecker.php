<?php


namespace Alipay\EasySDK\Kernel\Util;


class ResponseChecker
{
    public function success($response)
    {
        if (!empty($response->code) && $response->code == 10000) {
            return true;
        }
        if (empty($response->code) && empty($response->subCode)) {
            return true;
        }
        return false;
    }
}