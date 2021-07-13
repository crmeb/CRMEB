// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.templatemessage.models;

import com.aliyun.tea.*;

public class AlipayOpenAppMiniTemplatemessageSendResponse extends TeaModel {
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

    public static AlipayOpenAppMiniTemplatemessageSendResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenAppMiniTemplatemessageSendResponse self = new AlipayOpenAppMiniTemplatemessageSendResponse();
        return TeaModel.build(map, self);
    }

}
