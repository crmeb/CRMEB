// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class AlipayUserCertifyOpenQueryResponse extends TeaModel {
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

    @NameInMap("passed")
    @Validation(required = true)
    public String passed;

    @NameInMap("identity_info")
    @Validation(required = true)
    public String identityInfo;

    @NameInMap("material_info")
    @Validation(required = true)
    public String materialInfo;

    public static AlipayUserCertifyOpenQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayUserCertifyOpenQueryResponse self = new AlipayUserCertifyOpenQueryResponse();
        return TeaModel.build(map, self);
    }

    public AlipayUserCertifyOpenQueryResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayUserCertifyOpenQueryResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayUserCertifyOpenQueryResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayUserCertifyOpenQueryResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayUserCertifyOpenQueryResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayUserCertifyOpenQueryResponse setPassed(String passed) {
        this.passed = passed;
        return this;
    }
    public String getPassed() {
        return this.passed;
    }

    public AlipayUserCertifyOpenQueryResponse setIdentityInfo(String identityInfo) {
        this.identityInfo = identityInfo;
        return this;
    }
    public String getIdentityInfo() {
        return this.identityInfo;
    }

    public AlipayUserCertifyOpenQueryResponse setMaterialInfo(String materialInfo) {
        this.materialInfo = materialInfo;
        return this;
    }
    public String getMaterialInfo() {
        return this.materialInfo;
    }

}
