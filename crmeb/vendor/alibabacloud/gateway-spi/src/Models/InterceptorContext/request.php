<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi\Models\InterceptorContext;

use AlibabaCloud\Tea\Model;
use AlibabaCloud\Credentials\Credential;

class request extends Model {
    public function validate() {
        Model::validateRequired('pathname', $this->pathname, true);
        Model::validateRequired('productId', $this->productId, true);
        Model::validateRequired('action', $this->action, true);
        Model::validateRequired('version', $this->version, true);
        Model::validateRequired('protocol', $this->protocol, true);
        Model::validateRequired('method', $this->method, true);
        Model::validateRequired('authType', $this->authType, true);
        Model::validateRequired('bodyType', $this->bodyType, true);
        Model::validateRequired('reqBodyType', $this->reqBodyType, true);
        Model::validateRequired('credential', $this->credential, true);
        Model::validateRequired('userAgent', $this->userAgent, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->headers) {
            $res['headers'] = $this->headers;
        }
        if (null !== $this->query) {
            $res['query'] = $this->query;
        }
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }
        if (null !== $this->stream) {
            $res['stream'] = $this->stream;
        }
        if (null !== $this->hostMap) {
            $res['hostMap'] = $this->hostMap;
        }
        if (null !== $this->pathname) {
            $res['pathname'] = $this->pathname;
        }
        if (null !== $this->productId) {
            $res['productId'] = $this->productId;
        }
        if (null !== $this->action) {
            $res['action'] = $this->action;
        }
        if (null !== $this->version) {
            $res['version'] = $this->version;
        }
        if (null !== $this->protocol) {
            $res['protocol'] = $this->protocol;
        }
        if (null !== $this->method) {
            $res['method'] = $this->method;
        }
        if (null !== $this->authType) {
            $res['authType'] = $this->authType;
        }
        if (null !== $this->bodyType) {
            $res['bodyType'] = $this->bodyType;
        }
        if (null !== $this->reqBodyType) {
            $res['reqBodyType'] = $this->reqBodyType;
        }
        if (null !== $this->style) {
            $res['style'] = $this->style;
        }
        if (null !== $this->credential) {
            $res['credential'] = null !== $this->credential ? $this->credential->toMap() : null;
        }
        if (null !== $this->signatureVersion) {
            $res['signatureVersion'] = $this->signatureVersion;
        }
        if (null !== $this->signatureAlgorithm) {
            $res['signatureAlgorithm'] = $this->signatureAlgorithm;
        }
        if (null !== $this->userAgent) {
            $res['userAgent'] = $this->userAgent;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return request
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['headers'])){
            $model->headers = $map['headers'];
        }
        if(isset($map['query'])){
            $model->query = $map['query'];
        }
        if(isset($map['body'])){
            $model->body = $map['body'];
        }
        if(isset($map['stream'])){
            $model->stream = $map['stream'];
        }
        if(isset($map['hostMap'])){
            $model->hostMap = $map['hostMap'];
        }
        if(isset($map['pathname'])){
            $model->pathname = $map['pathname'];
        }
        if(isset($map['productId'])){
            $model->productId = $map['productId'];
        }
        if(isset($map['action'])){
            $model->action = $map['action'];
        }
        if(isset($map['version'])){
            $model->version = $map['version'];
        }
        if(isset($map['protocol'])){
            $model->protocol = $map['protocol'];
        }
        if(isset($map['method'])){
            $model->method = $map['method'];
        }
        if(isset($map['authType'])){
            $model->authType = $map['authType'];
        }
        if(isset($map['bodyType'])){
            $model->bodyType = $map['bodyType'];
        }
        if(isset($map['reqBodyType'])){
            $model->reqBodyType = $map['reqBodyType'];
        }
        if(isset($map['style'])){
            $model->style = $map['style'];
        }
        if(isset($map['credential'])){
            $model->credential = Credential::fromMap($map['credential']);
        }
        if(isset($map['signatureVersion'])){
            $model->signatureVersion = $map['signatureVersion'];
        }
        if(isset($map['signatureAlgorithm'])){
            $model->signatureAlgorithm = $map['signatureAlgorithm'];
        }
        if(isset($map['userAgent'])){
            $model->userAgent = $map['userAgent'];
        }
        return $model;
    }
    public $headers;

    public $query;

    public $body;

    public $stream;

    public $hostMap;

    /**
     * @var string
     */
    public $pathname;

    /**
     * @var string
     */
    public $productId;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $protocol;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $authType;

    /**
     * @var string
     */
    public $bodyType;

    /**
     * @var string
     */
    public $reqBodyType;

    public $style;

    /**
     * @var Credential
     */
    public $credential;

    public $signatureVersion;

    public $signatureAlgorithm;

    /**
     * @var string
     */
    public $userAgent;

}
