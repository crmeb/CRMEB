// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.facetoface.models;

import com.aliyun.tea.*;

public class AlipayTradePayResponse extends TeaModel {
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

}
