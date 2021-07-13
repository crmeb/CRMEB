// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeRefundResponse extends TeaModel {
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

    @NameInMap("fund_change")
    @Validation(required = true)
    public String fundChange;

    @NameInMap("refund_fee")
    @Validation(required = true)
    public String refundFee;

    @NameInMap("refund_currency")
    @Validation(required = true)
    public String refundCurrency;

    @NameInMap("gmt_refund_pay")
    @Validation(required = true)
    public String gmtRefundPay;

    @NameInMap("refund_detail_item_list")
    @Validation(required = true)
    public java.util.List<TradeFundBill> refundDetailItemList;

    @NameInMap("store_name")
    @Validation(required = true)
    public String storeName;

    @NameInMap("buyer_user_id")
    @Validation(required = true)
    public String buyerUserId;

    @NameInMap("refund_preset_paytool_list")
    @Validation(required = true)
    public java.util.List<PresetPayToolInfo> refundPresetPaytoolList;

    @NameInMap("refund_settlement_id")
    @Validation(required = true)
    public String refundSettlementId;

    @NameInMap("present_refund_buyer_amount")
    @Validation(required = true)
    public String presentRefundBuyerAmount;

    @NameInMap("present_refund_discount_amount")
    @Validation(required = true)
    public String presentRefundDiscountAmount;

    @NameInMap("present_refund_mdiscount_amount")
    @Validation(required = true)
    public String presentRefundMdiscountAmount;

    public static AlipayTradeRefundResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeRefundResponse self = new AlipayTradeRefundResponse();
        return TeaModel.build(map, self);
    }

}
