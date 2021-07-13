// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class AlipayTradeCancelResponse : TeaModel {
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

        [NameInMap("retry_flag")]
        [Validation(Required=true)]
        public string RetryFlag { get; set; }

        [NameInMap("action")]
        [Validation(Required=true)]
        public string Action { get; set; }

        [NameInMap("gmt_refund_pay")]
        [Validation(Required=true)]
        public string GmtRefundPay { get; set; }

        [NameInMap("refund_settlement_id")]
        [Validation(Required=true)]
        public string RefundSettlementId { get; set; }

    }

}
