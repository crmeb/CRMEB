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

    public Article setTitle(String title) {
        this.title = title;
        return this;
    }
    public String getTitle() {
        return this.title;
    }

    public Article setDesc(String desc) {
        this.desc = desc;
        return this;
    }
    public String getDesc() {
        return this.desc;
    }

    public Article setImageUrl(String imageUrl) {
        this.imageUrl = imageUrl;
        return this;
    }
    public String getImageUrl() {
        return this.imageUrl;
    }

    public Article setUrl(String url) {
        this.url = url;
        return this;
    }
    public String getUrl() {
        return this.url;
    }

    public Article setActionName(String actionName) {
        this.actionName = actionName;
        return this;
    }
    public String getActionName() {
        return this.actionName;
    }

}
