<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class RestoreConfig
 * @package OSS\Model
 *
 */
class RestoreConfig implements XmlConfig
{
    /**
     * RestoreConfig constructor.
     * @param int $day
     * @param null $tier
     */
    public function __construct($day, $tier = null)
    {
        $this->day = $day;
        $this->tier = $tier;
    }

    /**
     * Parse RestoreConfig from the xml.
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
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><RestoreRequest></RestoreRequest>');
        $xml->addChild('Days', strval($this->day));
        if (isset($this->tier)) {
            $xml_param = $xml->addChild('JobParameters');
            $xml_param->addChild('Tier', $this->tier);
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

    /**
     * @return string
     */
    public function getTier()
    {
        return $this->tier;
    }

    private $day = 1;
    private $tier = 'Standard';
}