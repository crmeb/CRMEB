<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Util\Generic\Models;

use AlibabaCloud\Tea\Model;

class AlipayOpenApiGenericSDKResponse extends Model {
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
     * @return AlipayOpenApiGenericSDKResponse
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['body'])){
            $model->body = $map['body'];
        }
        return $model;
    }
    /**
     * @description 订单信息，字符串形式
     * @var string
     */
    public $body;

}
