// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class AlipayTradeQueryResponse : TeaModel {
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

        [NameInMap("trade_status")]
        [Validation(Required=true)]
        public string TradeStatus { get; set; }

        [NameInMap("total_amount")]
        [Validation(Required=true)]
        public string TotalAmount { get; set; }

        [NameInMap("trans_currency")]
        [Validation(Required=true)]
        public string TransCurrency { get; set; }

        [NameInMap("settle_currency")]
        [Validation(Required=true)]
        public string SettleCurrency { get; set; }

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

        [NameInMap("buyer_pay_amount")]
        [Validation(Required=true)]
        public string BuyerPayAmount { get; set; }

        [NameInMap("point_amount")]
        [Validation(Required=true)]
        public string PointAmount { get; set; }

        [NameInMap("invoice_amount")]
        [Validation(Required=true)]
        public string InvoiceAmount { get; set; }

        [NameInMap("send_pay_date")]
        [Validation(Required=true)]
        public string SendPayDate { get; set; }

        [NameInMap("receipt_amount")]
        [Validation(Required=true)]
        public string ReceiptAmount { get; set; }

        [NameInMap("store_id")]
        [Validation(Required=true)]
        public string StoreId { get; set; }

        [NameInMap("terminal_id")]
        [Validation(Required=true)]
        public string TerminalId { get; set; }

        [NameInMap("fund_bill_list")]
        [Validation(Required=true)]
        public List<TradeFundBill> FundBillList { get; set; }

        [NameInMap("store_name")]
        [Validation(Required=true)]
        public string StoreName { get; set; }

        [NameInMap("buyer_user_id")]
        [Validation(Required=true)]
        public string BuyerUserId { get; set; }

        [NameInMap("charge_amount")]
        [Validation(Required=true)]
        public string ChargeAmount { get; set; }

        [NameInMap("charge_flags")]
        [Validation(Required=true)]
        public string ChargeFlags { get; set; }

        [NameInMap("settlement_id")]
        [Validation(Required=true)]
        public string SettlementId { get; set; }

        [NameInMap("trade_settle_info")]
        [Validation(Required=true)]
        public List<TradeSettleInfo> TradeSettleInfo { get; set; }

        [NameInMap("auth_trade_pay_mode")]
        [Validation(Required=true)]
        public string AuthTradePayMode { get; set; }

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

        [NameInMap("subject")]
        [Validation(Required=true)]
        public string Subject { get; set; }

        [NameInMap("body")]
        [Validation(Required=true)]
        public string Body { get; set; }

        [NameInMap("alipay_sub_merchant_id")]
        [Validation(Required=true)]
        public string AlipaySubMerchantId { get; set; }

        [NameInMap("ext_infos")]
        [Validation(Required=true)]
        public string ExtInfos { get; set; }

    }

}
