<?php

namespace OSS\Model;

/**
 * Class ObjectListInfoV2
 *
 * The class of return value of ListObjectsV2
 *
 * @package OSS\Model
 */
class ObjectListInfoV2
{
    /**
     * ObjectListInfoV2 constructor.
     *
     * @param string $bucketName
     * @param string $prefix
     * @param int $maxKeys
     * @param string $delimiter
     * @param null $isTruncated
     * @param array $objectList
     * @param array $prefixList
     * @param string $continuationToken
     * @param string $nextContinuationToken
     * @param string $startAfter
     * @param int $keyCount
     */
    public function __construct($bucketName, $prefix, $maxKeys, $delimiter, $isTruncated, array $objectList, array $prefixList, $continuationToken, $nextContinuationToken, $startAfter, $keyCount)
    {
        $this->bucketName = $bucketName;
        $this->prefix = $prefix;
        $this->maxKeys = $maxKeys;
        $this->delimiter = $delimiter;
        $this->isTruncated = $isTruncated;
        $this->objectList = $objectList;
        $this->prefixList = $prefixList;
        $this->continuationToken = $continuationToken;
        $this->nextContinuationToken = $nextContinuationToken;
        $this->startAfter = $startAfter;
        $this->keyCount = $keyCount;
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
     * Get the ObjectInfo list.
     *
     * @return ObjectInfo[]
     */
    public function getObjectList()
    {
        return $this->objectList;
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

    /**
     * @return string
     */
    public function getContinuationToken()
    {
        return $this->continuationToken;
    }
    
    /**
     * @return string
     */
    public function getNextContinuationToken()
    {
        return $this->nextContinuationToken;
    }
    
    /**
     * @return string
     */
    public function getStartAfter()
    {
        return $this->startAfter;
    }

    /**
     * @return int
     */
    public function getKeyCount()
    {
        return $this->keyCount;
    }

    private $bucketName = "";
    private $prefix = "";
    private $maxKeys = 0;
    private $delimiter = "";
    private $isTruncated = null;
    private $objectList = array();
    private $prefixList = array();
    private $nextContinuationToken = "";
    private $continuationToken = "";
    private $startAfter = "";
    private $keyCount = 0;
}