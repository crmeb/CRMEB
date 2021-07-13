// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Marketing.OpenLife.Models
{
    public class Template : TeaModel {
        [NameInMap("template_id")]
        [Validation(Required=true)]
        public string TemplateId { get; set; }

        [NameInMap("context")]
        [Validation(Required=true)]
        public Context Context { get; set; }

    }

}
