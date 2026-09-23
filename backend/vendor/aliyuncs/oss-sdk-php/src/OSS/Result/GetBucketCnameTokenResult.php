<?php

namespace OSS\Result;

use OSS\Model\CnameTokenInfo;

class GetBucketCnameTokenResult extends Result
{
    /**
     * @return CnameConfig
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $info = new CnameTokenInfo();
        $info->parseFromXml($content);
        return $info;
    }
}