<?php

namespace OSS\Model;


/**
 * Bucket stat class.
 *
 * Class BucketStat
 * @package OSS\Model
 */
class BucketStat
{
    /**
     * Get storage
     *
     * @return int
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get object count
     *
     * @return int
     */
    public function getObjectCount()
    {
        return $this->objectCount;
    }

    /**
     * Get multipart upload count.
     *
     * @return int
     */
    public function getMultipartUploadCount()
    {
        return $this->multipartUploadCount;
    }

    /**
     * Get live channel count
     *
     * @return int
     */
    public function getLiveChannelCount()
    {
        return $this->liveChannelCount;
    }

    /**
     * Get last modified time
     *
     * @return int
     */
    public function getLastModifiedTime()
    {
        return $this->lastModifiedTime;
    }

    /**
     * Get standard storage
     *
     * @return int
     */
    public function getStandardStorage()
    {
        return $this->standardStorage;
    }

    /**
     * Get standard object count
     *
     * @return int
     */
    public function getStandardObjectCount()
    {
        return $this->standardObjectCount;
    }

    /**
     * Get infrequent access storage
     *
     * @return int
     */
    public function getInfrequentAccessStorage()
    {
        return $this->infrequentAccessStorage;
    }

    /**
     * Get infrequent access real storage
     *
     * @return int
     */
    public function getInfrequentAccessRealStorage()
    {
        return $this->infrequentAccessRealStorage;
    }

    /**
     * Get infrequent access object count
     *
     * @return int
     */
    public function getInfrequentAccessObjectCount()
    {
        return $this->infrequentAccessObjectCount;
    }

    /**
     * Get archive storage
     *
     * @return int
     */
    public function getArchiveStorage()
    {
        return $this->archiveStorage;
    }

    /**
     * Get archive real storage
     *
     * @return int
     */
    public function getArchiveRealStorage()
    {
        return $this->archiveRealStorage;
    }
    /**
     * Get archive object count
     *
     * @return int
     */
    public function getArchiveObjectCount()
    {
        return $this->archiveObjectCount;
    }

    /**
     * Get cold archive storage
     *
     * @return int
     */
    public function getColdArchiveStorage()
    {
        return $this->coldArchiveStorage;
    }

    /**
     * Get cold archive real storage
     *
     * @return int
     */
    public function getColdArchiveRealStorage()
    {
        return $this->coldArchiveRealStorage;
    }

    /**
     * Get cold archive object count
     *
     * @return int
     */
    public function getColdArchiveObjectCount()
    {
        return $this->coldArchiveObjectCount;
    }

    /**
     * Parse stat from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (isset($xml->Storage) ) {
            $this->storage = intval($xml->Storage);
        }
        if (isset($xml->ObjectCount) ) {
            $this->objectCount = intval($xml->ObjectCount);
        }
        if (isset($xml->MultipartUploadCount) ) {
            $this->multipartUploadCount = intval($xml->MultipartUploadCount);
        }
        if (isset($xml->LiveChannelCount) ) {
            $this->liveChannelCount = intval($xml->LiveChannelCount);
        }
        if (isset($xml->LastModifiedTime) ) {
            $this->lastModifiedTime = intval($xml->LastModifiedTime);
        }
        if (isset($xml->StandardStorage) ) {
            $this->standardStorage = intval($xml->StandardStorage);
        }
        if (isset($xml->StandardObjectCount) ) {
            $this->standardObjectCount = intval($xml->StandardObjectCount);
        }
        if (isset($xml->InfrequentAccessStorage) ) {
            $this->infrequentAccessStorage = intval($xml->InfrequentAccessStorage);
        }
        if (isset($xml->InfrequentAccessRealStorage) ) {
            $this->infrequentAccessRealStorage = intval($xml->InfrequentAccessRealStorage);
        }
        if (isset($xml->InfrequentAccessObjectCount) ) {
            $this->infrequentAccessObjectCount = intval($xml->InfrequentAccessObjectCount);
        }
        if (isset($xml->ArchiveStorage) ) {
            $this->archiveStorage = intval($xml->ArchiveStorage);
        }
        if (isset($xml->ArchiveRealStorage) ) {
            $this->archiveRealStorage = intval($xml->ArchiveRealStorage);
        }
        if (isset($xml->ArchiveObjectCount) ) {
            $this->archiveObjectCount = intval($xml->ArchiveObjectCount);
        }
        if (isset($xml->ColdArchiveStorage) ) {
            $this->coldArchiveStorage = intval($xml->ColdArchiveStorage);
        }
        if (isset($xml->ColdArchiveRealStorage) ) {
            $this->coldArchiveRealStorage = intval($xml->ColdArchiveRealStorage);
        }
        if (isset($xml->ColdArchiveObjectCount) ) {
            $this->coldArchiveObjectCount = intval($xml->ColdArchiveObjectCount);
        }
    }
    
    /**
     * current storage
     *
     * @var int
     */
    private $storage;
    /**
     * object count
     *
     * @var int
     */
    private $objectCount;

    /**
     * multipart upload count
     *
     * @var int
     */
    private $multipartUploadCount;

    /**
     * live channel count
     * @var int
     */
    private $liveChannelCount;

    /**
     * last modified time
     * @var int
     */
    private $lastModifiedTime;

    /**
     * standard storage
     * @var int
     */
    private $standardStorage;

    /**
     * standard object count
     * @var int
     */
    private $standardObjectCount;

    /**
     * infrequent access storage
     * @var int
     */
    private $infrequentAccessStorage;

    /**
     * infrequent access real storage
     * @var int
     */
    private $infrequentAccessRealStorage;

    /**
     * infrequent access object Count
     * @var int
     */
    private $infrequentAccessObjectCount;

    /**
     * archive storage
     * @var int
     */
    private $archiveStorage;

    /**
     * archive real storage
     * @var int
     */
    private $archiveRealStorage;

    /**
     * archive object count
     * @var int
     */
    private $archiveObjectCount;

    /**
     * cold archive storage
     * @var int
     */
    private $coldArchiveStorage;

    /**
     * cold archive real storage
     * @var int
     */
    private $coldArchiveRealStorage;

    /**
     * cold archive object count
     * @var int
     */
    private $coldArchiveObjectCount;

}