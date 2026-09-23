// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Base.OAuth.Models
{
    public class AlipaySystemOauthTokenResponse : TeaModel {
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

        [NameInMap("user_id")]
        [Validation(Required=true)]
        public string UserId { get; set; }

        [NameInMap("access_token")]
        [Validation(Required=true)]
        public string AccessToken { get; set; }

        [NameInMap("expires_in")]
        [Validation(Required=true)]
        public long ExpiresIn { get; set; }

        [NameInMap("refresh_token")]
        [Validation(Required=true)]
        public string RefreshToken { get; set; }

        [NameInMap("re_expires_in")]
        [Validation(Required=true)]
        public long ReExpiresIn { get; set; }

    }

}
