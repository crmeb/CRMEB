// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class AlipayOpenPublicTemplateMessageIndustryModifyResponse extends TeaModel {
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

    public static AlipayOpenPublicTemplateMessageIndustryModifyResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenPublicTemplateMessageIndustryModifyResponse self = new AlipayOpenPublicTemplateMessageIndustryModifyResponse();
        return TeaModel.build(map, self);
    }

    public AlipayOpenPublicTemplateMessageIndustryModifyResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayOpenPublicTemplateMessageIndustryModifyResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayOpenPublicTemplateMessageIndustryModifyResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayOpenPublicTemplateMessageIndustryModifyResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayOpenPublicTemplateMessageIndustryModifyResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

}
