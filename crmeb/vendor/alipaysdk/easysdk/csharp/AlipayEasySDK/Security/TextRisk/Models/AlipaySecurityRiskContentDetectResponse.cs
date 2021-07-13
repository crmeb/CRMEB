// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Security.TextRisk.Models
{
    public class AlipaySecurityRiskContentDetectResponse : TeaModel {
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

        [NameInMap("action")]
        [Validation(Required=true)]
        public string Action { get; set; }

        [NameInMap("keywords")]
        [Validation(Required=true)]
        public List<string> Keywords { get; set; }

        [NameInMap("unique_id")]
        [Validation(Required=true)]
        public string UniqueId { get; set; }

    }

}
