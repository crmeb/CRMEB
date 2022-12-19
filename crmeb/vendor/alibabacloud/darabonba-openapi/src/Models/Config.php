<?php

// This file is auto-generated, don't edit it. Thanks.

namespace Darabonba\OpenApi\Models;

use AlibabaCloud\Credentials\Credential;
use AlibabaCloud\Tea\Model;

/**
 * Model for initing client.
 */
class Config extends Model
{
    protected $_default = [
        'accessKeyId' => '',
        'accessKeySecret' => '',
        'securityToken' => '',
        'protocol' => 'http',
        'method' => '',
        'regionId' => '',
        'readTimeout' => '',
        'connectTimeout' => '',
        'httpProxy' => '',
        'httpsProxy' => '',
        'credential' => '',
        'endpoint' => '',
        'noProxy' => '',
        'maxIdleConns' => '',
        'network' => '',
        'userAgent' => '',
        'suffix' => '',
        'socks5Proxy' => '',
        'socks5NetWork' => '',
        'endpointType' => '',
        'openPlatformEndpoint' => '',
        'type' => '',
        'signatureVersion' => '',
        'signatureAlgorithm' => '',
    ];

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
        if (null !== $this->protocol) {
            $res['protocol'] = $this->protocol;
        }
        if (null !== $this->method) {
            $res['method'] = $this->method;
        }
        if (null !== $this->regionId) {
            $res['regionId'] = $this->regionId;
        }
        if (null !== $this->readTimeout) {
            $res['readTimeout'] = $this->readTimeout;
        }
        if (null !== $this->connectTimeout) {
            $res['connectTimeout'] = $this->connectTimeout;
        }
        if (null !== $this->httpProxy) {
            $res['httpProxy'] = $this->httpProxy;
        }
        if (null !== $this->httpsProxy) {
            $res['httpsProxy'] = $this->httpsProxy;
        }
        if (null !== $this->credential) {
            $res['credential'] = null !== $this->credential ? $this->credential->toMap() : null;
        }
        if (null !== $this->endpoint) {
            $res['endpoint'] = $this->endpoint;
        }
        if (null !== $this->noProxy) {
            $res['noProxy'] = $this->noProxy;
        }
        if (null !== $this->maxIdleConns) {
            $res['maxIdleConns'] = $this->maxIdleConns;
        }
        if (null !== $this->network) {
            $res['network'] = $this->network;
        }
        if (null !== $this->userAgent) {
            $res['userAgent'] = $this->userAgent;
        }
        if (null !== $this->suffix) {
            $res['suffix'] = $this->suffix;
        }
        if (null !== $this->socks5Proxy) {
            $res['socks5Proxy'] = $this->socks5Proxy;
        }
        if (null !== $this->socks5NetWork) {
            $res['socks5NetWork'] = $this->socks5NetWork;
        }
        if (null !== $this->endpointType) {
            $res['endpointType'] = $this->endpointType;
        }
        if (null !== $this->openPlatformEndpoint) {
            $res['openPlatformEndpoint'] = $this->openPlatformEndpoint;
        }
        if (null !== $this->type) {
            $res['type'] = $this->type;
        }
        if (null !== $this->signatureVersion) {
            $res['signatureVersion'] = $this->signatureVersion;
        }
        if (null !== $this->signatureAlgorithm) {
            $res['signatureAlgorithm'] = $this->signatureAlgorithm;
        }
        if (null !== $this->globalParameters) {
            $res['globalParameters'] = null !== $this->globalParameters ? $this->globalParameters->toMap() : null;
        }

