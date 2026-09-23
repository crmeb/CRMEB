// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.app.models;

import com.aliyun.tea.*;

public class AlipayTradeAppPayResponse extends TeaModel {
    // 订单信息，字符串形式
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayTradeAppPayResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeAppPayResponse self = new AlipayTradeAppPayResponse();
        return TeaModel.build(map, self);
    }

    public AlipayTradeAppPayResponse setBody(String body) {
        this.body = body;
        return this;
    }
    public String getBody() {
        return this.body;
    }

}
