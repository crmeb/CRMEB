// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.FaceToFace.Models
{
    public class AlipayTradePayResponse : TeaModel {
        /// <summary>
        /// 响应原始字符串
        /// </summary>
        [NameInMap("http_body")]
        [Validation(Required=true)]
        public string HttpBody { get; set; }

        [NameInMap("code")]
        [Validation(Required=true)]
        public string Code { get; set; }

        [NameInMap("msg")]
        [Validation(Required=true)]
        public string Msg { get; set; }

        [NameInMap("sub_code")]
        [Validation(Required=true)]
        public string SubCode { get; set; }

        [NameInMap("sub_msg")]
        [Validation(Required=true)]
        public string SubMsg { get; set; }

        [NameInMap("trade_no")]
        [Validation(Required=true)]
        public string TradeNo { get; set; }

        [NameInMap("out_trade_no")]
        [Validation(Required=true)]
        public string OutTradeNo { get; set; }

        [NameInMap("buyer_logon_id")]
        [Validation(Required=true)]
        public string BuyerLogonId { get; set; }

        [NameInMap("settle_amount")]
        [Validation(Required=true)]
        public string SettleAmount { get; set; }

        [NameInMap("pay_currency")]
        [Validation(Required=true)]
        public string PayCurrency { get; set; }

        [NameInMap("pay_amount")]
        [Validation(Required=true)]
        public string PayAmount { get; set; }

        [NameInMap("settle_trans_rate")]
        [Validation(Required=true)]
        public string SettleTransRate { get; set; }

        [NameInMap("trans_pay_rate")]
        [Validation(Required=true)]
        public string TransPayRate { get; set; }

        [NameInMap("total_amount")]
        [Validation(Required=true)]
        public string TotalAmount { get; set; }

        [NameInMap("trans_currency")]
        [Validation(Required=true)]
        public string TransCurrency { get; set; }

        [NameInMap("settle_currency")]
        [Validation(Required=true)]
        public string SettleCurrency { get; set; }

        [NameInMap("receipt_amount")]
        [Validation(Required=true)]
        public string ReceiptAmount { get; set; }

        [NameInMap("buyer_pay_amount")]
        [Validation(Required=true)]
        public string BuyerPayAmount { get; set; }

        [NameInMap("point_amount")]
        [Validation(Required=true)]
        public string PointAmount { get; set; }

        [NameInMap("invoice_amount")]
        [Validation(Required=true)]
        public string InvoiceAmount { get; set; }

        [NameInMap("gmt_payment")]
        [Validation(Required=true)]
        public string GmtPayment { get; set; }

        [NameInMap("fund_bill_list")]
        [Validation(Required=true)]
        public List<TradeFundBill> FundBillList { get; set; }

        [NameInMap("card_balance")]
        [Validation(Required=true)]
        public string CardBalance { get; set; }

        [NameInMap("store_name")]
        [Validation(Required=true)]
        public string StoreName { get; set; }

        [NameInMap("buyer_user_id")]
        [Validation(Required=true)]
        public string BuyerUserId { get; set; }

        [NameInMap("discount_goods_detail")]
        [Validation(Required=true)]
        public string DiscountGoodsDetail { get; set; }

        [NameInMap("voucher_detail_list")]
        [Validation(Required=true)]
        public List<VoucherDetail> VoucherDetailList { get; set; }

        [NameInMap("advance_amount")]
        [Validation(Required=true)]
        public string AdvanceAmount { get; set; }

        [NameInMap("auth_trade_pay_mode")]
        [Validation(Required=true)]
        public string AuthTradePayMode { get; set; }

        [NameInMap("charge_amount")]
        [Validation(Required=true)]
        public string ChargeAmount { get; set; }

        [NameInMap("charge_flags")]
        [Validation(Required=true)]
        public string ChargeFlags { get; set; }

        [NameInMap("settlement_id")]
        [Validation(Required=true)]
        public string SettlementId { get; set; }

        [NameInMap("business_params")]
        [Validation(Required=true)]
        public string BusinessParams { get; set; }

        [NameInMap("buyer_user_type")]
        [Validation(Required=true)]
        public string BuyerUserType { get; set; }

        [NameInMap("mdiscount_amount")]
        [Validation(Required=true)]
        public string MdiscountAmount { get; set; }

        [NameInMap("discount_amount")]
        [Validation(Required=true)]
        public string DiscountAmount { get; set; }

        [NameInMap("buyer_user_name")]
        [Validation(Required=true)]
        public string BuyerUserName { get; set; }

    }

}
