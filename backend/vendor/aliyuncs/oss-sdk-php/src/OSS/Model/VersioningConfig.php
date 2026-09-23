<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class VersioningConfig
 * @package OSS\Model
 *
 */
class VersioningConfig implements XmlConfig
{
    /**
     * VersioningConfig constructor.
     * @param null $status
     */
    public function __construct($status = null)
    {
        $this->status = $status;
    }

    /**
     * Parse VersioningConfig from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (isset($xml->Status)) {
            $this->status = strval($xml->Status);
        }
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><VersioningConfiguration></VersioningConfiguration>');
        if (isset($this->status)) {
            $xml->addChild('Status', $this->status);
        }
        return $xml->asXML();
    }

    public function __toString()
    {
        return $this->serializeToXml();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    private $status = "";
}