<?php

namespace OSS\Result;

use OSS\Core\OssException;
use OSS\Model\BucketInfo;

/**
 * Class GetBucketResult interface returns the result class, encapsulated
 * The returned xml data is parsed
 *
 * @package OSS\Result
 */
class GetBucketInfoResult extends Result
{
    /**
     * Parse data from response
     * 
     * @return BucketInfo
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        if (empty($content)) {
            throw new OssException("body is null");
        }
        $xml = simplexml_load_string($content);
        if (isset($xml->Bucket)) {
            $info = new BucketInfo();
            $info->parseFromXmlNode($xml->Bucket);
            return $info;
        } else {
            throw new OssException("xml format exception");
        }
    }
}