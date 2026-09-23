<?php

namespace OSS\Result;


use OSS\Model\TaggingConfig;

/**
 * Class GetBucketTagsResult
 * @package OSS\Result
 */
class GetBucketTagsResult extends Result
{
    /**
     *  Parse the TaggingConfig object from the response
     *
     * @return TaggingConfig
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $config = new TaggingConfig();
        $config->parseFromXml($content);
        return $config;
    }
}
