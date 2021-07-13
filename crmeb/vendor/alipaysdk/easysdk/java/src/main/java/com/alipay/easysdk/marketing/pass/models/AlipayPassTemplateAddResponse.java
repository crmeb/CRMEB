// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.pass.models;

import com.aliyun.tea.*;

public class AlipayPassTemplateAddResponse extends TeaModel {
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

    @NameInMap("success")
    @Validation(required = true)
    public Boolean success;

    @NameInMap("result")
    @Validation(required = true)
    public String result;

    public static AlipayPassTemplateAddResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayPassTemplateAddResponse self = new AlipayPassTemplateAddResponse();
        return TeaModel.build(map, self);
    }

}
