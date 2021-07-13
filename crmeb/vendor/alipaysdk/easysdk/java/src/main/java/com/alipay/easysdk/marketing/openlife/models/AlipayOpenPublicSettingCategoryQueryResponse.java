// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class AlipayOpenPublicSettingCategoryQueryResponse extends TeaModel {
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

    @NameInMap("primary_category")
    @Validation(required = true)
    public String primaryCategory;

    @NameInMap("secondary_category")
    @Validation(required = true)
    public String secondaryCategory;

    public static AlipayOpenPublicSettingCategoryQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOpenPublicSettingCategoryQueryResponse self = new AlipayOpenPublicSettingCategoryQueryResponse();
        return TeaModel.build(map, self);
    }

}
