// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.security.textrisk.models;

import com.aliyun.tea.*;

public class AlipaySecurityRiskContentDetectResponse extends TeaModel {
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

    @NameInMap("action")
    @Validation(required = true)
    public String action;

    @NameInMap("keywords")
    @Validation(required = true)
    public java.util.List<String> keywords;

    @NameInMap("unique_id")
    @Validation(required = true)
    public String uniqueId;

    public static AlipaySecurityRiskContentDetectResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipaySecurityRiskContentDetectResponse self = new AlipaySecurityRiskContentDetectResponse();
        return TeaModel.build(map, self);
    }

    public AlipaySecurityRiskContentDetectResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipaySecurityRiskContentDetectResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipaySecurityRiskContentDetectResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipaySecurityRiskContentDetectResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipaySecurityRiskContentDetectResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipaySecurityRiskContentDetectResponse setAction(String action) {
        this.action = action;
        return this;
    }
    public String getAction() {
        return this.action;
    }

    public AlipaySecurityRiskContentDetectResponse setKeywords(java.util.List<String> keywords) {
        this.keywords = keywords;
        return this;
    }
    public java.util.List<String> getKeywords() {
        return this.keywords;
    }

    public AlipaySecurityRiskContentDetectResponse setUniqueId(String uniqueId) {
        this.uniqueId = uniqueId;
        return this;
    }
    public String getUniqueId() {
        return this.uniqueId;
    }

}
