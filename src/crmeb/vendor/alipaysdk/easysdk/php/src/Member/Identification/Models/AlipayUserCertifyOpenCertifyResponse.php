<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Member\Identification\Models;

use AlibabaCloud\Tea\Model;

class AlipayUserCertifyOpenCertifyResponse extends Model {
    protected $_name = [
        'body' => 'body',
    ];
    public function validate() {
        Model::validateRequired('body', $this->body, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return AlipayUserCertifyOpenCertifyResponse
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['body'])){
            $model->body = $map['body'];
        }
        return $model;
    }
    /**
     * @description 认证服务请求地址
     * @var string
     */
    public $body;

}
