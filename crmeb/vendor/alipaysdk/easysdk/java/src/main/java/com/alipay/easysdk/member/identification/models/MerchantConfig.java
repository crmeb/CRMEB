// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class MerchantConfig extends TeaModel {
    @NameInMap("return_url")
    @Validation(required = true)
    public String returnUrl;

    public static MerchantConfig build(java.util.Map<String, ?> map) throws Exception {
        MerchantConfig self = new MerchantConfig();
        return TeaModel.build(map, self);
    }

}
