// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class AlipayUserCertifyOpenQueryResponse extends TeaModel {
    @NameInMap("http_body")
    @Validation(required = true)
    public String httpBody;

    @NameInMap("code")
    @Validation(required = true)
    public String code;

    @NameInMap("msg")
    @Validation(required = true)
    public String msg;

    @NameInMap("sub_code")
    @Validation(required = true)
    public String subCode;

    @NameInMap("sub_msg")
    @Validation(required = true)
    public String subMsg;

    @NameInMap("passed")
    @Validation(required = true)
    public String passed;

    @NameInMap("identity_info")
    @Validation(required = true)
    public String identityInfo;

    @NameInMap("material_info")
    @Validation(required = true)
    public String materialInfo;

    public static AlipayUserCertifyOpenQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayUserCertifyOpenQueryResponse self = new AlipayUserCertifyOpenQueryResponse();
        return TeaModel.build(map, self);
    }

}
