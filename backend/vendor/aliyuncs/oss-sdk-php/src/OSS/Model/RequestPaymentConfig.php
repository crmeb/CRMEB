<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class RequestPaymentConfig
 * @package OSS\Model
 *
 * @link https://help.aliyun.com/document_detail/117914.htm
 */
class RequestPaymentConfig implements XmlConfig
{
    /**
     * RequestPaymentConfig constructor.
     * @param null $payer
     */
    public function __construct($payer = null)
    {
        $this->payer = $payer;
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
        if (isset($xml->Payer)) {
            $this->payer = strval($xml->Payer);
        }
    }

    /**
     * Serialize the object into xml string.
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><RequestPaymentConfiguration></RequestPaymentConfiguration>');
        if (isset($this->payer)) {
            $xml->addChild('Payer', $this->payer);
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
    public function getPayer()
    {
        return $this->payer;
    }

    private $payer = "";
}