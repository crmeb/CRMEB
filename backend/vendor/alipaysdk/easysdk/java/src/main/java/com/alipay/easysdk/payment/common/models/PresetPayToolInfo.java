// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class PresetPayToolInfo extends TeaModel {
    @NameInMap("amount")
    @Validation(required = true)
    public java.util.List<String> amount;

    @NameInMap("assert_type_code")
    @Validation(required = true)
    public String assertTypeCode;

    public static PresetPayToolInfo build(java.util.Map<String, ?> map) throws Exception {
        PresetPayToolInfo self = new PresetPayToolInfo();
        return TeaModel.build(map, self);
    }

    public PresetPayToolInfo setAmount(java.util.List<String> amount) {
        this.amount = amount;
        return this;
    }
    public java.util.List<String> getAmount() {
        return this.amount;
    }

    public PresetPayToolInfo setAssertTypeCode(String assertTypeCode) {
        this.assertTypeCode = assertTypeCode;
        return this;
    }
    public String getAssertTypeCode() {
        return this.assertTypeCode;
    }

}
