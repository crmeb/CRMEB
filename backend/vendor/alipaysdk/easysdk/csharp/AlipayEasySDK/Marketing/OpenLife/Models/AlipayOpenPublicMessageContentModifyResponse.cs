// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Marketing.OpenLife.Models
{
    public class AlipayOpenPublicMessageContentModifyResponse : TeaModel {
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

        [NameInMap("content_id")]
        [Validation(Required=true)]
        public string ContentId { get; set; }

        [NameInMap("content_url")]
        [Validation(Required=true)]
        public string ContentUrl { get; set; }

    }

}
