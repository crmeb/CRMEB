<?php

namespace OSS\Model;


/**
 * Bucket information class. This is the type of element in BucketListInfo's
 *
 * Class BucketInfo
 * @package OSS\Model
 */
class BucketInfo
{
     /**
     * BucketInfo constructor.
     *
     * @param string $location
     * @param string $name
     * @param string $createDate
     */
    public function __construct($location = '', $name = '', $createDate = '')
    {
        $this->location = $location;
        $this->createDate = $createDate;
        $this->name = $name;
    }
    
    /**
     * Get bucket location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get bucket name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get bucket creation time.
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Get bucket storage class.
     *
     * @return string
     */
    public function getStorageClass()
    {
        return $this->storageClass;
    }

     /**
     * Get bucket extranet endpoint.
     *
     * @return string
     */
    public function getExtranetEndpoint()
    {
        return $this->extranetEndpoint;
    }

     /**
     * Get bucket intranet endpoint.
     *
     * @return string
     */
    public function getIntranetEndpoint()
    {
        return $this->intranetEndpoint;
    }

    /**
     * Get bucket intranet endpoint.
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }
    

    /**
     * Parse bucket information from node.
     *
     * @param xml $xml
     * @throws OssException
     * @return null
     */
    public function parseFromXmlNode($xml)
    {
        if (isset($xml->Location)) {
            $this->location = strval($xml->Location);
        }
        if (isset($xml->Name)) {
            $this->name = strval($xml->Name);
        }
        if (isset($xml->CreationDate)) {
            $this->createDate = strval($xml->CreationDate);
        }
        if (isset($xml->StorageClass)) {
            $this->storageClass = strval($xml->StorageClass);
        }
        if (isset($xml->ExtranetEndpoint)) {
            $this->extranetEndpoint = strval($xml->ExtranetEndpoint);
        }
        if (isset($xml->IntranetEndpoint)) {
            $this->intranetEndpoint = strval($xml->IntranetEndpoint);
        }
        if (isset($xml->IntranetEndpoint)) {
            $this->intranetEndpoint = strval($xml->IntranetEndpoint);
        }
        if (isset($xml->Region)) {
            $this->region = strval($xml->Region);
        }
    }
    
    /**
     * bucket region
     *
     * @var string
     */
    private $location;
    /**
     * bucket name
     *
     * @var string
     */
    private $name;

    /**
     * bucket creation time
     *
     * @var string
     */
    private $createDate;

     /**
     * bucket storage class
     *
     * @var string
     */
    private $storageClass;

    /**
     * bucket extranet endpoint
     *
     * @var string
     */
    private $extranetEndpoint;

    /**
     * bucket intranet endpoint
     *
     * @var string
     */
    private $intranetEndpoint;

    /**
     * bucket region
     *
     * @var string
     */
    private $region;
}