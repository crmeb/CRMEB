// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Member.Identification.Models
{
    public class IdentityParam : TeaModel {
        [NameInMap("identity_type")]
        [Validation(Required=true)]
        public string IdentityType { get; set; }

        [NameInMap("cert_type")]
        [Validation(Required=true)]
        public string CertType { get; set; }

        [NameInMap("cert_name")]
        [Validation(Required=true)]
        public string CertName { get; set; }

        [NameInMap("cert_no")]
        [Validation(Required=true)]
        public string CertNo { get; set; }

    }

}
