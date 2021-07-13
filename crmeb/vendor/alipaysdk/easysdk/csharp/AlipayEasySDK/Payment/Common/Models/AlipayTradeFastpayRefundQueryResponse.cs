// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class AlipayTradeFastpayRefundQueryResponse : TeaModel {
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

        [NameInMap("error_code")]
        [Validation(Required=true)]
        public string ErrorCode { get; set; }

        [NameInMap("gmt_refund_pay")]
        [Validation(Required=true)]
        public string GmtRefundPay { get; set; }

        [NameInMap("industry_sepc_detail")]
        [Validation(Required=true)]
        public string IndustrySepcDetail { get; set; }

        [NameInMap("out_request_no")]
        [Validation(Required=true)]
        public string OutRequestNo { get; set; }

        [NameInMap("out_trade_no")]
        [Validation(Required=true)]
        public string OutTradeNo { get; set; }

        [NameInMap("present_refund_buyer_amount")]
        [Validation(Required=true)]
        public string PresentRefundBuyerAmount { get; set; }

        [NameInMap("present_refund_discount_amount")]
        [Validation(Required=true)]
        public string PresentRefundDiscountAmount { get; set; }

        [NameInMap("present_refund_mdiscount_amount")]
        [Validation(Required=true)]
        public string PresentRefundMdiscountAmount { get; set; }

        [NameInMap("refund_amount")]
        [Validation(Required=true)]
        public string RefundAmount { get; set; }

        [NameInMap("refund_charge_amount")]
        [Validation(Required=true)]
        public string RefundChargeAmount { get; set; }

        [NameInMap("refund_detail_item_list")]
        [Validation(Required=true)]
        public List<TradeFundBill> RefundDetailItemList { get; set; }

        [NameInMap("refund_reason")]
        [Validation(Required=true)]
        public string RefundReason { get; set; }

        [NameInMap("refund_royaltys")]
        [Validation(Required=true)]
        public List<RefundRoyaltyResult> RefundRoyaltys { get; set; }

        [NameInMap("refund_settlement_id")]
        [Validation(Required=true)]
        public string RefundSettlementId { get; set; }

        [NameInMap("refund_status")]
        [Validation(Required=true)]
        public string RefundStatus { get; set; }

        [NameInMap("send_back_fee")]
        [Validation(Required=true)]
        public string SendBackFee { get; set; }

        [NameInMap("total_amount")]
        [Validation(Required=true)]
        public string TotalAmount { get; set; }

        [NameInMap("trade_no")]
        [Validation(Required=true)]
        public string TradeNo { get; set; }

    }

}
