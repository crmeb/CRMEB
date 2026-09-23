<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class ServerSideEncryptionConfig
 * @package OSS\Model
 *
 * @link https://help.aliyun.com/document_detail/117914.htm
 */
class ServerSideEncryptionConfig implements XmlConfig
{
    /**
     * ServerSideEncryptionConfig constructor.
     * @param null $sseAlgorithm
     * @param null $kmsMasterKeyID
     */
    public function __construct($sseAlgorithm = null, $kmsMasterKeyID = null)
    {
        $this->sseAlgorithm = $sseAlgorithm;
        $this->kmsMasterKeyID = $kmsMasterKeyID;
    }

    /**
     * Parse ServerSideEncryptionConfig from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (!isset($xml->ApplyServerSideEncryptionByDefault)) return;
        foreach ($xml->ApplyServerSideEncryptionByDefault as $default) {
            foreach ($default as $key => $value) {
                if ($key === 'SSEAlgorithm') {
                    $this->sseAlgorithm = strval($value);
                } elseif ($key === 'KMSMasterKeyID') {
                    $this->kmsMasterKeyID = strval($value);
                }
            }
            break;
        }
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ServerSideEncryptionRule></ServerSideEncryptionRule>');
        $default = $xml->addChild('ApplyServerSideEncryptionByDefault');
        if (isset($this->sseAlgorithm)) {
            $default->addChild('SSEAlgorithm', $this->sseAlgorithm);
        }
        if (isset($this->kmsMasterKeyID)) {
            $default->addChild('KMSMasterKeyID', $this->kmsMasterKeyID);
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
    public function getSSEAlgorithm()
    {
        return $this->sseAlgorithm;
    }

    /**
     * @return string
     */
    public function getKMSMasterKeyID()
    {
        return $this->kmsMasterKeyID;
    }

    private $sseAlgorithm = "";
    private $kmsMasterKeyID = "";
}