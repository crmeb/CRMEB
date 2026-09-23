<?php

namespace OSS\Result;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\Model\ObjectInfo;
use OSS\Model\ObjectListInfo;
use OSS\Model\Owner;
use OSS\Model\PrefixInfo;

/**
 * Class ListObjectsResult
 * @package OSS\Result
 */
class ListObjectsResult extends Result
{
    /**
     * Parse the xml data returned by the ListObjects interface
     *
     * @return ObjectListInfo
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $xml = new \SimpleXMLElement($this->rawResponse->body);
        $encodingType = isset($xml->EncodingType) ? strval($xml->EncodingType) : "";
        $objectList = $this->parseObjectList($xml, $encodingType);
        $prefixList = $this->parsePrefixList($xml, $encodingType);
        $bucketName = isset($xml->Name) ? strval($xml->Name) : "";
        $prefix = isset($xml->Prefix) ? strval($xml->Prefix) : "";
        $prefix = OssUtil::decodeKey($prefix, $encodingType);
        $marker = isset($xml->Marker) ? strval($xml->Marker) : "";
        $marker = OssUtil::decodeKey($marker, $encodingType);
        $maxKeys = isset($xml->MaxKeys) ? intval($xml->MaxKeys) : 0;
        $delimiter = isset($xml->Delimiter) ? strval($xml->Delimiter) : "";
        $delimiter = OssUtil::decodeKey($delimiter, $encodingType);
        $isTruncated = isset($xml->IsTruncated) ? strval($xml->IsTruncated) : "";
        $nextMarker = isset($xml->NextMarker) ? strval($xml->NextMarker) : "";
        $nextMarker = OssUtil::decodeKey($nextMarker, $encodingType);
        return new ObjectListInfo($bucketName, $prefix, $marker, $nextMarker, $maxKeys, $delimiter, $isTruncated, $objectList, $prefixList);
    }

    private function parseObjectList($xml, $encodingType)
    {
        $retList = array();
        if (isset($xml->Contents)) {
            foreach ($xml->Contents as $content) {
                $key = isset($content->Key) ? strval($content->Key) : "";
                $key = OssUtil::decodeKey($key, $encodingType);
                $lastModified = isset($content->LastModified) ? strval($content->LastModified) : "";
                $eTag = isset($content->ETag) ? strval($content->ETag) : "";
                $type = isset($content->Type) ? strval($content->Type) : "";
                $size = isset($content->Size) ? strval($content->Size) : "0";
                $storageClass = isset($content->StorageClass) ? strval($content->StorageClass) : "";
                if(isset($content->Owner)){
                    $owner = new Owner(strval($content->Owner->ID),strval($content->Owner->DisplayName));
                }else{
                    $owner = null;
                }
                $restoreInfo= isset($content->RestoreInfo) ? strval($content->RestoreInfo) : null;
                $retList[] = new ObjectInfo($key, $lastModified, $eTag, $type, $size, $storageClass,$owner,$restoreInfo);
            }
        }
        return $retList;
    }

    private function parsePrefixList($xml, $encodingType)
    {
        $retList = array();
        if (isset($xml->CommonPrefixes)) {
            foreach ($xml->CommonPrefixes as $commonPrefix) {
                $prefix = isset($commonPrefix->Prefix) ? strval($commonPrefix->Prefix) : "";
                $prefix = OssUtil::decodeKey($prefix, $encodingType);
                $retList[] = new PrefixInfo($prefix);
            }
        }
        return $retList;
    }
}