// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Marketing.OpenLife.Models
{
    public class Context : TeaModel {
        [NameInMap("head_color")]
        [Validation(Required=true)]
        public string HeadColor { get; set; }

        [NameInMap("url")]
        [Validation(Required=true)]
        public string Url { get; set; }

        [NameInMap("action_name")]
        [Validation(Required=true)]
        public string ActionName { get; set; }

        [NameInMap("keyword1")]
        [Validation(Required=false)]
        public Keyword Keyword1 { get; set; }

        [NameInMap("keyword2")]
        [Validation(Required=false)]
        public Keyword Keyword2 { get; set; }

        [NameInMap("first")]
        [Validation(Required=false)]
        public Keyword First { get; set; }

        [NameInMap("remark")]
        [Validation(Required=false)]
        public Keyword Remark { get; set; }

    }

}
