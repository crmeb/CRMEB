<?php

namespace AlibabaCloud\Credentials\Signature;

/**
 * Interface SignatureInterface
 *
 * @package AlibabaCloud\Credentials\Signature
 */
interface SignatureInterface
{
    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @param string $string
     * @param string $accessKeySecret
     *
     * @return string
     */
    public function sign($string, $accessKeySecret);

    /**
     * @return string
     */
    public function getType();
}
