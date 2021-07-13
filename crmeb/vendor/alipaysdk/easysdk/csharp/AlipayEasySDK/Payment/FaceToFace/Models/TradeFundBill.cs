// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.FaceToFace.Models
{
    public class TradeFundBill : TeaModel {
        [NameInMap("fund_channel")]
        [Validation(Required=true)]
        public string FundChannel { get; set; }

        [NameInMap("bank_code")]
        [Validation(Required=true)]
        public string BankCode { get; set; }

        [NameInMap("amount")]
        [Validation(Required=true)]
        public string Amount { get; set; }

        [NameInMap("real_amount")]
        [Validation(Required=true)]
        public string RealAmount { get; set; }

    }

}
