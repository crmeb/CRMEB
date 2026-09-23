// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.wap.models;

import com.aliyun.tea.*;

public class AlipayTradeWapPayResponse extends TeaModel {
    // 订单信息，Form表单形式
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayTradeWapPayResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeWapPayResponse self = new AlipayTradeWapPayResponse();
        return TeaModel.build(map, self);
    }

    public AlipayTradeWapPayResponse setBody(String body) {
        this.body = body;
        return this;
    }
    public String getBody() {
        return this.body;
    }

}
