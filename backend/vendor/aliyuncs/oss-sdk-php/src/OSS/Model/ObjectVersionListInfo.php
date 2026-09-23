<?php

namespace OSS\Model;

/**
 * Class ObjectVersionListInfo
 *
 * The class of return value of ListObjectVersions
 *
 * @package OSS\Model
 */
class ObjectVersionListInfo
{
    /**
     * ObjectVersionListInfo constructor.
     *
     * @param string $bucketName
     * @param string $prefix
     * @param string $keyMarker
     * @param string $nextKeyMarker
     * @param string $versionIdMarker
     * @param string $nextVersionIdMarker
     * @param string $maxKeys
     * @param string $delimiter
     * @param null|string $isTruncated
     * @param array $objectversionList
     * @param array $deleteMarkerList
     * @param array $prefixList
     */
    public function __construct($bucketName, $prefix, $keyMarker, $nextKeyMarker, $versionIdMarker, $nextVersionIdMarker
        , $maxKeys, $delimiter, $isTruncated
        , array $objectversionList, array $deleteMarkerList, array $prefixList)
    {
        $this->bucketName = $bucketName;
        $this->prefix = $prefix;
        $this->keyMarker = $keyMarker;
        $this->nextKeyMarker = $nextKeyMarker;
        $this->versionIdMarker = $versionIdMarker;
        $this->nextVersionIdMarker = $nextVersionIdMarker;
        $this->maxKeys = $maxKeys;
        $this->delimiter = $delimiter;
        $this->isTruncated = $isTruncated;
        $this->objectVersionList = $objectversionList;
        $this->deleteMarkerList = $deleteMarkerList;
        $this->prefixList = $prefixList;
    }

    /**
     * @return string
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getKeyMarker()
    {
        return $this->keyMarker;
    }

    /**
     * @return string
     */
    public function getNextKeyMarker()
    {
        return $this->nextKeyMarker;
    }

    /**
     * @return string
     */
    public function getVersionIdMarker()
    {
        return $this->versionIdMarker;
    }

    /**
     * @return string
     */
    public function getNextVersionIdMarker()
    {
        return $this->nextVersionIdMarker;
    }

    /**
     * @return int
     */
    public function getMaxKeys()
    {
        return $this->maxKeys;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @return mixed
     */
    public function getIsTruncated()
    {
        return $this->isTruncated;
    }

    /**
     * Get the ObjectVersionInfo list.
     *
     * @return ObjectVersionInfo[]
     */
    public function getObjectVersionList()
    {
        return $this->objectVersionList;
    }

    /**
     * Get the DeleteMarkerInfo list.
     *
     * @return DeleteMarkerInfo[]
     */
    public function getDeleteMarkerList()
    {
        return $this->deleteMarkerList;
    }

    /**
     * Get the PrefixInfo list
     *
     * @return PrefixInfo[]
     */
    public function getPrefixList()
    {
        return $this->prefixList;
    }

    private $bucketName = "";
    private $prefix = "";
    private $keyMarker = "";
    private $nextKeyMarker = "";
    private $versionIdMarker = "";
    private $nextVersionIdMarker = "";
    private $maxKeys = 0;
    private $delimiter = "";
    private $isTruncated = null;
    private $objectVersionList = array();
    private $deleteMarkerList = array();
    private $prefixList = array();
}