<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\FaceToFace\Models;

use AlibabaCloud\Tea\Model;

class TradeFundBill extends Model {
    protected $_name = [
        'fundChannel' => 'fund_channel',
        'bankCode' => 'bank_code',
        'amount' => 'amount',
        'realAmount' => 'real_amount',
    ];
    public function validate() {
        Model::validateRequired('fundChannel', $this->fundChannel, true);
        Model::validateRequired('bankCode', $this->bankCode, true);
        Model::validateRequired('amount', $this->amount, true);
        Model::validateRequired('realAmount', $this->realAmount, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->fundChannel) {
            $res['fund_channel'] = $this->fundChannel;
        }
        if (null !== $this->bankCode) {
            $res['bank_code'] = $this->bankCode;
        }
        if (null !== $this->amount) {
            $res['amount'] = $this->amount;
        }
        if (null !== $this->realAmount) {
            $res['real_amount'] = $this->realAmount;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return TradeFundBill
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['fund_channel'])){
            $model->fundChannel = $map['fund_channel'];
        }
        if(isset($map['bank_code'])){
            $model->bankCode = $map['bank_code'];
        }
        if(isset($map['amount'])){
            $model->amount = $map['amount'];
        }
        if(isset($map['real_amount'])){
            $model->realAmount = $map['real_amount'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $fundChannel;

    /**
     * @var string
     */
    public $bankCode;

    /**
     * @var string
     */
    public $amount;

    /**
     * @var string
     */
    public $realAmount;

}
