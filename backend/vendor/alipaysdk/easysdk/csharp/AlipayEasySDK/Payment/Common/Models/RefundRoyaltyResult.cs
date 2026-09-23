// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class RefundRoyaltyResult : TeaModel {
        [NameInMap("refund_amount")]
        [Validation(Required=true)]
        public string RefundAmount { get; set; }

        [NameInMap("royalty_type")]
        [Validation(Required=true)]
        public string RoyaltyType { get; set; }

        [NameInMap("result_code")]
        [Validation(Required=true)]
        public string ResultCode { get; set; }

        [NameInMap("trans_out")]
        [Validation(Required=true)]
        public string TransOut { get; set; }

        [NameInMap("trans_out_email")]
        [Validation(Required=true)]
        public string TransOutEmail { get; set; }

        [NameInMap("trans_in")]
        [Validation(Required=true)]
        public string TransIn { get; set; }

        [NameInMap("trans_in_email")]
        [Validation(Required=true)]
        public string TransInEmail { get; set; }

    }

}
