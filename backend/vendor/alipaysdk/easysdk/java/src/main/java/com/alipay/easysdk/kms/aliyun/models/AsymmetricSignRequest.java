package com.alipay.easysdk.kms.aliyun.models;

import com.aliyun.tea.*;

public class AsymmetricSignRequest extends TeaModel {
    @NameInMap("KeyId")
    @Validation(required = true)
    public String keyId;

    @NameInMap("KeyVersionId")
    @Validation(required = true)
    public String keyVersionId;

    @NameInMap("Algorithm")
    @Validation(required = true)
    public String algorithm;

    @NameInMap("Digest")
    @Validation(required = true)
    public String digest;

    public static AsymmetricSignRequest build(java.util.Map<String, ?> map) throws Exception {
        AsymmetricSignRequest self = new AsymmetricSignRequest();
        return TeaModel.build(map, self);
    }
}