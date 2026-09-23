<?php

namespace AlibabaCloud\Credentials\Signature;

/**
 * Class ShaHmac256Signature
 *
 * @package AlibabaCloud\Credentials\Signature
 */
class ShaHmac256Signature implements SignatureInterface
{

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'HMAC-SHA256';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @param string $string
     * @param string $accessKeySecret
     *
     * @return string
     */
    public function sign($string, $accessKeySecret)
    {
        return base64_encode(hash_hmac('sha256', $string, $accessKeySecret, true));
    }
}
