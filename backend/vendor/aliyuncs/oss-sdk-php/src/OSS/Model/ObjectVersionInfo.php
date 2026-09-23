<?php

namespace OSS\Model;

/**
 *
 * Class ObjectVersionInfo
 *
 * The element type of ObjectVersionListInfo, which is the return value type of listObjectVersions
 *
 * The return value of listObjectVersions includes three arrays
 * One is the returned ObjectVersionListInfo, which is similar to a file list in a file system.
 * The other is the returned prefix list, which is similar to a folder list in a file system.
 *
 * @package OSS\Model
 */
class ObjectVersionInfo
{
    /**
     * ObjectVersionInfo constructor.
     *
     * @param string $key
     * @param string $lastModified
     * @param string $eTag
     * @param string $type
     * @param string $size
     * @param string $storageClass
     * @param string $isLatest
     */
    public function __construct($key, $versionId, $lastModified, $eTag, $type, $size, $storageClass, $isLatest)
    {
        $this->key = $key;
        $this->versionId = $versionId;
        $this->lastModified = $lastModified;
        $this->eTag = $eTag;
        $this->type = $type;
        $this->size = $size;
        $this->storageClass = $storageClass;
        $this->isLatest = $isLatest;
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
    public function getVersionId()
    {
        return $this->versionId;
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
    public function getIsLatest()
    {
        return $this->isLatest;
    }

    private $key = "";
    private $versionId = "";
    private $lastModified = "";
    private $eTag = "";
    private $type = "";
    private $size = "0";
    private $storageClass = "";
    private $isLatest = "";
}