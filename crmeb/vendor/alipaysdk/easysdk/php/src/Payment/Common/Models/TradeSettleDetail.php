<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\Common\Models;

use AlibabaCloud\Tea\Model;

class TradeSettleDetail extends Model {
    protected $_name = [
        'operationType' => 'operation_type',
        'operationSerial_no' => 'operation_serial_no',
        'operationDt' => 'operation_dt',
        'transOut' => 'trans_out',
        'transIn' => 'trans_in',
        'amount' => 'amount',
    ];
    public function validate() {
        Model::validateRequired('operationType', $this->operationType, true);
        Model::validateRequired('operationSerial_no', $this->operationSerial_no, true);
        Model::validateRequired('operationDt', $this->operationDt, true);
        Model::validateRequired('transOut', $this->transOut, true);
        Model::validateRequired('transIn', $this->transIn, true);
        Model::validateRequired('amount', $this->amount, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->operationType) {
            $res['operation_type'] = $this->operationType;
        }
        if (null !== $this->operationSerial_no) {
            $res['operation_serial_no'] = $this->operationSerial_no;
        }
        if (null !== $this->operationDt) {
            $res['operation_dt'] = $this->operationDt;
        }
        if (null !== $this->transOut) {
            $res['trans_out'] = $this->transOut;
        }
        if (null !== $this->transIn) {
            $res['trans_in'] = $this->transIn;
        }
        if (null !== $this->amount) {
            $res['amount'] = $this->amount;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return TradeSettleDetail
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['operation_type'])){
            $model->operationType = $map['operation_type'];
        }
        if(isset($map['operation_serial_no'])){
            $model->operationSerial_no = $map['operation_serial_no'];
        }
        if(isset($map['operation_dt'])){
            $model->operationDt = $map['operation_dt'];
        }
        if(isset($map['trans_out'])){
            $model->transOut = $map['trans_out'];
        }
        if(isset($map['trans_in'])){
            $model->transIn = $map['trans_in'];
        }
        if(isset($map['amount'])){
            $model->amount = $map['amount'];
        }
        return $model;
    }
    /**
     * @var string
     */
    public $operationType;

    /**
     * @var string
     */
    public $operationSerial_no;

    /**
     * @var string
     */
    public $operationDt;

    /**
     * @var string
     */
    public $transOut;

    /**
     * @var string
     */
    public $transIn;

    /**
     * @var string
     */
    public $amount;

}
