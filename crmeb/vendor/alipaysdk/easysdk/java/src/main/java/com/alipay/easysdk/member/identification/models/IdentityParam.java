// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class IdentityParam extends TeaModel {
    @NameInMap("identity_type")
    @Validation(required = true)
    public String identityType;

    @NameInMap("cert_type")
    @Validation(required = true)
    public String certType;

    @NameInMap("cert_name")
    @Validation(required = true)
    public String certName;

    @NameInMap("cert_no")
    @Validation(required = true)
    public String certNo;

    public static IdentityParam build(java.util.Map<String, ?> map) throws Exception {
        IdentityParam self = new IdentityParam();
        return TeaModel.build(map, self);
    }

}
