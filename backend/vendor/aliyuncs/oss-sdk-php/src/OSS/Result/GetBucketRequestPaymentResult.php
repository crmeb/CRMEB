<?php

namespace OSS\Result;


use OSS\Model\RequestPaymentConfig;

/**
 * Class GetBucketRequestPaymentResult
 * @package OSS\Result
 */
class GetBucketRequestPaymentResult extends Result
{
    /**
     *  Parse the RequestPaymentConfig object from the response
     *
     * @return RequestPaymentConfig
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $config = new RequestPaymentConfig();
        $config->parseFromXml($content);
        return $config->getPayer();
    }
}
