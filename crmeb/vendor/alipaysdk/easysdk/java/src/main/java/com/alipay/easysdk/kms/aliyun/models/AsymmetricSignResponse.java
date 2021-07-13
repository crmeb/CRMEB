/**
 * Alipay.com Inc. Copyright (c) 2004-2019 All Rights Reserved.
 */
package com.alipay.easysdk.kms.aliyun.models;

import com.aliyun.tea.NameInMap;
import com.aliyun.tea.TeaModel;
import com.aliyun.tea.Validation;

public class AsymmetricSignResponse extends TeaModel {
    @NameInMap("Value")
    @Validation(required = true)
    public String value;

    @NameInMap("KeyId")
    @Validation(required = true)
    public String keyId;

    @NameInMap("RequestId")
    @Validation(required = true)
    public String requestId;

    @NameInMap("KeyVersionId")
    @Validation(required = true)
    public String keyVersionId;

    public static AsymmetricSignResponse build(java.util.Map<String, ?> map) throws Exception {
        AsymmetricSignResponse self = new AsymmetricSignResponse();
        return TeaModel.build(map, self);
    }
}
