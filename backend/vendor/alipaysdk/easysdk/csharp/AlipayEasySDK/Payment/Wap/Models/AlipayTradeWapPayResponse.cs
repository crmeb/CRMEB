// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Wap.Models
{
    public class AlipayTradeWapPayResponse : TeaModel {
        /// <summary>
        /// 订单信息，Form表单形式
        /// </summary>
        [NameInMap("body")]
        [Validation(Required=true)]
        public string Body { get; set; }

    }

}
