// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.member.identification.models;

import com.aliyun.tea.*;

public class AlipayUserCertifyOpenCertifyResponse extends TeaModel {
    // 认证服务请求地址
    @NameInMap("body")
    @Validation(required = true)
    public String body;

    public static AlipayUserCertifyOpenCertifyResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayUserCertifyOpenCertifyResponse self = new AlipayUserCertifyOpenCertifyResponse();
        return TeaModel.build(map, self);
    }

    public AlipayUserCertifyOpenCertifyResponse setBody(String body) {
        this.body = body;
        return this;
    }
    public String getBody() {
        return this.body;
    }

}
