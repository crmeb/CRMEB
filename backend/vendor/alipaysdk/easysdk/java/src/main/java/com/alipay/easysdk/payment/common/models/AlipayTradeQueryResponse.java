// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeQueryResponse extends TeaModel {
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

    @NameInMap("trade_status")
    @Validation(required = true)
    public String tradeStatus;

    @NameInMap("total_amount")
    @Validation(required = true)
    public String totalAmount;

    @NameInMap("trans_currency")
    @Validation(required = true)
    public String transCurrency;

    @NameInMap("settle_currency")
    @Validation(required = true)
    public String settleCurrency;

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

    @NameInMap("buyer_pay_amount")
    @Validation(required = true)
    public String buyerPayAmount;

    @NameInMap("point_amount")
    @Validation(required = true)
    public String pointAmount;

    @NameInMap("invoice_amount")
    @Validation(required = true)
    public String invoiceAmount;

    @NameInMap("send_pay_date")
    @Validation(required = true)
    public String sendPayDate;

    @NameInMap("receipt_amount")
    @Validation(required = true)
    public String receiptAmount;

    @NameInMap("store_id")
    @Validation(required = true)
    public String storeId;

    @NameInMap("terminal_id")
    @Validation(required = true)
    public String terminalId;

    @NameInMap("fund_bill_list")
    @Validation(required = true)
    public java.util.List<TradeFundBill> fundBillList;

    @NameInMap("store_name")
    @Validation(required = true)
    public String storeName;

    @NameInMap("buyer_user_id")
    @Validation(required = true)
    public String buyerUserId;

    @NameInMap("charge_amount")
    @Validation(required = true)
    public String chargeAmount;

    @NameInMap("charge_flags")
    @Validation(required = true)
    public String chargeFlags;

    @NameInMap("settlement_id")
    @Validation(required = true)
    public String settlementId;

    @NameInMap("trade_settle_info")
    @Validation(required = true)
    public java.util.List<TradeSettleInfo> tradeSettleInfo;

    @NameInMap("auth_trade_pay_mode")
    @Validation(required = true)
    public String authTradePayMode;

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

    @NameInMap("subject")
    @Validation(required = true)
    public String subject;

    @NameInMap("body")
    @Validation(required = true)
    public String body;

    @NameInMap("alipay_sub_merchant_id")
    @Validation(required = true)
    public String alipaySubMerchantId;

    @NameInMap("ext_infos")
    @Validation(required = true)
    public String extInfos;

    public static AlipayTradeQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeQueryResponse self = new AlipayTradeQueryResponse();
        return TeaModel.build(map, self);
    }

    public AlipayTradeQueryResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayTradeQueryResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayTradeQueryResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayTradeQueryResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayTradeQueryResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayTradeQueryResponse setTradeNo(String tradeNo) {
        this.tradeNo = tradeNo;
        return this;
    }
    public String getTradeNo() {
        return this.tradeNo;
    }

    public AlipayTradeQueryResponse setOutTradeNo(String outTradeNo) {
        this.outTradeNo = outTradeNo;
        return this;
    }
    public String getOutTradeNo() {
        return this.outTradeNo;
    }

    public AlipayTradeQueryResponse setBuyerLogonId(String buyerLogonId) {
        this.buyerLogonId = buyerLogonId;
        return this;
    }
    public String getBuyerLogonId() {
        return this.buyerLogonId;
    }

    public AlipayTradeQueryResponse setTradeStatus(String tradeStatus) {
        this.tradeStatus = tradeStatus;
        return this;
    }
    public String getTradeStatus() {
        return this.tradeStatus;
    }

    public AlipayTradeQueryResponse setTotalAmount(String totalAmount) {
        this.totalAmount = totalAmount;
        return this;
    }
    public String getTotalAmount() {
        return this.totalAmount;
    }

    public AlipayTradeQueryResponse setTransCurrency(String transCurrency) {
        this.transCurrency = transCurrency;
        return this;
    }
    public String getTransCurrency() {
        return this.transCurrency;
    }

    public AlipayTradeQueryResponse setSettleCurrency(String settleCurrency) {
        this.settleCurrency = settleCurrency;
        return this;
    }
    public String getSettleCurrency() {
        return this.settleCurrency;
    }

    public AlipayTradeQueryResponse setSettleAmount(String settleAmount) {
        this.settleAmount = settleAmount;
        return this;
    }
    public String getSettleAmount() {
        return this.settleAmount;
    }

    public AlipayTradeQueryResponse setPayCurrency(String payCurrency) {
        this.payCurrency = payCurrency;
        return this;
    }
    public String getPayCurrency() {
        return this.payCurrency;
    }

    public AlipayTradeQueryResponse setPayAmount(String payAmount) {
        this.payAmount = payAmount;
        return this;
    }
    public String getPayAmount() {
        return this.payAmount;
    }

    public AlipayTradeQueryResponse setSettleTransRate(String settleTransRate) {
        this.settleTransRate = settleTransRate;
        return this;
    }
    public String getSettleTransRate() {
        return this.settleTransRate;
    }

    public AlipayTradeQueryResponse setTransPayRate(String transPayRate) {
        this.transPayRate = transPayRate;
        return this;
    }
    public String getTransPayRate() {
        return this.transPayRate;
    }

    public AlipayTradeQueryResponse setBuyerPayAmount(String buyerPayAmount) {
        this.buyerPayAmount = buyerPayAmount;
        return this;
    }
    public String getBuyerPayAmount() {
        return this.buyerPayAmount;
    }

    public AlipayTradeQueryResponse setPointAmount(String pointAmount) {
        this.pointAmount = pointAmount;
        return this;
    }
    public String getPointAmount() {
        return this.pointAmount;
    }

    public AlipayTradeQueryResponse setInvoiceAmount(String invoiceAmount) {
        this.invoiceAmount = invoiceAmount;
        return this;
    }
    public String getInvoiceAmount() {
        return this.invoiceAmount;
    }

    public AlipayTradeQueryResponse setSendPayDate(String sendPayDate) {
        this.sendPayDate = sendPayDate;
        return this;
    }
    public String getSendPayDate() {
        return this.sendPayDate;
    }

    public AlipayTradeQueryResponse setReceiptAmount(String receiptAmount) {
        this.receiptAmount = receiptAmount;
        return this;
    }
    public String getReceiptAmount() {
        return this.receiptAmount;
    }

    public AlipayTradeQueryResponse setStoreId(String storeId) {
        this.storeId = storeId;
        return this;
    }
    public String getStoreId() {
        return this.storeId;
    }

    public AlipayTradeQueryResponse setTerminalId(String terminalId) {
        this.terminalId = terminalId;
        return this;
    }
    public String getTerminalId() {
        return this.terminalId;
    }

    public AlipayTradeQueryResponse setFundBillList(java.util.List<TradeFundBill> fundBillList) {
        this.fundBillList = fundBillList;
        return this;
    }
    public java.util.List<TradeFundBill> getFundBillList() {
        return this.fundBillList;
    }

    public AlipayTradeQueryResponse setStoreName(String storeName) {
        this.storeName = storeName;
        return this;
    }
    public String getStoreName() {
        return this.storeName;
    }

    public AlipayTradeQueryResponse setBuyerUserId(String buyerUserId) {
        this.buyerUserId = buyerUserId;
        return this;
    }
    public String getBuyerUserId() {
        return this.buyerUserId;
    }

    public AlipayTradeQueryResponse setChargeAmount(String chargeAmount) {
        this.chargeAmount = chargeAmount;
        return this;
    }
    public String getChargeAmount() {
        return this.chargeAmount;
    }

    public AlipayTradeQueryResponse setChargeFlags(String chargeFlags) {
        this.chargeFlags = chargeFlags;
        return this;
    }
    public String getChargeFlags() {
        return this.chargeFlags;
    }

    public AlipayTradeQueryResponse setSettlementId(String settlementId) {
        this.settlementId = settlementId;
        return this;
    }
    public String getSettlementId() {
        return this.settlementId;
    }

    public AlipayTradeQueryResponse setTradeSettleInfo(java.util.List<TradeSettleInfo> tradeSettleInfo) {
        this.tradeSettleInfo = tradeSettleInfo;
        return this;
    }
    public java.util.List<TradeSettleInfo> getTradeSettleInfo() {
        return this.tradeSettleInfo;
    }

    public AlipayTradeQueryResponse setAuthTradePayMode(String authTradePayMode) {
        this.authTradePayMode = authTradePayMode;
        return this;
    }
    public String getAuthTradePayMode() {
        return this.authTradePayMode;
    }

    public AlipayTradeQueryResponse setBuyerUserType(String buyerUserType) {
        this.buyerUserType = buyerUserType;
        return this;
    }
    public String getBuyerUserType() {
        return this.buyerUserType;
    }

    public AlipayTradeQueryResponse setMdiscountAmount(String mdiscountAmount) {
        this.mdiscountAmount = mdiscountAmount;
        return this;
    }
    public String getMdiscountAmount() {
        return this.mdiscountAmount;
    }

    public AlipayTradeQueryResponse setDiscountAmount(String discountAmount) {
        this.discountAmount = discountAmount;
        return this;
    }
    public String getDiscountAmount() {
        return this.discountAmount;
    }

    public AlipayTradeQueryResponse setBuyerUserName(String buyerUserName) {
        this.buyerUserName = buyerUserName;
        return this;
    }
    public String getBuyerUserName() {
        return this.buyerUserName;
    }

    public AlipayTradeQueryResponse setSubject(String subject) {
        this.subject = subject;
        return this;
    }
    public String getSubject() {
        return this.subject;
    }

    public AlipayTradeQueryResponse setBody(String body) {
        this.body = body;
        return this;
    }
    public String getBody() {
        return this.body;
    }

    public AlipayTradeQueryResponse setAlipaySubMerchantId(String alipaySubMerchantId) {
        this.alipaySubMerchantId = alipaySubMerchantId;
        return this;
    }
    public String getAlipaySubMerchantId() {
        return this.alipaySubMerchantId;
    }

    public AlipayTradeQueryResponse setExtInfos(String extInfos) {
        this.extInfos = extInfos;
        return this;
    }
    public String getExtInfos() {
        return this.extInfos;
    }

}
