// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class AlipayOpenPublicSettingCategoryQueryResponse extends TeaModel {
    // 响应原始字符串
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

    public AlipayOpenPublicSettingCategoryQueryResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setPrimaryCategory(String primaryCategory) {
        this.primaryCategory = primaryCategory;
        return this;
    }
    public String getPrimaryCategory() {
        return this.primaryCategory;
    }

    public AlipayOpenPublicSettingCategoryQueryResponse setSecondaryCategory(String secondaryCategory) {
        this.secondaryCategory = secondaryCategory;
        return this;
    }
    public String getSecondaryCategory() {
        return this.secondaryCategory;
    }

}
