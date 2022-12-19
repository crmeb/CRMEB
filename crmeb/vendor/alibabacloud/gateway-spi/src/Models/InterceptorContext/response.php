<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi\Models\InterceptorContext;

use AlibabaCloud\Tea\Model;

class response extends Model {
    public function validate() {}
    public function toMap() {
        $res = [];
        if (null !== $this->statusCode) {
            $res['statusCode'] = $this->statusCode;
        }
        if (null !== $this->headers) {
            $res['headers'] = $this->headers;
        }
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }
        if (null !== $this->deserializedBody) {
            $res['deserializedBody'] = $this->deserializedBody;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return response
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['statusCode'])){
            $model->statusCode = $map['statusCode'];
        }
        if(isset($map['headers'])){
            $model->headers = $map['headers'];
        }
        if(isset($map['body'])){
            $model->body = $map['body'];
        }
        if(isset($map['deserializedBody'])){
            $model->deserializedBody = $map['deserializedBody'];
        }
        return $model;
    }
    public $statusCode;

    public $headers;

    public $body;

    public $deserializedBody;

}
