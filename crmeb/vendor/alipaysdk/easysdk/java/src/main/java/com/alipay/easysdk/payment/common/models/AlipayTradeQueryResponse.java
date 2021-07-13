// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeQueryResponse extends TeaModel {
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

}
