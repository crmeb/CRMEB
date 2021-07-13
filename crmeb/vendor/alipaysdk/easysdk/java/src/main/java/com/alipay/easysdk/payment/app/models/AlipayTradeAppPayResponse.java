// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.app.models;

import com.aliyun.tea.*;

public class AlipayTradeAppPayResponse extends TeaModel {
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayTradeAppPayResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeAppPayResponse self = new AlipayTradeAppPayResponse();
        return TeaModel.build(map, self);
    }

}
