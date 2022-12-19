<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Signature\BearerTokenSignature;

/**
 * Class BearerTokenCredential
 */
class BearerTokenCredential implements CredentialsInterface
{

    /**
     * @var string
     */
    private $bearerToken;

    /**
     * BearerTokenCredential constructor.
     *
     * @param $bearerToken
     */
    public function __construct($bearerToken)
    {
        Filter::bearerToken($bearerToken);

        $this->bearerToken = $bearerToken;
    }

    /**
     * @return string
     */
    public function getBearerToken()
    {
        return $this->bearerToken;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "bearerToken#$this->bearerToken";
    }

    /**
     * @return BearerTokenSignature
     */
    public function getSignature()
    {
        return new BearerTokenSignature();
    }
}
