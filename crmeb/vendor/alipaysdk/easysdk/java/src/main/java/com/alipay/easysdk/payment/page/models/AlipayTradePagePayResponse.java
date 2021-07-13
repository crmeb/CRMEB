// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.page.models;

import com.aliyun.tea.*;

public class AlipayTradePagePayResponse extends TeaModel {
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayTradePagePayResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradePagePayResponse self = new AlipayTradePagePayResponse();
        return TeaModel.build(map, self);
    }

}
