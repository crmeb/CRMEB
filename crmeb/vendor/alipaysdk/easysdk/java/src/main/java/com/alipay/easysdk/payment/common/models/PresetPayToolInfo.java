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

}
