// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class TradeSettleInfo : TeaModel {
        [NameInMap("trade_settle_detail_list")]
        [Validation(Required=true)]
        public List<TradeSettleDetail> TradeSettleDetailList { get; set; }

    }

}
