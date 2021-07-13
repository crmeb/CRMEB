// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class Article extends TeaModel {
    @NameInMap("title")
    public String title;

    @NameInMap("desc")
    @Validation(required = true)
    public String desc;

    @NameInMap("image_url")
    public String imageUrl;

    @NameInMap("url")
    @Validation(required = true)
    public String url;

    @NameInMap("action_name")
    public String actionName;

    public static Article build(java.util.Map<String, ?> map) throws Exception {
        Article self = new Article();
        return TeaModel.build(map, self);
    }

}