        return $res;
    }

    /**
     * @param array $map
     *
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
        if (isset($map['protocol'])) {
            $model->protocol = $map['protocol'];
        }
        if (isset($map['method'])) {
            $model->method = $map['method'];
        }
        if (isset($map['regionId'])) {
            $model->regionId = $map['regionId'];
        }
        if (isset($map['readTimeout'])) {
            $model->readTimeout = $map['readTimeout'];
        }
        if (isset($map['connectTimeout'])) {
            $model->connectTimeout = $map['connectTimeout'];
        }
        if (isset($map['httpProxy'])) {
            $model->httpProxy = $map['httpProxy'];
        }
        if (isset($map['httpsProxy'])) {
            $model->httpsProxy = $map['httpsProxy'];
        }
        if (isset($map['credential'])) {
            $model->credential = Credential::fromMap($map['credential']);
        }
        if (isset($map['endpoint'])) {
            $model->endpoint = $map['endpoint'];
        }
        if (isset($map['noProxy'])) {
            $model->noProxy = $map['noProxy'];
        }
        if (isset($map['maxIdleConns'])) {
            $model->maxIdleConns = $map['maxIdleConns'];
        }
        if (isset($map['network'])) {
            $model->network = $map['network'];
        }
        if (isset($map['userAgent'])) {
            $model->userAgent = $map['userAgent'];
        }
        if (isset($map['suffix'])) {
            $model->suffix = $map['suffix'];
        }
        if (isset($map['socks5Proxy'])) {
            $model->socks5Proxy = $map['socks5Proxy'];
        }
        if (isset($map['socks5NetWork'])) {
            $model->socks5NetWork = $map['socks5NetWork'];
        }
        if (isset($map['endpointType'])) {
            $model->endpointType = $map['endpointType'];
        }
        if (isset($map['openPlatformEndpoint'])) {
            $model->openPlatformEndpoint = $map['openPlatformEndpoint'];
        }
        if (isset($map['type'])) {
            $model->type = $map['type'];
        }
        if (isset($map['signatureVersion'])) {
            $model->signatureVersion = $map['signatureVersion'];
        }
        if (isset($map['signatureAlgorithm'])) {
            $model->signatureAlgorithm = $map['signatureAlgorithm'];
        }
        if (isset($map['globalParameters'])) {
            $model->globalParameters = GlobalParameters::fromMap($map['globalParameters']);
        }

        return $model;
    }

    /**
     * @description accesskey id
     *
     * @var string
     */
    public $accessKeyId;

    /**
     * @description accesskey secret
     *
     * @var string
     */
    public $accessKeySecret;

    /**
     * @description security token
     *
     * @example a.txt
     *
     * @var string
     */
    public $securityToken;

    /**
     * @description http protocol
     *
     * @example http
     *
     * @var string
     */
    public $protocol;

    /**
     * @description http method
     *
     * @example GET
     *
     * @var string
     */
    public $method;

    /**
     * @description region id
     *
     * @example cn-hangzhou
     *
     * @var string
     */
    public $regionId;

    /**
     * @description read timeout
     *
     * @example 10
     *
     * @var int
     */
    public $readTimeout;

    /**
     * @description connect timeout
     *
     * @example 10
     *
     * @var int
     */
    public $connectTimeout;

    /**
     * @description http proxy
     *
     * @example http://localhost
     *
     * @var string
     */
    public $httpProxy;

    /**
     * @description https proxy
     *
     * @example https://localhost
     *
     * @var string
     */
    public $httpsProxy;

    /**
     * @description credential
     *
     * @example
     *
     * @var Credential
     */
    public $credential;

    /**
     * @description endpoint
     *
     * @example cs.aliyuncs.com
     *
     * @var string
     */
    public $endpoint;

    /**
     * @description proxy white list
     *
     * @example http://localhost
     *
     * @var string
     */
    public $noProxy;

    /**
     * @description max idle conns
     *
     * @example 3
     *
     * @var int
     */
    public $maxIdleConns;

    /**
     * @description network for endpoint
     *
     * @example public
     *
     * @var string
     */
    public $network;

    /**
     * @description user agent
     *
     * @example Alibabacloud/1
     *
     * @var string
     */
    public $userAgent;

    /**
     * @description suffix for endpoint
     *
     * @example aliyun
     *
     * @var string
     */
    public $suffix;

    /**
     * @description socks5 proxy
     *
     * @var string
     */
    public $socks5Proxy;

    /**
     * @description socks5 network
     *
     * @example TCP
     *
     * @var string
     */
    public $socks5NetWork;

    /**
     * @description endpoint type
     *
     * @example internal
     *
     * @var string
     */
    public $endpointType;

    /**
     * @description OpenPlatform endpoint
     *
     * @example openplatform.aliyuncs.com
     *
     * @var string
     */
    public $openPlatformEndpoint;

    /**
     * @description credential type
     *
     * @example access_key
     *
     * @deprecated
     *
     * @var string
     */
    public $type;

    /**
     * @description Signature Version
     *
     * @example v1
     *
     * @var string
     */
    public $signatureVersion;

    /**
     * @description Signature Algorithm
     *
     * @example ACS3-HMAC-SHA256
     *
     * @var string
     */
    public $signatureAlgorithm;

    /**
     * @description Global Parameters
     *
     * @var GlobalParameters
     */
    public $globalParameters;
}
