// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class TradeFundBill extends TeaModel {
    @NameInMap("fund_channel")
    @Validation(required = true)
    public String fundChannel;

    @NameInMap("bank_code")
    @Validation(required = true)
    public String bankCode;

    @NameInMap("amount")
    @Validation(required = true)
    public String amount;

    @NameInMap("real_amount")
    @Validation(required = true)
    public String realAmount;

    @NameInMap("fund_type")
    @Validation(required = true)
    public String fundType;

    public static TradeFundBill build(java.util.Map<String, ?> map) throws Exception {
        TradeFundBill self = new TradeFundBill();
        return TeaModel.build(map, self);
    }

}
