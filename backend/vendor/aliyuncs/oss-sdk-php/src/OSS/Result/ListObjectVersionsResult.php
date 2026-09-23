<?php

namespace OSS\Result;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\Model\ObjectVersionInfo;
use OSS\Model\ObjectVersionListInfo;
use OSS\Model\DeleteMarkerInfo;
use OSS\Model\PrefixInfo;

/**
 * Class ListObjectVersionsResult
 * @package OSS\Result
 */
class ListObjectVersionsResult extends Result
{
    /**
     * Parse the xml data returned by the ListObjectVersions interface
     *
     * @return ObjectVersionListInfo
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $xml = simplexml_load_string($this->rawResponse->body);
        $encodingType = isset($xml->EncodingType) ? strval($xml->EncodingType) : "";
        $objectVersionList = $this->parseObjecVersionList($xml, $encodingType);
        $deleteMarkerList = $this->parseDeleteMarkerList($xml, $encodingType);
        $prefixList = $this->parsePrefixList($xml, $encodingType);
        $bucketName = isset($xml->Name) ? strval($xml->Name) : "";
        $prefix = isset($xml->Prefix) ? strval($xml->Prefix) : "";
        $prefix = OssUtil::decodeKey($prefix, $encodingType);
        $keyMarker = isset($xml->KeyMarker) ? strval($xml->KeyMarker) : "";
        $keyMarker = OssUtil::decodeKey($keyMarker, $encodingType);
        $nextKeyMarker = isset($xml->NextKeyMarker) ? strval($xml->NextKeyMarker) : "";
        $nextKeyMarker = OssUtil::decodeKey($nextKeyMarker, $encodingType);
        $versionIdMarker = isset($xml->VersionIdMarker) ? strval($xml->VersionIdMarker) : "";
        $nextVersionIdMarker = isset($xml->NextVersionIdMarker) ? strval($xml->NextVersionIdMarker) : "";
        $maxKeys = isset($xml->MaxKeys) ? intval($xml->MaxKeys) : 0;
        $delimiter = isset($xml->Delimiter) ? strval($xml->Delimiter) : "";
        $delimiter = OssUtil::decodeKey($delimiter, $encodingType);
        $isTruncated = isset($xml->IsTruncated) ? strval($xml->IsTruncated) : "";

        return new ObjectVersionListInfo($bucketName, $prefix, $keyMarker, $nextKeyMarker,
            $versionIdMarker, $nextVersionIdMarker, $maxKeys, $delimiter, $isTruncated,
            $objectVersionList, $deleteMarkerList, $prefixList);
    }

    private function parseObjecVersionList($xml, $encodingType)
    {
        $retList = array();
        if (isset($xml->Version)) {
            foreach ($xml->Version as $content) {
                $key = isset($content->Key) ? strval($content->Key) : "";
                $key = OssUtil::decodeKey($key, $encodingType);
                $versionId = isset($content->VersionId) ? strval($content->VersionId) : "";
                $lastModified = isset($content->LastModified) ? strval($content->LastModified) : "";
                $eTag = isset($content->ETag) ? strval($content->ETag) : "";
                $type = isset($content->Type) ? strval($content->Type) : "";
                $size = isset($content->Size) ? strval($content->Size) : "0";
                $storageClass = isset($content->StorageClass) ? strval($content->StorageClass) : "";
                $isLatest = isset($content->IsLatest) ? strval($content->IsLatest) : "";
                $retList[] = new ObjectVersionInfo($key, $versionId, $lastModified, $eTag, $type, $size, $storageClass, $isLatest);
            }
        }
        return $retList;
    }

    private function parseDeleteMarkerList($xml, $encodingType)
    {
        $retList = array();
        if (isset($xml->DeleteMarker)) {
            foreach ($xml->DeleteMarker as $content) {
                $key = isset($content->Key) ? strval($content->Key) : "";
                $key = OssUtil::decodeKey($key, $encodingType);
                $versionId = isset($content->VersionId) ? strval($content->VersionId) : "";
                $lastModified = isset($content->LastModified) ? strval($content->LastModified) : "";
                $isLatest = isset($content->IsLatest) ? strval($content->IsLatest) : "";
                $retList[] = new DeleteMarkerInfo($key, $versionId, $lastModified, $isLatest);
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