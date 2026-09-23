<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Member\Identification\Models;

use AlibabaCloud\Tea\Model;

class MerchantConfig extends Model {
    protected $_name = [
        'returnUrl' => 'return_url',
    ];
    public function validate() {
        Model::validateRequired('returnUrl', $this->returnUrl, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->returnUrl) {
            $res['return_url'] = $this->returnUrl;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return MerchantConfig
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['return_url'])){
            $model->returnUrl = $map['return_url'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $returnUrl;

}
