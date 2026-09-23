// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Marketing.OpenLife.Models
{
    public class Article : TeaModel {
        [NameInMap("title")]
        [Validation(Required=false)]
        public string Title { get; set; }

        [NameInMap("desc")]
        [Validation(Required=true)]
        public string Desc { get; set; }

        [NameInMap("image_url")]
        [Validation(Required=false)]
        public string ImageUrl { get; set; }

        [NameInMap("url")]
        [Validation(Required=true)]
        public string Url { get; set; }

        [NameInMap("action_name")]
        [Validation(Required=false)]
        public string ActionName { get; set; }

    }

}
