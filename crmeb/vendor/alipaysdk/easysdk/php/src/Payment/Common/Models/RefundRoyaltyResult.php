<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\Common\Models;

use AlibabaCloud\Tea\Model;

class RefundRoyaltyResult extends Model {
    protected $_name = [
        'refundAmount' => 'refund_amount',
        'royaltyType' => 'royalty_type',
        'resultCode' => 'result_code',
        'transOut' => 'trans_out',
        'transOutEmail' => 'trans_out_email',
        'transIn' => 'trans_in',
        'transInEmail' => 'trans_in_email',
    ];
    public function validate() {
        Model::validateRequired('refundAmount', $this->refundAmount, true);
        Model::validateRequired('royaltyType', $this->royaltyType, true);
        Model::validateRequired('resultCode', $this->resultCode, true);
        Model::validateRequired('transOut', $this->transOut, true);
        Model::validateRequired('transOutEmail', $this->transOutEmail, true);
        Model::validateRequired('transIn', $this->transIn, true);
        Model::validateRequired('transInEmail', $this->transInEmail, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->refundAmount) {
            $res['refund_amount'] = $this->refundAmount;
        }
        if (null !== $this->royaltyType) {
            $res['royalty_type'] = $this->royaltyType;
        }
        if (null !== $this->resultCode) {
            $res['result_code'] = $this->resultCode;
        }
        if (null !== $this->transOut) {
            $res['trans_out'] = $this->transOut;
        }
        if (null !== $this->transOutEmail) {
            $res['trans_out_email'] = $this->transOutEmail;
        }
        if (null !== $this->transIn) {
            $res['trans_in'] = $this->transIn;
        }
        if (null !== $this->transInEmail) {
            $res['trans_in_email'] = $this->transInEmail;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return RefundRoyaltyResult
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['refund_amount'])){
            $model->refundAmount = $map['refund_amount'];
        }
        if(isset($map['royalty_type'])){
            $model->royaltyType = $map['royalty_type'];
        }
        if(isset($map['result_code'])){
            $model->resultCode = $map['result_code'];
        }
        if(isset($map['trans_out'])){
            $model->transOut = $map['trans_out'];
        }
        if(isset($map['trans_out_email'])){
            $model->transOutEmail = $map['trans_out_email'];
        }
        if(isset($map['trans_in'])){
            $model->transIn = $map['trans_in'];
        }
        if(isset($map['trans_in_email'])){
            $model->transInEmail = $map['trans_in_email'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $refundAmount;

    /**
     * @var string
     */
    public $royaltyType;

    /**
     * @var string
     */
    public $resultCode;

    /**
     * @var string
     */
    public $transOut;

    /**
     * @var string
     */
    public $transOutEmail;

    /**
     * @var string
     */
    public $transIn;

    /**
     * @var string
     */
    public $transInEmail;

}
