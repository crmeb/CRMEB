// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class AlipayOpenPublicMessageContentModifyResponse extends TeaModel {
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

    @NameInMap("content_id")
    @Validation(required = true)
    public String contentId;

    @NameInMap("content_url")
    @Validation(required = true)
    public String contentUrl;

    public static AlipayOpenPublicMessageContentModifyResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenPublicMessageContentModifyResponse self = new AlipayOpenPublicMessageContentModifyResponse();
        return TeaModel.build(map, self);
    }

    public AlipayOpenPublicMessageContentModifyResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayOpenPublicMessageContentModifyResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayOpenPublicMessageContentModifyResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayOpenPublicMessageContentModifyResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayOpenPublicMessageContentModifyResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayOpenPublicMessageContentModifyResponse setContentId(String contentId) {
        this.contentId = contentId;
        return this;
    }
    public String getContentId() {
        return this.contentId;
    }

    public AlipayOpenPublicMessageContentModifyResponse setContentUrl(String contentUrl) {
        this.contentUrl = contentUrl;
        return this;
    }
    public String getContentUrl() {
        return this.contentUrl;
    }

}
