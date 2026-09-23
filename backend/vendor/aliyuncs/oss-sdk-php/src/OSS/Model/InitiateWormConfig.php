<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class InitiateWormConfig
 * @package OSS\Model
 *
 */
class InitiateWormConfig implements XmlConfig
{
    /**
     * InitiateWormConfig constructor.
     * @param null $day
     */
    public function __construct($day = null)
    {
        $this->day = $day;
    }

    /**
     * Parse InitiateWormConfig from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        throw new OssException("Not implemented.");
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><InitiateWormConfiguration></InitiateWormConfiguration>');
        if (isset($this->day)) {
            $xml->addChild('RetentionPeriodInDays', $this->day);
        }
        return $xml->asXML();
    }

    public function __toString()
    {
        return $this->serializeToXml();
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    private $day = 0;
}