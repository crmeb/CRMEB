// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.pass.models;

import com.aliyun.tea.*;

public class AlipayPassTemplateUpdateResponse extends TeaModel {
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

    @NameInMap("success")
    @Validation(required = true)
    public Boolean success;

    @NameInMap("result")
    @Validation(required = true)
    public String result;

    public static AlipayPassTemplateUpdateResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayPassTemplateUpdateResponse self = new AlipayPassTemplateUpdateResponse();
        return TeaModel.build(map, self);
    }

    public AlipayPassTemplateUpdateResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayPassTemplateUpdateResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayPassTemplateUpdateResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayPassTemplateUpdateResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayPassTemplateUpdateResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayPassTemplateUpdateResponse setSuccess(Boolean success) {
        this.success = success;
        return this;
    }
    public Boolean getSuccess() {
        return this.success;
    }

    public AlipayPassTemplateUpdateResponse setResult(String result) {
        this.result = result;
        return this;
    }
    public String getResult() {
        return this.result;
    }

}
