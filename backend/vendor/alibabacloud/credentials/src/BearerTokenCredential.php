<?php

namespace AlibabaCloud\Credentials;

use AlibabaCloud\Credentials\Utils\Filter;
use AlibabaCloud\Credentials\Credential\CredentialModel;
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
     * @param $bearer_token
     */
    public function __construct($bearer_token)
    {
        Filter::bearerToken($bearer_token);

        $this->bearerToken = $bearer_token;
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

    /**
     * @inheritDoc
     */
    public function getCredential()
    {
        return new CredentialModel([
            'bearerToken' => $this->bearerToken,
            'type' => 'bearer',
        ]);
    }

}
