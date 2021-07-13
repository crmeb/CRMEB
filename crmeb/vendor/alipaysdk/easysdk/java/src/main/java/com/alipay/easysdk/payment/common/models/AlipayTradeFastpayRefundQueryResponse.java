// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeFastpayRefundQueryResponse extends TeaModel {
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

    @NameInMap("error_code")
    @Validation(required = true)
    public String errorCode;

    @NameInMap("gmt_refund_pay")
    @Validation(required = true)
    public String gmtRefundPay;

    @NameInMap("industry_sepc_detail")
    @Validation(required = true)
    public String industrySepcDetail;

    @NameInMap("out_request_no")
    @Validation(required = true)
    public String outRequestNo;

    @NameInMap("out_trade_no")
    @Validation(required = true)
    public String outTradeNo;

    @NameInMap("present_refund_buyer_amount")
    @Validation(required = true)
    public String presentRefundBuyerAmount;

    @NameInMap("present_refund_discount_amount")
    @Validation(required = true)
    public String presentRefundDiscountAmount;

    @NameInMap("present_refund_mdiscount_amount")
    @Validation(required = true)
    public String presentRefundMdiscountAmount;

    @NameInMap("refund_amount")
    @Validation(required = true)
    public String refundAmount;

    @NameInMap("refund_charge_amount")
    @Validation(required = true)
    public String refundChargeAmount;

    @NameInMap("refund_detail_item_list")
    @Validation(required = true)
    public java.util.List<TradeFundBill> refundDetailItemList;

    @NameInMap("refund_reason")
    @Validation(required = true)
    public String refundReason;

    @NameInMap("refund_royaltys")
    @Validation(required = true)
    public java.util.List<RefundRoyaltyResult> refundRoyaltys;

    @NameInMap("refund_settlement_id")
    @Validation(required = true)
    public String refundSettlementId;

    @NameInMap("refund_status")
    @Validation(required = true)
    public String refundStatus;

    @NameInMap("send_back_fee")
    @Validation(required = true)
    public String sendBackFee;

    @NameInMap("total_amount")
    @Validation(required = true)
    public String totalAmount;

    @NameInMap("trade_no")
    @Validation(required = true)
    public String tradeNo;

    public static AlipayTradeFastpayRefundQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeFastpayRefundQueryResponse self = new AlipayTradeFastpayRefundQueryResponse();
        return TeaModel.build(map, self);
    }

}
