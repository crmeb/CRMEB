<?php
namespace OSS\Result;

use OSS\Core\OssException;

/**
 * Class GetLocationResult getBucketLocation接口返回结果类，封装了
 * 返回的xml数据的解析
 *
 * @package OSS\Result
 */
class GetLocationResult extends Result
{

    /**
     * Parse data from response
     * 
     * @return string
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        if (empty($content)) {
            throw new OssException("body is null");
        }
        $xml = simplexml_load_string($content);
        return $xml;
    }
}