<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Alipay\EasySDK\Payment\FaceToFace\Models;

use AlibabaCloud\Tea\Model;

use Alipay\EasySDK\Payment\FaceToFace\Models\TradeFundBill;
use Alipay\EasySDK\Payment\FaceToFace\Models\VoucherDetail;

class AlipayTradePayResponse extends Model {
    protected $_name = [
        'httpBody' => 'http_body',
        'code' => 'code',
        'msg' => 'msg',
        'subCode' => 'sub_code',
        'subMsg' => 'sub_msg',
        'tradeNo' => 'trade_no',
        'outTradeNo' => 'out_trade_no',
        'buyerLogonId' => 'buyer_logon_id',
        'settleAmount' => 'settle_amount',
        'payCurrency' => 'pay_currency',
        'payAmount' => 'pay_amount',
        'settleTransRate' => 'settle_trans_rate',
        'transPayRate' => 'trans_pay_rate',
        'totalAmount' => 'total_amount',
        'transCurrency' => 'trans_currency',
        'settleCurrency' => 'settle_currency',
        'receiptAmount' => 'receipt_amount',
        'buyerPayAmount' => 'buyer_pay_amount',
        'pointAmount' => 'point_amount',
        'invoiceAmount' => 'invoice_amount',
        'gmtPayment' => 'gmt_payment',
        'fundBillList' => 'fund_bill_list',
        'cardBalance' => 'card_balance',
        'storeName' => 'store_name',
        'buyerUserId' => 'buyer_user_id',
        'discountGoodsDetail' => 'discount_goods_detail',
        'voucherDetailList' => 'voucher_detail_list',
        'advanceAmount' => 'advance_amount',
        'authTradePayMode' => 'auth_trade_pay_mode',
        'chargeAmount' => 'charge_amount',
        'chargeFlags' => 'charge_flags',
        'settlementId' => 'settlement_id',
        'businessParams' => 'business_params',
        'buyerUserType' => 'buyer_user_type',
        'mdiscountAmount' => 'mdiscount_amount',
        'discountAmount' => 'discount_amount',
        'buyerUserName' => 'buyer_user_name',
    ];
    public function validate() {
        Model::validateRequired('httpBody', $this->httpBody, true);
        Model::validateRequired('code', $this->code, true);
        Model::validateRequired('msg', $this->msg, true);
        Model::validateRequired('subCode', $this->subCode, true);
        Model::validateRequired('subMsg', $this->subMsg, true);
        Model::validateRequired('tradeNo', $this->tradeNo, true);
        Model::validateRequired('outTradeNo', $this->outTradeNo, true);
        Model::validateRequired('buyerLogonId', $this->buyerLogonId, true);
        Model::validateRequired('settleAmount', $this->settleAmount, true);
        Model::validateRequired('payCurrency', $this->payCurrency, true);
        Model::validateRequired('payAmount', $this->payAmount, true);
        Model::validateRequired('settleTransRate', $this->settleTransRate, true);
        Model::validateRequired('transPayRate', $this->transPayRate, true);
        Model::validateRequired('totalAmount', $this->totalAmount, true);
        Model::validateRequired('transCurrency', $this->transCurrency, true);
        Model::validateRequired('settleCurrency', $this->settleCurrency, true);
        Model::validateRequired('receiptAmount', $this->receiptAmount, true);
        Model::validateRequired('buyerPayAmount', $this->buyerPayAmount, true);
        Model::validateRequired('pointAmount', $this->pointAmount, true);
        Model::validateRequired('invoiceAmount', $this->invoiceAmount, true);
        Model::validateRequired('gmtPayment', $this->gmtPayment, true);
        Model::validateRequired('fundBillList', $this->fundBillList, true);
        Model::validateRequired('cardBalance', $this->cardBalance, true);
        Model::validateRequired('storeName', $this->storeName, true);
        Model::validateRequired('buyerUserId', $this->buyerUserId, true);
        Model::validateRequired('discountGoodsDetail', $this->discountGoodsDetail, true);
        Model::validateRequired('voucherDetailList', $this->voucherDetailList, true);
        Model::validateRequired('advanceAmount', $this->advanceAmount, true);
        Model::validateRequired('authTradePayMode', $this->authTradePayMode, true);
        Model::validateRequired('chargeAmount', $this->chargeAmount, true);
        Model::validateRequired('chargeFlags', $this->chargeFlags, true);
        Model::validateRequired('settlementId', $this->settlementId, true);
        Model::validateRequired('businessParams', $this->businessParams, true);
        Model::validateRequired('buyerUserType', $this->buyerUserType, true);
        Model::validateRequired('mdiscountAmount', $this->mdiscountAmount, true);
        Model::validateRequired('discountAmount', $this->discountAmount, true);
        Model::validateRequired('buyerUserName', $this->buyerUserName, true);
    }
    public function toMap() {
        $res = [];
        if (null !== $this->httpBody) {
            $res['http_body'] = $this->httpBody;
        }
        if (null !== $this->code) {
            $res['code'] = $this->code;
        }
        if (null !== $this->msg) {
            $res['msg'] = $this->msg;
        }
        if (null !== $this->subCode) {
            $res['sub_code'] = $this->subCode;
        }
        if (null !== $this->subMsg) {
            $res['sub_msg'] = $this->subMsg;
        }
        if (null !== $this->tradeNo) {
            $res['trade_no'] = $this->tradeNo;
        }
        if (null !== $this->outTradeNo) {
            $res['out_trade_no'] = $this->outTradeNo;
        }
        if (null !== $this->buyerLogonId) {
            $res['buyer_logon_id'] = $this->buyerLogonId;
        }
        if (null !== $this->settleAmount) {
            $res['settle_amount'] = $this->settleAmount;
        }
        if (null !== $this->payCurrency) {
            $res['pay_currency'] = $this->payCurrency;
        }
        if (null !== $this->payAmount) {
            $res['pay_amount'] = $this->payAmount;
        }
        if (null !== $this->settleTransRate) {
            $res['settle_trans_rate'] = $this->settleTransRate;
        }
        if (null !== $this->transPayRate) {
            $res['trans_pay_rate'] = $this->transPayRate;
        }
        if (null !== $this->totalAmount) {
            $res['total_amount'] = $this->totalAmount;
        }
        if (null !== $this->transCurrency) {
            $res['trans_currency'] = $this->transCurrency;
        }
        if (null !== $this->settleCurrency) {
            $res['settle_currency'] = $this->settleCurrency;
        }
        if (null !== $this->receiptAmount) {
            $res['receipt_amount'] = $this->receiptAmount;
        }
        if (null !== $this->buyerPayAmount) {
            $res['buyer_pay_amount'] = $this->buyerPayAmount;
        }
        if (null !== $this->pointAmount) {
            $res['point_amount'] = $this->pointAmount;
        }
        if (null !== $this->invoiceAmount) {
            $res['invoice_amount'] = $this->invoiceAmount;
        }
        if (null !== $this->gmtPayment) {
            $res['gmt_payment'] = $this->gmtPayment;
        }
        if (null !== $this->fundBillList) {
            $res['fund_bill_list'] = [];
            if(null !== $this->fundBillList && is_array($this->fundBillList)){
                $n = 0;
                foreach($this->fundBillList as $item){
                    $res['fund_bill_list'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        if (null !== $this->cardBalance) {
            $res['card_balance'] = $this->cardBalance;
        }
        if (null !== $this->storeName) {
            $res['store_name'] = $this->storeName;
        }
        if (null !== $this->buyerUserId) {
            $res['buyer_user_id'] = $this->buyerUserId;
        }
        if (null !== $this->discountGoodsDetail) {
            $res['discount_goods_detail'] = $this->discountGoodsDetail;
        }
        if (null !== $this->voucherDetailList) {
            $res['voucher_detail_list'] = [];
            if(null !== $this->voucherDetailList && is_array($this->voucherDetailList)){
                $n = 0;
                foreach($this->voucherDetailList as $item){
                    $res['voucher_detail_list'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }
        if (null !== $this->advanceAmount) {
            $res['advance_amount'] = $this->advanceAmount;
        }
        if (null !== $this->authTradePayMode) {
            $res['auth_trade_pay_mode'] = $this->authTradePayMode;
        }
        if (null !== $this->chargeAmount) {
            $res['charge_amount'] = $this->chargeAmount;
        }
        if (null !== $this->chargeFlags) {
            $res['charge_flags'] = $this->chargeFlags;
        }
        if (null !== $this->settlementId) {
            $res['settlement_id'] = $this->settlementId;
        }
        if (null !== $this->businessParams) {
            $res['business_params'] = $this->businessParams;
        }
        if (null !== $this->buyerUserType) {
            $res['buyer_user_type'] = $this->buyerUserType;
        }
        if (null !== $this->mdiscountAmount) {
            $res['mdiscount_amount'] = $this->mdiscountAmount;
        }
        if (null !== $this->discountAmount) {
            $res['discount_amount'] = $this->discountAmount;
        }
        if (null !== $this->buyerUserName) {
            $res['buyer_user_name'] = $this->buyerUserName;
        }
        return $res;
    }
    /**
     * @param array $map
     * @return AlipayTradePayResponse
     */
    public static function fromMap($map = []) {
        $model = new self();
        if(isset($map['http_body'])){
            $model->httpBody = $map['http_body'];
        }
        if(isset($map['code'])){
            $model->code = $map['code'];
        }
        if(isset($map['msg'])){
            $model->msg = $map['msg'];
        }
        if(isset($map['sub_code'])){
            $model->subCode = $map['sub_code'];
        }
        if(isset($map['sub_msg'])){
            $model->subMsg = $map['sub_msg'];
        }
        if(isset($map['trade_no'])){
            $model->tradeNo = $map['trade_no'];
        }
        if(isset($map['out_trade_no'])){
            $model->outTradeNo = $map['out_trade_no'];
        }
        if(isset($map['buyer_logon_id'])){
            $model->buyerLogonId = $map['buyer_logon_id'];
        }
        if(isset($map['settle_amount'])){
            $model->settleAmount = $map['settle_amount'];
        }
        if(isset($map['pay_currency'])){
            $model->payCurrency = $map['pay_currency'];
        }
        if(isset($map['pay_amount'])){
            $model->payAmount = $map['pay_amount'];
        }
        if(isset($map['settle_trans_rate'])){
            $model->settleTransRate = $map['settle_trans_rate'];
        }
        if(isset($map['trans_pay_rate'])){
            $model->transPayRate = $map['trans_pay_rate'];
        }
        if(isset($map['total_amount'])){
            $model->totalAmount = $map['total_amount'];
        }
        if(isset($map['trans_currency'])){
            $model->transCurrency = $map['trans_currency'];
        }
        if(isset($map['settle_currency'])){
            $model->settleCurrency = $map['settle_currency'];
        }
        if(isset($map['receipt_amount'])){
            $model->receiptAmount = $map['receipt_amount'];
        }
        if(isset($map['buyer_pay_amount'])){
            $model->buyerPayAmount = $map['buyer_pay_amount'];
        }
        if(isset($map['point_amount'])){
            $model->pointAmount = $map['point_amount'];
        }
        if(isset($map['invoice_amount'])){
            $model->invoiceAmount = $map['invoice_amount'];
        }
        if(isset($map['gmt_payment'])){
            $model->gmtPayment = $map['gmt_payment'];
        }
        if(isset($map['fund_bill_list'])){
            if(!empty($map['fund_bill_list'])){
                $model->fundBillList = [];
                $n = 0;
                foreach($map['fund_bill_list'] as $item) {
                    $model->fundBillList[$n++] = null !== $item ? TradeFundBill::fromMap($item) : $item;
                }
            }
        }
        if(isset($map['card_balance'])){
            $model->cardBalance = $map['card_balance'];
        }
        if(isset($map['store_name'])){
            $model->storeName = $map['store_name'];
        }
        if(isset($map['buyer_user_id'])){
            $model->buyerUserId = $map['buyer_user_id'];
        }
        if(isset($map['discount_goods_detail'])){
            $model->discountGoodsDetail = $map['discount_goods_detail'];
        }
        if(isset($map['voucher_detail_list'])){
            if(!empty($map['voucher_detail_list'])){
                $model->voucherDetailList = [];
                $n = 0;
                foreach($map['voucher_detail_list'] as $item) {
                    $model->voucherDetailList[$n++] = null !== $item ? VoucherDetail::fromMap($item) : $item;
                }
            }
        }
        if(isset($map['advance_amount'])){
            $model->advanceAmount = $map['advance_amount'];
        }
        if(isset($map['auth_trade_pay_mode'])){
            $model->authTradePayMode = $map['auth_trade_pay_mode'];
        }
        if(isset($map['charge_amount'])){
            $model->chargeAmount = $map['charge_amount'];
        }
        if(isset($map['charge_flags'])){
            $model->chargeFlags = $map['charge_flags'];
        }
        if(isset($map['settlement_id'])){
            $model->settlementId = $map['settlement_id'];
        }
        if(isset($map['business_params'])){
            $model->businessParams = $map['business_params'];
        }
        if(isset($map['buyer_user_type'])){
            $model->buyerUserType = $map['buyer_user_type'];
        }
        if(isset($map['mdiscount_amount'])){
            $model->mdiscountAmount = $map['mdiscount_amount'];
        }
        if(isset($map['discount_amount'])){
            $model->discountAmount = $map['discount_amount'];
        }
        if(isset($map['buyer_user_name'])){
            $model->buyerUserName = $map['buyer_user_name'];
        }
        return $model;
    }
    /**
     * @description 响应原始字符串
     * @var string
     */
    public $httpBody;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $msg;

    /**
     * @var string
     */
    public $subCode;

    /**
     * @var string
     */
    public $subMsg;

    /**
     * @var string
     */
    public $tradeNo;

    /**
     * @var string
     */
    public $outTradeNo;

    /**
     * @var string
     */
    public $buyerLogonId;

    /**
     * @var string
     */
    public $settleAmount;

    /**
     * @var string
     */
    public $payCurrency;

    /**
     * @var string
     */
    public $payAmount;

    /**
     * @var string
     */
    public $settleTransRate;

    /**
     * @var string
     */
    public $transPayRate;

    /**
     * @var string
     */
    public $totalAmount;

    /**
     * @var string
     */
    public $transCurrency;

    /**
     * @var string
     */
    public $settleCurrency;

    /**
     * @var string
     */
    public $receiptAmount;

    /**
     * @var string
     */
    public $buyerPayAmount;

    /**
     * @var string
     */
    public $pointAmount;

    /**
     * @var string
     */
    public $invoiceAmount;

    /**
     * @var string
     */
    public $gmtPayment;

    /**
     * @var array
     */
    public $fundBillList;

    /**
     * @var string
     */
    public $cardBalance;

    /**
     * @var string
     */
    public $storeName;

    /**
     * @var string
     */
    public $buyerUserId;

    /**
     * @var string
     */
    public $discountGoodsDetail;

    /**
     * @var array
     */
    public $voucherDetailList;

    /**
     * @var string
     */
    public $advanceAmount;

    /**
     * @var string
     */
    public $authTradePayMode;

    /**
     * @var string
     */
    public $chargeAmount;

    /**
     * @var string
     */
    public $chargeFlags;

    /**
     * @var string
     */
    public $settlementId;

    /**
     * @var string
     */
    public $businessParams;

    /**
     * @var string
     */
    public $buyerUserType;

    /**
     * @var string
     */
    public $mdiscountAmount;

    /**
     * @var string
     */
    public $discountAmount;

    /**
     * @var string
     */
    public $buyerUserName;

}
