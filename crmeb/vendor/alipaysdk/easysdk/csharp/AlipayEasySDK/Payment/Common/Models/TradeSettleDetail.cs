// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class TradeSettleDetail : TeaModel {
        [NameInMap("operation_type")]
        [Validation(Required=true)]
        public string OperationType { get; set; }

        [NameInMap("operation_serial_no")]
        [Validation(Required=true)]
        public string OperationSerial_no { get; set; }

        [NameInMap("operation_dt")]
        [Validation(Required=true)]
        public string OperationDt { get; set; }

        [NameInMap("trans_out")]
        [Validation(Required=true)]
        public string TransOut { get; set; }

        [NameInMap("trans_in")]
        [Validation(Required=true)]
        public string TransIn { get; set; }

        [NameInMap("amount")]
        [Validation(Required=true)]
        public string Amount { get; set; }

    }

}
