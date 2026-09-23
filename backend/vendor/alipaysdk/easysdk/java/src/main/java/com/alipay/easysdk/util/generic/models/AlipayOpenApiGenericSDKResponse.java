// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.util.generic.models;

import com.aliyun.tea.*;

public class AlipayOpenApiGenericSDKResponse extends TeaModel {
    // 订单信息，字符串形式
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayOpenApiGenericSDKResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenApiGenericSDKResponse self = new AlipayOpenApiGenericSDKResponse();
        return TeaModel.build(map, self);
    }

    public AlipayOpenApiGenericSDKResponse setBody(String body) {
        this.body = body;
        return this;
    }
    public String getBody() {
        return this.body;
    }

}
