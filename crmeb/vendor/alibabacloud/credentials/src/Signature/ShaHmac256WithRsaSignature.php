<?php

namespace AlibabaCloud\Credentials\Signature;

use Exception;
use InvalidArgumentException;

/**
 * Class ShaHmac256WithRsaSignature
 *
 * @package AlibabaCloud\Credentials\Signature
 */
class ShaHmac256WithRsaSignature implements SignatureInterface
{

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'SHA256withRSA';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'PRIVATEKEY';
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
     * @param string $privateKey
     *
     * @return string
     */
    public function sign($string, $privateKey)
    {
        $binarySignature = '';
        try {
            openssl_sign(
                $string,
                $binarySignature,
                $privateKey,
                \OPENSSL_ALGO_SHA256
            );
        } catch (Exception $exception) {
            throw  new InvalidArgumentException(
                $exception->getMessage()
            );
        }

        return base64_encode($binarySignature);
    }
}
