<?php

namespace OSS\Model;

/**
 *
 * Class DeleteMarkerInfo
 *
 * @package OSS\Model
 */
class DeleteMarkerInfo
{
    /**
     * DeleteMarkerInfo constructor.
     *
     * @param string $key
     * @param string $versionId
     * @param string $lastModified
     * @param string $isLatest
     */
    public function __construct($key, $versionId, $lastModified, $isLatest)
    {
        $this->key = $key;
        $this->versionId = $versionId;
        $this->lastModified = $lastModified;
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
    public function getIsLatest()
    {
        return $this->isLatest;
    }

    private $key = "";
    private $versionId = "";
    private $lastModified = "";
    private $isLatest = "";
}