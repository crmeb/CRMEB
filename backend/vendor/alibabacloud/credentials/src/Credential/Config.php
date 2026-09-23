<?php

namespace AlibabaCloud\Credentials\Credential;

use AlibabaCloud\Tea\Model;

class Config extends Model
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
        if (null !== $this->durationSeconds) {
            $res['durationSeconds'] = $this->durationSeconds;
        }
        if (null !== $this->roleArn) {
            $res['roleArn'] = $this->roleArn;
        }
        if (null !== $this->policy) {
            $res['policy'] = $this->policy;
        }
        if (null !== $this->roleSessionExpiration) {
            $res['roleSessionExpiration'] = $this->roleSessionExpiration;
        }
        if (null !== $this->roleSessionName) {
            $res['roleSessionName'] = $this->roleSessionName;
        }
        if (null !== $this->publicKeyId) {
            $res['publicKeyId'] = $this->publicKeyId;
        }
        if (null !== $this->privateKeyFile) {
            $res['privateKeyFile'] = $this->privateKeyFile;
        }
        if (null !== $this->roleName) {
            $res['roleName'] = $this->roleName;
        }
        if (null !== $this->credentialsURI) {
            $res['credentialsURI'] = $this->credentialsURI;
        }
        if (null !== $this->type) {
            $res['type'] = $this->type;
        }
        if (null !== $this->STSEndpoint) {
            $res['STSEndpoint'] = $this->STSEndpoint;
        }
        if (null !== $this->externalId) {
            $res['externalId'] = $this->externalId;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return Config
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
        if (isset($map['durationSeconds'])) {
            $model->durationSeconds = $map['durationSeconds'];
        }
        if (isset($map['roleArn'])) {
            $model->roleArn = $map['roleArn'];
        }
        if (isset($map['policy'])) {
            $model->policy = $map['policy'];
        }
        if (isset($map['roleSessionExpiration'])) {
            $model->roleSessionExpiration = $map['roleSessionExpiration'];
        }
        if (isset($map['roleSessionName'])) {
            $model->roleSessionName = $map['roleSessionName'];
        }
        if (isset($map['publicKeyId'])) {
            $model->publicKeyId = $map['publicKeyId'];
        }
        if (isset($map['privateKeyFile'])) {
            $model->privateKeyFile = $map['privateKeyFile'];
        }
        if (isset($map['roleName'])) {
            $model->roleName = $map['roleName'];
        }
        if (isset($map['credentialsURI'])) {
            $model->credentialsURI = $map['credentialsURI'];
        }
        if (isset($map['type'])) {
            $model->type = $map['type'];
        }
        if (isset($map['STSEndpoint'])) {
            $model->STSEndpoint = $map['STSEndpoint'];
        }
        if (isset($map['externalId'])) {
            $model->externalId = $map['externalId'];
        }
        return $model;
    }
    /**
     * @description credential type
     * @example access_key
     * @var string
     */
    public $type = 'default';

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
     * @description role name
     * @var string
     */
    public $roleName;

    /**
     * @description role arn
     * @var string
     */
    public $roleArn;

    /**
     * @description oidc provider arn
     * @var string
     */
    public $oidcProviderArn;

    /**
     * @description oidc token file path
     * @var string
     */
    public $oidcTokenFilePath;

    /**
     * @description role session expiration
     * @example 3600
     * @var int
     */
    public $roleSessionExpiration;

    /**
     * @description role session name
     * @var string
     */
    public $roleSessionName;

    /**
     * @description role arn policy
     * @var string
     */
    public $policy;

    /**
     * @description external id for ram role arn
     * @var string
     */
    public $externalId;

    /**
     * @description sts endpoint
     * @var string
     */
    public $STSEndpoint;

    public $publicKeyId;

    public $privateKeyFile;

    /**
     * @description read timeout
     * @var int
     */
    public $readTimeout;

    /**
     * @description connection timeout
     * @var int
     */
    public $connectTimeout;

    /**
     * @description disable IMDS v1
     * @var bool
     */
    public $disableIMDSv1;

    /**
     * @description credentials URI
     * @var string
     */
    public $credentialsURI;

    /**
     * @deprecated
     */
    public $metadataTokenDuration;

    /**
     * @deprecated
     */
    public $durationSeconds;

    /**
     * @deprecated
     */
    public $host;

    /**
     * @deprecated
     */
    public $expiration;

    /**
     * @deprecated
     */
    public $certFile = "";

    /**
     * @deprecated
     */
    public $certPassword = "";

    /**
     * @internal
     */
    public $proxy;
}
