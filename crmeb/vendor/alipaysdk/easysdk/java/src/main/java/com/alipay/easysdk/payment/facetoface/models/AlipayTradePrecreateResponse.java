// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.facetoface.models;

import com.aliyun.tea.*;

public class AlipayTradePrecreateResponse extends TeaModel {
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

    @NameInMap("qr_code")
    @Validation(required = true)
    public String qrCode;

    public static AlipayTradePrecreateResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayTradePrecreateResponse self = new AlipayTradePrecreateResponse();
        return TeaModel.build(map, self);
    }

}
