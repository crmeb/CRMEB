// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.Common.Models
{
    public class PresetPayToolInfo : TeaModel {
        [NameInMap("amount")]
        [Validation(Required=true)]
        public List<string> Amount { get; set; }

        [NameInMap("assert_type_code")]
        [Validation(Required=true)]
        public string AssertTypeCode { get; set; }

    }

}
