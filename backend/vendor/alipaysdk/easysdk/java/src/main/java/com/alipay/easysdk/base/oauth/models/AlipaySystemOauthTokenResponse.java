// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.oauth.models;

import com.aliyun.tea.*;

public class AlipaySystemOauthTokenResponse extends TeaModel {
    // 响应原始字符串
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

    public AlipaySystemOauthTokenResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipaySystemOauthTokenResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipaySystemOauthTokenResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipaySystemOauthTokenResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipaySystemOauthTokenResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipaySystemOauthTokenResponse setUserId(String userId) {
        this.userId = userId;
        return this;
    }
    public String getUserId() {
        return this.userId;
    }

    public AlipaySystemOauthTokenResponse setAccessToken(String accessToken) {
        this.accessToken = accessToken;
        return this;
    }
    public String getAccessToken() {
        return this.accessToken;
    }

    public AlipaySystemOauthTokenResponse setExpiresIn(Long expiresIn) {
        this.expiresIn = expiresIn;
        return this;
    }
    public Long getExpiresIn() {
        return this.expiresIn;
    }

    public AlipaySystemOauthTokenResponse setRefreshToken(String refreshToken) {
        this.refreshToken = refreshToken;
        return this;
    }
    public String getRefreshToken() {
        return this.refreshToken;
    }

    public AlipaySystemOauthTokenResponse setReExpiresIn(Long reExpiresIn) {
        this.reExpiresIn = reExpiresIn;
        return this;
    }
    public Long getReExpiresIn() {
        return this.reExpiresIn;
    }

}
