// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class AlipayUserCertifyOpenCertifyResponse extends TeaModel {
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayUserCertifyOpenCertifyResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayUserCertifyOpenCertifyResponse self = new AlipayUserCertifyOpenCertifyResponse();
        return TeaModel.build(map, self);
    }

}
