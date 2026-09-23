package com.alipay.easysdk.kms.aliyun.models;

import com.aliyun.tea.*;

public class GetPublicKeyResponse extends TeaModel {
    @NameInMap("PublicKey")
    @Validation(required = true)
    public String publicKey;

    @NameInMap("KeyId")
    @Validation(required = true)
    public String keyId;

    @NameInMap("RequestId")
    @Validation(required = true)
    public String requestId;

    @NameInMap("KeyVersionId")
    @Validation(required = true)
    public String keyVersionId;

    public static GetPublicKeyResponse build(java.util.Map<String, ?> map) throws Exception {
        GetPublicKeyResponse self = new GetPublicKeyResponse();
        return TeaModel.build(map, self);
    }
}
