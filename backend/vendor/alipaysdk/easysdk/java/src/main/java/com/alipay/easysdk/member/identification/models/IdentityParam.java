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

    public IdentityParam setIdentityType(String identityType) {
        this.identityType = identityType;
        return this;
    }
    public String getIdentityType() {
        return this.identityType;
    }

    public IdentityParam setCertType(String certType) {
        this.certType = certType;
        return this;
    }
    public String getCertType() {
        return this.certType;
    }

    public IdentityParam setCertName(String certName) {
        this.certName = certName;
        return this;
    }
    public String getCertName() {
        return this.certName;
    }

    public IdentityParam setCertNo(String certNo) {
        this.certNo = certNo;
        return this;
    }
    public String getCertNo() {
        return this.certNo;
    }

}
