// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class Context extends TeaModel {
    @NameInMap("head_color")
    @Validation(required = true)
    public String headColor;

    @NameInMap("url")
    @Validation(required = true)
    public String url;

    @NameInMap("action_name")
    @Validation(required = true)
    public String actionName;

    @NameInMap("keyword1")
    public Keyword keyword1;

    @NameInMap("keyword2")
    public Keyword keyword2;

    @NameInMap("first")
    public Keyword first;

    @NameInMap("remark")
    public Keyword remark;

    public static Context build(java.util.Map<String, ?> map) throws Exception {
        Context self = new Context();
        return TeaModel.build(map, self);
    }

}
