// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.qrcode.models;

import com.aliyun.tea.*;

public class AlipayOpenAppQrcodeCreateResponse extends TeaModel {
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

    @NameInMap("qr_code_url")
    @Validation(required = true)
    public String qrCodeUrl;

    public static AlipayOpenAppQrcodeCreateResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenAppQrcodeCreateResponse self = new AlipayOpenAppQrcodeCreateResponse();
        return TeaModel.build(map, self);
    }

    public AlipayOpenAppQrcodeCreateResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayOpenAppQrcodeCreateResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayOpenAppQrcodeCreateResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayOpenAppQrcodeCreateResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayOpenAppQrcodeCreateResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayOpenAppQrcodeCreateResponse setQrCodeUrl(String qrCodeUrl) {
        this.qrCodeUrl = qrCodeUrl;
        return this;
    }
    public String getQrCodeUrl() {
        return this.qrCodeUrl;
    }

}
