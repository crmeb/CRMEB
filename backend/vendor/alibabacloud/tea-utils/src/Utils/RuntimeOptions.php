<?php

// This file is auto-generated, don't edit it. Thanks.
namespace AlibabaCloud\Tea\Utils\Utils;

use AlibabaCloud\Tea\Model;

/**
 * The common runtime options model
 */
class RuntimeOptions extends Model {
    protected $_name = [
        'autoretry' => 'autoretry',
        'ignoreSSL' => 'ignoreSSL',
        'key' => 'key',
        'cert' => 'cert',
        'ca' => 'ca',
        'maxAttempts' => 'max_attempts',
        'backoffPolicy' => 'backoff_policy',
        'backoffPeriod' => 'backoff_period',
        'readTimeout' => 'readTimeout',
        'connectTimeout' => 'connectTimeout',
        'httpProxy' => 'httpProxy',
        'httpsProxy' => 'httpsProxy',
        'noProxy' => 'noProxy',
        'maxIdleConns' => 'maxIdleConns',
        'localAddr' => 'localAddr',
        'socks5Proxy' => 'socks5Proxy',
        'socks5NetWork' => 'socks5NetWork',
        'keepAlive' => 'keepAlive',
    ];
    public function validate() {}
    public function toMap() {
        $res = [];
        if (null !== $this->autoretry) {
            $res['autoretry'] = $this->autoretry;
        }
        if (null !== $this->ignoreSSL) {
            $res['ignoreSSL'] = $this->ignoreSSL;
        }
        if (null !== $this->key) {
            $res['key'] = $this->key;
        }
        if (null !== $this->cert) {
            $res['cert'] = $this->cert;
        }
        if (null !== $this->ca) {
            $res['ca'] = $this->ca;
        }
        if (null !== $this->maxAttempts) {
            $res['max_attempts'] = $this->maxAttempts;
        }
        if (null !== $this->backoffPolicy) {
            $res['backoff_policy'] = $this->backoffPolicy;
        }
        if (null !== $this->backoffPeriod) {
            $res['backoff_period'] = $this->backoffPeriod;
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
        if (null !== $this->noProxy) {
            $res['noProxy'] = $this->noProxy;
        }
        if (null !== $this->maxIdleConns) {
            $res['maxIdleConns'] = $this->maxIdleConns;
        }
        if (null !== $this->localAddr) {
            $res['localAddr'] = $this->localAddr;
        }
        if (null !== $this->socks5Proxy) {
            $res['socks5Proxy'] = $this->socks5Proxy;
        }
        if (null !== $this->socks5NetWork) {
            $res['socks5NetWork'] = $this->socks5NetWork;
        }
        if (null !== $this->keepAlive) {
            $res['keepAlive'] = $this->keepAlive;
        }
        if (null !== $this->extendsParameters) {
            $res['extendsParameters'] = null !== $this->extendsParameters ? $this->extendsParameters->toMap() : null;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return RuntimeOptions
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['autoretry'])){
            $model->autoretry = $map['autoretry'];
        }
        if(isset($map['ignoreSSL'])){
            $model->ignoreSSL = $map['ignoreSSL'];
        }
        if(isset($map['key'])){
            $model->key = $map['key'];
        }
        if(isset($map['cert'])){
            $model->cert = $map['cert'];
        }
        if(isset($map['ca'])){
            $model->ca = $map['ca'];
        }
        if(isset($map['max_attempts'])){
            $model->maxAttempts = $map['max_attempts'];
        }
        if(isset($map['backoff_policy'])){
            $model->backoffPolicy = $map['backoff_policy'];
        }
        if(isset($map['backoff_period'])){
            $model->backoffPeriod = $map['backoff_period'];
        }
        if(isset($map['readTimeout'])){
            $model->readTimeout = $map['readTimeout'];
        }
        if(isset($map['connectTimeout'])){
            $model->connectTimeout = $map['connectTimeout'];
        }
        if(isset($map['httpProxy'])){
            $model->httpProxy = $map['httpProxy'];
        }
        if(isset($map['httpsProxy'])){
            $model->httpsProxy = $map['httpsProxy'];
        }
        if(isset($map['noProxy'])){
            $model->noProxy = $map['noProxy'];
        }
        if(isset($map['maxIdleConns'])){
            $model->maxIdleConns = $map['maxIdleConns'];
        }
        if(isset($map['localAddr'])){
            $model->localAddr = $map['localAddr'];
        }
        if(isset($map['socks5Proxy'])){
            $model->socks5Proxy = $map['socks5Proxy'];
        }
        if(isset($map['socks5NetWork'])){
            $model->socks5NetWork = $map['socks5NetWork'];
        }
        if(isset($map['keepAlive'])){
            $model->keepAlive = $map['keepAlive'];
        }
        if(isset($map['extendsParameters'])){
            $model->extendsParameters = ExtendsParameters::fromMap($map['extendsParameters']);
        }
        return $model;
    }
    /**
     * @description whether to try again
     * @var bool
     */
    public $autoretry;

    /**
     * @description ignore SSL validation
     * @var bool
     */
    public $ignoreSSL;

    /**
     * @description privite key for client certificate
     * @var string
     */
    public $key;

    /**
     * @description client certificate
     * @var string
     */
    public $cert;

    /**
     * @description server certificate
     * @var string
     */
    public $ca;

    /**
     * @description maximum number of retries
     * @var int
     */
    public $maxAttempts;

    /**
     * @description backoff policy
     * @var string
     */
    public $backoffPolicy;

    /**
     * @description backoff period
     * @var int
     */
    public $backoffPeriod;

    /**
     * @description read timeout
     * @var int
     */
    public $readTimeout;

    /**
     * @description connect timeout
     * @var int
     */
    public $connectTimeout;

    /**
     * @description http proxy url
     * @var string
     */
    public $httpProxy;

    /**
     * @description https Proxy url
     * @var string
     */
    public $httpsProxy;

    /**
     * @description agent blacklist
     * @var string
     */
    public $noProxy;

    /**
     * @description maximum number of connections
     * @var int
     */
    public $maxIdleConns;

    /**
     * @description local addr
     * @var string
     */
    public $localAddr;

    /**
     * @description SOCKS5 proxy
     * @var string
     */
    public $socks5Proxy;

    /**
     * @description SOCKS5 netWork
     * @var string
     */
    public $socks5NetWork;

    /**
     * @description whether to enable keep-alive
     * @var bool
     */
    public $keepAlive;

    /**
     * @description Extends Parameters
     * @var ExtendsParameters
     */
    public $extendsParameters;

}
