<?php

namespace OSS\Model;
/**
 *
 * Class ObjectInfo
 *
 * The element type of ObjectListInfo, which is the return value type of listObjects
 *
 * The return value of listObjects includes two arrays
 * One is the returned ObjectListInfo, which is similar to a file list in a file system.
 * The other is the returned prefix list, which is similar to a folder list in a file system.
 *
 * @package OSS\Model
 */
class ObjectInfo
{
    /**
     * ObjectInfo constructor.
     *
     * @param string $key
     * @param string $lastModified
     * @param string $eTag
     * @param string $type
     * @param string $size
     * @param string $storageClass
     * @param Owner|null $owner
     * @param null $restoreInfo
     */
    public function __construct($key, $lastModified, $eTag, $type, $size, $storageClass,$owner=null,$restoreInfo=null)
    {
        $this->key = $key;
        $this->lastModified = $lastModified;
        $this->eTag = $eTag;
        $this->type = $type;
        $this->size = $size;
        $this->storageClass = $storageClass;
        $this->owner = $owner;
        $this->restoreInfo = $restoreInfo;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return string
     */
    public function getETag()
    {
        return $this->eTag;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * php7 && 64bit can use it
     * @return int
     */
    public function getSize()
    {
        return (int)$this->size;
    }
    
    
    /**
     * php5.x or 32bit must use it
     * @return string
     */
    public function getSizeStr()
    {
        return $this->size;
    }
    
    /**
     * @return string
     */
    public function getStorageClass()
    {
        return $this->storageClass;
    }

    /**
     * @return string
     */
    public function getRestoreInfo()
    {
        return $this->restoreInfo;
    }


    /**
     * @return Owner|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    private $key = "";
    private $lastModified = "";
    private $eTag = "";
    private $type = "";
    private $size = "0";
    private $storageClass = "";
    /**
     * @var Owner
     */
    private $owner;
    private $restoreInfo;
}