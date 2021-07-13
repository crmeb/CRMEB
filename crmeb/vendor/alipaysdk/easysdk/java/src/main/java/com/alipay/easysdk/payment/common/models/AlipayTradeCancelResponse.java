// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeCancelResponse extends TeaModel {
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

}
