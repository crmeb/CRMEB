<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\Credentials\Credential;

use AlibabaCloud\Tea\Model;

class CredentialModel extends Model
{
    public function validate()
    {
    }
    public function toMap()
    {
        $res = [];
        if (null !== $this->accessKeyId) {
            $res['accessKeyId'] = $this->accessKeyId;
        }
        if (null !== $this->accessKeySecret) {
            $res['accessKeySecret'] = $this->accessKeySecret;
        }
        if (null !== $this->securityToken) {
            $res['securityToken'] = $this->securityToken;
        }
        if (null !== $this->bearerToken) {
            $res['bearerToken'] = $this->bearerToken;
        }
        if (null !== $this->type) {
            $res['type'] = $this->type;
        }
        if (null !== $this->providerName) {
            $res['providerName'] = $this->providerName;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return CredentialModel
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['accessKeyId'])) {
            $model->accessKeyId = $map['accessKeyId'];
        }
        if (isset($map['accessKeySecret'])) {
            $model->accessKeySecret = $map['accessKeySecret'];
        }
        if (isset($map['securityToken'])) {
            $model->securityToken = $map['securityToken'];
        }
        if (isset($map['bearerToken'])) {
            $model->bearerToken = $map['bearerToken'];
        }
        if (isset($map['type'])) {
            $model->type = $map['type'];
        }
        if(isset($map['providerName'])){
            $model->providerName = $map['providerName'];
        }
        return $model;
    }
    /**
     * @description accesskey id
     * @var string
     */
    public $accessKeyId;

    /**
     * @description accesskey secret
     * @var string
     */
    public $accessKeySecret;

    /**
     * @description security token
     * @var string
     */
    public $securityToken;

    /**
     * @description bearer token
     * @var string
     */
    public $bearerToken;

    /**
     * @description type
     * @example access_key
     * @var string
     */
    public $type;

    /**
     * @description provider name
     * @example cli_profile/static_ak
     * @var string
     */
    public $providerName;

    /**
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }

    /**
     * @return string
     */
    public function getAccessKeySecret()
    {
        return $this->accessKeySecret;
    }

    /**
     * @return string
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * @return string
     */
    public function getBearerToken()
    {
        return $this->bearerToken;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getProviderName()
    {
        return $this->providerName;
    }

}
