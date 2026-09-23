<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class WormConfig
 * @package OSS\Model
 *
 */
class WormConfig implements XmlConfig
{
    /**
     * Parse WormConfig from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (isset($xml->WormId)) {
            $this->wormId = strval($xml->WormId);
        }
        if (isset($xml->State)) {
            $this->state = strval($xml->State);
        }
        if (isset($xml->RetentionPeriodInDays)) {
            $this->day = intval($xml->RetentionPeriodInDays);
        }
        if (isset($xml->CreationDate)) {
            $this->creationDate = strval($xml->CreationDate);
        }
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        throw new OssException("Not implemented.");
    }

    public function __toString()
    {
        return $this->serializeToXml();
    }

    /**
     * @return string
     */
    public function getWormId()
    {
        return $this->wormId;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    private $wormId = '';
    private $state = '';
    private $creationDate = '';
    private $day = 0;
}