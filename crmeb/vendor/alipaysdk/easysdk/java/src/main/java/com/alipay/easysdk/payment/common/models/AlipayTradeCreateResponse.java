// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayTradeCreateResponse extends TeaModel {
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

    @NameInMap("out_trade_no")
    @Validation(required = true)
    public String outTradeNo;

    @NameInMap("trade_no")
    @Validation(required = true)
    public String tradeNo;

    public static AlipayTradeCreateResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradeCreateResponse self = new AlipayTradeCreateResponse();
        return TeaModel.build(map, self);
    }

}
