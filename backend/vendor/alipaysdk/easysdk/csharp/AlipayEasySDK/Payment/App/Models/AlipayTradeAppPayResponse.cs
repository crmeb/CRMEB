// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.App.Models
{
    public class AlipayTradeAppPayResponse : TeaModel {
        /// <summary>
        /// 订单信息，字符串形式
        /// </summary>
        [NameInMap("body")]
        [Validation(Required=true)]
        public string Body { get; set; }

    }

}
