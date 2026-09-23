// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class Template extends TeaModel {
    @NameInMap("template_id")
    @Validation(required = true)
    public String templateId;

    @NameInMap("context")
    @Validation(required = true)
    public Context context;

    public static Template build(java.util.Map<String, ?> map) throws Exception {
        Template self = new Template();
        return TeaModel.build(map, self);
    }

    public Template setTemplateId(String templateId) {
        this.templateId = templateId;
        return this;
    }
    public String getTemplateId() {
        return this.templateId;
    }

    public Template setContext(Context context) {
        this.context = context;
        return this;
    }
    public Context getContext() {
        return this.context;
    }

}
