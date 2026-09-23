// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Huabei.Models
{
    public class HuabeiConfig : TeaModel {
        [NameInMap("hb_fq_num")]
        [Validation(Required=true)]
        public string HbFqNum { get; set; }

        [NameInMap("hb_fq_seller_percent")]
        [Validation(Required=true)]
        public string HbFqSellerPercent { get; set; }

    }

}
