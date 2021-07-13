// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.security.textrisk.models;

import com.aliyun.tea.*;

public class AlipaySecurityRiskContentDetectResponse extends TeaModel {
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

    @NameInMap("action")
    @Validation(required = true)
    public String action;

    @NameInMap("keywords")
    @Validation(required = true)
    public java.util.List<String> keywords;

    @NameInMap("unique_id")
    @Validation(required = true)
    public String uniqueId;

    public static AlipaySecurityRiskContentDetectResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipaySecurityRiskContentDetectResponse self = new AlipaySecurityRiskContentDetectResponse();
        return TeaModel.build(map, self);
    }

}
