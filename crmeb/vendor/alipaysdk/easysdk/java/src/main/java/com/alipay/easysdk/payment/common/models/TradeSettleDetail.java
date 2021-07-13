// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class TradeSettleDetail extends TeaModel {
    @NameInMap("operation_type")
    @Validation(required = true)
    public String operationType;

    @NameInMap("operation_serial_no")
    @Validation(required = true)
    public String operationSerial_no;

    @NameInMap("operation_dt")
    @Validation(required = true)
    public String operationDt;

    @NameInMap("trans_out")
    @Validation(required = true)
    public String transOut;

    @NameInMap("trans_in")
    @Validation(required = true)
    public String transIn;

    @NameInMap("amount")
    @Validation(required = true)
    public String amount;

    public static TradeSettleDetail build(java.util.Map<String, ?> map) throws Exception {
        TradeSettleDetail self = new TradeSettleDetail();
        return TeaModel.build(map, self);
    }

}
