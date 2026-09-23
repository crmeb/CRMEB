<?php

namespace OSS\Result;


use OSS\Model\ServerSideEncryptionConfig;

/**
 * Class GetBucketEncryptionResult
 * @package OSS\Result
 */
class GetBucketEncryptionResult extends Result
{
    /**
     *  Parse the ServerSideEncryptionConfig object from the response
     *
     * @return ServerSideEncryptionConfig
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $config = new ServerSideEncryptionConfig();
        $config->parseFromXml($content);
        return $config;
    }
}
