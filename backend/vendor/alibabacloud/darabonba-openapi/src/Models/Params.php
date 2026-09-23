<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\OpenApi\Models;

use AlibabaCloud\Tea\Model;

class Params extends Model
{
    public function validate()
    {
        Model::validateRequired('action', $this->action, true);
        Model::validateRequired('version', $this->version, true);
        Model::validateRequired('protocol', $this->protocol, true);
        Model::validateRequired('pathname', $this->pathname, true);
        Model::validateRequired('method', $this->method, true);
        Model::validateRequired('authType', $this->authType, true);
        Model::validateRequired('bodyType', $this->bodyType, true);
        Model::validateRequired('reqBodyType', $this->reqBodyType, true);
    }
    public function toMap()
    {
        $res = [];
        if (null !== $this->action) {
            $res['action'] = $this->action;
        }
        if (null !== $this->version) {
            $res['version'] = $this->version;
        }
        if (null !== $this->protocol) {
            $res['protocol'] = $this->protocol;
        }
        if (null !== $this->pathname) {
            $res['pathname'] = $this->pathname;
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
        return $res;
    }
    /**
     * @param array $map
     * @return Params
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['action'])) {
            $model->action = $map['action'];
        }
        if (isset($map['version'])) {
            $model->version = $map['version'];
        }
        if (isset($map['protocol'])) {
            $model->protocol = $map['protocol'];
        }
        if (isset($map['pathname'])) {
            $model->pathname = $map['pathname'];
        }
        if (isset($map['method'])) {
            $model->method = $map['method'];
        }
        if (isset($map['authType'])) {
            $model->authType = $map['authType'];
        }
        if (isset($map['bodyType'])) {
            $model->bodyType = $map['bodyType'];
        }
        if (isset($map['reqBodyType'])) {
            $model->reqBodyType = $map['reqBodyType'];
        }
        if (isset($map['style'])) {
            $model->style = $map['style'];
        }
        return $model;
    }
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
    public $pathname;

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
}
