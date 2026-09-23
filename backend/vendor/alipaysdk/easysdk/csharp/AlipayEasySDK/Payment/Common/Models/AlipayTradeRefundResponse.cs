// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class AlipayTradeRefundResponse : TeaModel {
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

        [NameInMap("fund_change")]
        [Validation(Required=true)]
        public string FundChange { get; set; }

        [NameInMap("refund_fee")]
        [Validation(Required=true)]
        public string RefundFee { get; set; }

        [NameInMap("refund_currency")]
        [Validation(Required=true)]
        public string RefundCurrency { get; set; }

        [NameInMap("gmt_refund_pay")]
        [Validation(Required=true)]
        public string GmtRefundPay { get; set; }

        [NameInMap("refund_detail_item_list")]
        [Validation(Required=true)]
        public List<TradeFundBill> RefundDetailItemList { get; set; }

        [NameInMap("store_name")]
        [Validation(Required=true)]
        public string StoreName { get; set; }

        [NameInMap("buyer_user_id")]
        [Validation(Required=true)]
        public string BuyerUserId { get; set; }

        [NameInMap("refund_preset_paytool_list")]
        [Validation(Required=true)]
        public List<PresetPayToolInfo> RefundPresetPaytoolList { get; set; }

        [NameInMap("refund_settlement_id")]
        [Validation(Required=true)]
        public string RefundSettlementId { get; set; }

        [NameInMap("present_refund_buyer_amount")]
        [Validation(Required=true)]
        public string PresentRefundBuyerAmount { get; set; }

        [NameInMap("present_refund_discount_amount")]
        [Validation(Required=true)]
        public string PresentRefundDiscountAmount { get; set; }

        [NameInMap("present_refund_mdiscount_amount")]
        [Validation(Required=true)]
        public string PresentRefundMdiscountAmount { get; set; }

    }

}
