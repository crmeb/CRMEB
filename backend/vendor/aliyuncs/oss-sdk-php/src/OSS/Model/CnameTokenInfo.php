<?php

namespace OSS\Model;


/**
 * Cname token info class.
 *
 * Class CnameTokenInfo
 * @package OSS\Model
 */
class CnameTokenInfo
{
    /**
     * Get bucket name
     *
     * @return string
     */
    public function getBucket()
    {
        return $this->bucket;
    }

    /**
     * Get cname
     *
     * @return string
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get expireTime.
     *
     * @return string
     */
    public function getExpireTime()
    {
        return $this->expireTime;
    }

    /**
     * Parse cname token from the xml.
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (isset($xml->Bucket) ) {
            $this->bucket = strval($xml->Bucket);
        }
        if (isset($xml->Cname) ) {
            $this->cname = strval($xml->Cname);
        }
        if (isset($xml->Token) ) {
            $this->token = strval($xml->Token);
        }
        if (isset($xml->ExpireTime) ) {
            $this->expireTime = strval($xml->ExpireTime);
        }
    }
    
    /**
     * bucket name
     *
     * @var string
     */
    private $bucket;
    /**
     * cname
     *
     * @var string
     */
    private $cname;

    /**
     * token
     *
     * @var string
     */
    private $token;

    /**
     * expire time
     *
     * @var string
     */
    private $expireTime;

}