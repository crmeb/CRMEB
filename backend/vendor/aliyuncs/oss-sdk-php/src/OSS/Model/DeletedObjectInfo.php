<?php

namespace OSS\Model;

/**
 * Class DeletedObjectInfo
 * @package OSS\Model
 */
class DeletedObjectInfo
{
    /**
     * DeletedObjectInfo constructor.
     *
     * @param string $key
     * @param string $versionId
     * @param string $deleteMarker
     * @param string $deleteMarkerVersionId
     */
    public function __construct($key, $versionId, $deleteMarker, $deleteMarkerVersionId)
    {
        $this->key = $key;
        $this->versionId = $versionId;
        $this->deleteMarker = $deleteMarker;
        $this->deleteMarkerVersionId = $deleteMarkerVersionId;
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
    public function getDeleteMarker()
    {
        return $this->deleteMarker;
    }

    /**
     * @return string
     */
    public function getDeleteMarkerVersionId()
    {
        return $this->deleteMarkerVersionId;
    }

    private $key = "";
    private $versionId = "";
    private $deleteMarker = "";
    private $deleteMarkerVersionId = "";
}