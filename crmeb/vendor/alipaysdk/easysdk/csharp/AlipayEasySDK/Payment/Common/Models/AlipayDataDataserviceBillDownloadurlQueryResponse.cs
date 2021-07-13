// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class AlipayDataDataserviceBillDownloadurlQueryResponse : TeaModel {
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

        [NameInMap("bill_download_url")]
        [Validation(Required=true)]
        public string BillDownloadUrl { get; set; }

    }

}
