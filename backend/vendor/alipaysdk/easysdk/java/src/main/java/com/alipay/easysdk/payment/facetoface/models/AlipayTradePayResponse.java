// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.facetoface.models;

import com.aliyun.tea.*;

public class AlipayTradePayResponse extends TeaModel {
    // 响应原始字符串
    @NameInMap("http_body")
    @Validation(required = true)
    public String httpBody;

    @NameInMap("code")
    @Validation(required = true)
    public String code;

    @NameInMap("msg")
    @Validation(required = true)
    public String msg;

    @NameInMap("sub_code")
    @Validation(required = true)
    public String subCode;

    @NameInMap("sub_msg")
    @Validation(required = true)
    public String subMsg;

    @NameInMap("trade_no")
    @Validation(required = true)
    public String tradeNo;

    @NameInMap("out_trade_no")
    @Validation(required = true)
    public String outTradeNo;

    @NameInMap("buyer_logon_id")
    @Validation(required = true)
    public String buyerLogonId;

    @NameInMap("settle_amount")
    @Validation(required = true)
    public String settleAmount;

    @NameInMap("pay_currency")
    @Validation(required = true)
    public String payCurrency;

    @NameInMap("pay_amount")
    @Validation(required = true)
    public String payAmount;

    @NameInMap("settle_trans_rate")
    @Validation(required = true)
    public String settleTransRate;

    @NameInMap("trans_pay_rate")
    @Validation(required = true)
    public String transPayRate;

    @NameInMap("total_amount")
    @Validation(required = true)
    public String totalAmount;

    @NameInMap("trans_currency")
    @Validation(required = true)
    public String transCurrency;

    @NameInMap("settle_currency")
    @Validation(required = true)
    public String settleCurrency;

    @NameInMap("receipt_amount")
    @Validation(required = true)
    public String receiptAmount;

    @NameInMap("buyer_pay_amount")
    @Validation(required = true)
    public String buyerPayAmount;

    @NameInMap("point_amount")
    @Validation(required = true)
    public String pointAmount;

    @NameInMap("invoice_amount")
    @Validation(required = true)
    public String invoiceAmount;

    @NameInMap("gmt_payment")
    @Validation(required = true)
    public String gmtPayment;

    @NameInMap("fund_bill_list")
    @Validation(required = true)
    public java.util.List<TradeFundBill> fundBillList;

    @NameInMap("card_balance")
    @Validation(required = true)
    public String cardBalance;

    @NameInMap("store_name")
    @Validation(required = true)
    public String storeName;

    @NameInMap("buyer_user_id")
    @Validation(required = true)
    public String buyerUserId;

    @NameInMap("discount_goods_detail")
    @Validation(required = true)
    public String discountGoodsDetail;

    @NameInMap("voucher_detail_list")
    @Validation(required = true)
    public java.util.List<VoucherDetail> voucherDetailList;

    @NameInMap("advance_amount")
    @Validation(required = true)
    public String advanceAmount;

    @NameInMap("auth_trade_pay_mode")
    @Validation(required = true)
    public String authTradePayMode;

    @NameInMap("charge_amount")
    @Validation(required = true)
    public String chargeAmount;

    @NameInMap("charge_flags")
    @Validation(required = true)
    public String chargeFlags;

    @NameInMap("settlement_id")
    @Validation(required = true)
    public String settlementId;

    @NameInMap("business_params")
    @Validation(required = true)
    public String businessParams;

    @NameInMap("buyer_user_type")
    @Validation(required = true)
    public String buyerUserType;

    @NameInMap("mdiscount_amount")
    @Validation(required = true)
    public String mdiscountAmount;

    @NameInMap("discount_amount")
    @Validation(required = true)
    public String discountAmount;

    @NameInMap("buyer_user_name")
    @Validation(required = true)
    public String buyerUserName;

