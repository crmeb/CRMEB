// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeCancelResponse extends TeaModel {
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

    @NameInMap("retry_flag")
    @Validation(required = true)
    public String retryFlag;

    @NameInMap("action")
    @Validation(required = true)
    public String action;

    @NameInMap("gmt_refund_pay")
    @Validation(required = true)
    public String gmtRefundPay;

    @NameInMap("refund_settlement_id")
    @Validation(required = true)
    public String refundSettlementId;

    public static AlipayTradeCancelResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeCancelResponse self = new AlipayTradeCancelResponse();
        return TeaModel.build(map, self);
    }

    public AlipayTradeCancelResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayTradeCancelResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayTradeCancelResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayTradeCancelResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayTradeCancelResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayTradeCancelResponse setTradeNo(String tradeNo) {
        this.tradeNo = tradeNo;
        return this;
    }
    public String getTradeNo() {
        return this.tradeNo;
    }

    public AlipayTradeCancelResponse setOutTradeNo(String outTradeNo) {
        this.outTradeNo = outTradeNo;
        return this;
    }
    public String getOutTradeNo() {
        return this.outTradeNo;
    }

    public AlipayTradeCancelResponse setRetryFlag(String retryFlag) {
        this.retryFlag = retryFlag;
        return this;
    }
    public String getRetryFlag() {
        return this.retryFlag;
    }

    public AlipayTradeCancelResponse setAction(String action) {
        this.action = action;
        return this;
    }
    public String getAction() {
        return this.action;
    }

    public AlipayTradeCancelResponse setGmtRefundPay(String gmtRefundPay) {
        this.gmtRefundPay = gmtRefundPay;
        return this;
    }
    public String getGmtRefundPay() {
        return this.gmtRefundPay;
    }

    public AlipayTradeCancelResponse setRefundSettlementId(String refundSettlementId) {
        this.refundSettlementId = refundSettlementId;
        return this;
    }
    public String getRefundSettlementId() {
        return this.refundSettlementId;
    }

}
