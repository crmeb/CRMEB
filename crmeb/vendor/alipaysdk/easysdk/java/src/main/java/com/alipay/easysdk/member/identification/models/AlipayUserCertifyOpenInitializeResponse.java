// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class AlipayUserCertifyOpenInitializeResponse extends TeaModel {
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

    @NameInMap("certify_id")
    @Validation(required = true)
    public String certifyId;

    public static AlipayUserCertifyOpenInitializeResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayUserCertifyOpenInitializeResponse self = new AlipayUserCertifyOpenInitializeResponse();
        return TeaModel.build(map, self);
    }

}