    public static AlipayTradePayResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradePayResponse self = new AlipayTradePayResponse();
        return TeaModel.build(map, self);
    }

    public AlipayTradePayResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayTradePayResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayTradePayResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayTradePayResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayTradePayResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayTradePayResponse setTradeNo(String tradeNo) {
        this.tradeNo = tradeNo;
        return this;
    }
    public String getTradeNo() {
        return this.tradeNo;
    }

    public AlipayTradePayResponse setOutTradeNo(String outTradeNo) {
        this.outTradeNo = outTradeNo;
        return this;
    }
    public String getOutTradeNo() {
        return this.outTradeNo;
    }

    public AlipayTradePayResponse setBuyerLogonId(String buyerLogonId) {
        this.buyerLogonId = buyerLogonId;
        return this;
    }
    public String getBuyerLogonId() {
        return this.buyerLogonId;
    }

    public AlipayTradePayResponse setSettleAmount(String settleAmount) {
        this.settleAmount = settleAmount;
        return this;
    }
    public String getSettleAmount() {
        return this.settleAmount;
    }

    public AlipayTradePayResponse setPayCurrency(String payCurrency) {
        this.payCurrency = payCurrency;
        return this;
    }
    public String getPayCurrency() {
        return this.payCurrency;
    }

    public AlipayTradePayResponse setPayAmount(String payAmount) {
        this.payAmount = payAmount;
        return this;
    }
    public String getPayAmount() {
        return this.payAmount;
    }

    public AlipayTradePayResponse setSettleTransRate(String settleTransRate) {
        this.settleTransRate = settleTransRate;
        return this;
    }
    public String getSettleTransRate() {
        return this.settleTransRate;
    }

    public AlipayTradePayResponse setTransPayRate(String transPayRate) {
        this.transPayRate = transPayRate;
        return this;
    }
    public String getTransPayRate() {
        return this.transPayRate;
    }

    public AlipayTradePayResponse setTotalAmount(String totalAmount) {
        this.totalAmount = totalAmount;
        return this;
    }
    public String getTotalAmount() {
        return this.totalAmount;
    }

    public AlipayTradePayResponse setTransCurrency(String transCurrency) {
        this.transCurrency = transCurrency;
        return this;
    }
    public String getTransCurrency() {
        return this.transCurrency;
    }

    public AlipayTradePayResponse setSettleCurrency(String settleCurrency) {
        this.settleCurrency = settleCurrency;
        return this;
    }
    public String getSettleCurrency() {
        return this.settleCurrency;
    }

    public AlipayTradePayResponse setReceiptAmount(String receiptAmount) {
        this.receiptAmount = receiptAmount;
        return this;
    }
    public String getReceiptAmount() {
        return this.receiptAmount;
    }

    public AlipayTradePayResponse setBuyerPayAmount(String buyerPayAmount) {
        this.buyerPayAmount = buyerPayAmount;
        return this;
    }
    public String getBuyerPayAmount() {
        return this.buyerPayAmount;
    }

    public AlipayTradePayResponse setPointAmount(String pointAmount) {
        this.pointAmount = pointAmount;
        return this;
    }
    public String getPointAmount() {
        return this.pointAmount;
    }

    public AlipayTradePayResponse setInvoiceAmount(String invoiceAmount) {
        this.invoiceAmount = invoiceAmount;
        return this;
    }
    public String getInvoiceAmount() {
        return this.invoiceAmount;
    }

    public AlipayTradePayResponse setGmtPayment(String gmtPayment) {
        this.gmtPayment = gmtPayment;
        return this;
    }
    public String getGmtPayment() {
        return this.gmtPayment;
    }

    public AlipayTradePayResponse setFundBillList(java.util.List<TradeFundBill> fundBillList) {
        this.fundBillList = fundBillList;
        return this;
    }
    public java.util.List<TradeFundBill> getFundBillList() {
        return this.fundBillList;
    }

    public AlipayTradePayResponse setCardBalance(String cardBalance) {
        this.cardBalance = cardBalance;
        return this;
    }
    public String getCardBalance() {
        return this.cardBalance;
    }

    public AlipayTradePayResponse setStoreName(String storeName) {
        this.storeName = storeName;
        return this;
    }
    public String getStoreName() {
        return this.storeName;
    }

    public AlipayTradePayResponse setBuyerUserId(String buyerUserId) {
        this.buyerUserId = buyerUserId;
        return this;
    }
    public String getBuyerUserId() {
        return this.buyerUserId;
    }

    public AlipayTradePayResponse setDiscountGoodsDetail(String discountGoodsDetail) {
        this.discountGoodsDetail = discountGoodsDetail;
        return this;
    }
    public String getDiscountGoodsDetail() {
        return this.discountGoodsDetail;
    }

    public AlipayTradePayResponse setVoucherDetailList(java.util.List<VoucherDetail> voucherDetailList) {
        this.voucherDetailList = voucherDetailList;
        return this;
    }
    public java.util.List<VoucherDetail> getVoucherDetailList() {
        return this.voucherDetailList;
    }

    public AlipayTradePayResponse setAdvanceAmount(String advanceAmount) {
        this.advanceAmount = advanceAmount;
        return this;
    }
    public String getAdvanceAmount() {
        return this.advanceAmount;
    }

    public AlipayTradePayResponse setAuthTradePayMode(String authTradePayMode) {
        this.authTradePayMode = authTradePayMode;
        return this;
    }
    public String getAuthTradePayMode() {
        return this.authTradePayMode;
    }

    public AlipayTradePayResponse setChargeAmount(String chargeAmount) {
        this.chargeAmount = chargeAmount;
        return this;
    }
    public String getChargeAmount() {
        return this.chargeAmount;
    }

    public AlipayTradePayResponse setChargeFlags(String chargeFlags) {
        this.chargeFlags = chargeFlags;
        return this;
    }
    public String getChargeFlags() {
        return this.chargeFlags;
    }

    public AlipayTradePayResponse setSettlementId(String settlementId) {
        this.settlementId = settlementId;
        return this;
    }
    public String getSettlementId() {
        return this.settlementId;
    }

    public AlipayTradePayResponse setBusinessParams(String businessParams) {
        this.businessParams = businessParams;
        return this;
    }
    public String getBusinessParams() {
        return this.businessParams;
    }

    public AlipayTradePayResponse setBuyerUserType(String buyerUserType) {
        this.buyerUserType = buyerUserType;
        return this;
    }
    public String getBuyerUserType() {
        return this.buyerUserType;
    }

    public AlipayTradePayResponse setMdiscountAmount(String mdiscountAmount) {
        this.mdiscountAmount = mdiscountAmount;
        return this;
    }
    public String getMdiscountAmount() {
        return this.mdiscountAmount;
    }

    public AlipayTradePayResponse setDiscountAmount(String discountAmount) {
        this.discountAmount = discountAmount;
        return this;
    }
    public String getDiscountAmount() {
        return this.discountAmount;
    }

    public AlipayTradePayResponse setBuyerUserName(String buyerUserName) {
        this.buyerUserName = buyerUserName;
        return this;
    }
    public String getBuyerUserName() {
        return this.buyerUserName;
    }

}
