package com.alipay.easysdk.kms.aliyun.models;

import com.aliyun.tea.*;

public class GetPublicKeyRequest extends TeaModel {
    @NameInMap("KeyId")
    @Validation(required = true)
    public String keyId;

    @NameInMap("KeyVersionId")
    @Validation(required = true)
    public String keyVersionId;

    public static GetPublicKeyRequest build(java.util.Map<String, ?> map) throws Exception {
        GetPublicKeyRequest self = new GetPublicKeyRequest();
        return TeaModel.build(map, self);
    }
}
