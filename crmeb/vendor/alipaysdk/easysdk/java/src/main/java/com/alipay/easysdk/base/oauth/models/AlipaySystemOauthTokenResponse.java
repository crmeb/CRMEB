// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.oauth.models;

import com.aliyun.tea.*;

public class AlipaySystemOauthTokenResponse extends TeaModel {
    @NameInMap("http_body")
    @Validation(required = true)
    public String httpBody;

    @NameInMap("code")
    @Validation(required = true)
    public String code;

    @NameInMap("msg")
    @Validation(required = true)
    public String msg;

    @NameInMap("sub_code")
    @Validation(required = true)
    public String subCode;

    @NameInMap("sub_msg")
    @Validation(required = true)
    public String subMsg;

    @NameInMap("user_id")
    @Validation(required = true)
    public String userId;

    @NameInMap("access_token")
    @Validation(required = true)
    public String accessToken;

    @NameInMap("expires_in")
    @Validation(required = true)
    public Long expiresIn;

    @NameInMap("refresh_token")
    @Validation(required = true)
    public String refreshToken;

    @NameInMap("re_expires_in")
    @Validation(required = true)
    public Long reExpiresIn;

    public static AlipaySystemOauthTokenResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipaySystemOauthTokenResponse self = new AlipaySystemOauthTokenResponse();
        return TeaModel.build(map, self);
    }

}
