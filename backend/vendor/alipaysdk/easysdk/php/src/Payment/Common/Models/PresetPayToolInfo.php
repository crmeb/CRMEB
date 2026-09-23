<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\Common\Models;

use AlibabaCloud\Tea\Model;

class PresetPayToolInfo extends Model {
    protected $_name = [
        'amount' => 'amount',
        'assertTypeCode' => 'assert_type_code',
    ];
    public function validate() {
        Model::validateRequired('amount', $this->amount, true);
        Model::validateRequired('assertTypeCode', $this->assertTypeCode, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->amount) {
            $res['amount'] = $this->amount;
        }
        if (null !== $this->assertTypeCode) {
            $res['assert_type_code'] = $this->assertTypeCode;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return PresetPayToolInfo
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['amount'])){
            if(!empty($map['amount'])){
                $model->amount = $map['amount'];
            }
        }
        if(isset($map['assert_type_code'])){
            $model->assertTypeCode = $map['assert_type_code'];
        }
        return $model;
    }
    /**
     * @var string[]
     */
    public $amount;

    /**
     * @var string
     */
    public $assertTypeCode;

}
