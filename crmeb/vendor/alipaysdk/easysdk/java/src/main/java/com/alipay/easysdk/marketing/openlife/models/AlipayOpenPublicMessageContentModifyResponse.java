// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class AlipayOpenPublicMessageContentModifyResponse extends TeaModel {
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

    @NameInMap("content_id")
    @Validation(required = true)
    public String contentId;

    @NameInMap("content_url")
    @Validation(required = true)
    public String contentUrl;

    public static AlipayOpenPublicMessageContentModifyResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenPublicMessageContentModifyResponse self = new AlipayOpenPublicMessageContentModifyResponse();
        return TeaModel.build(map, self);
    }

}
