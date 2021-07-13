// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.qrcode.models;

import com.aliyun.tea.*;

public class AlipayOpenAppQrcodeCreateResponse extends TeaModel {
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

}
