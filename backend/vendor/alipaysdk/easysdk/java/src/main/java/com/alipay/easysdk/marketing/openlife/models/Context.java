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

    public Context setHeadColor(String headColor) {
        this.headColor = headColor;
        return this;
    }
    public String getHeadColor() {
        return this.headColor;
    }

    public Context setUrl(String url) {
        this.url = url;
        return this;
    }
    public String getUrl() {
        return this.url;
    }

    public Context setActionName(String actionName) {
        this.actionName = actionName;
        return this;
    }
    public String getActionName() {
        return this.actionName;
    }

    public Context setKeyword1(Keyword keyword1) {
        this.keyword1 = keyword1;
        return this;
    }
    public Keyword getKeyword1() {
        return this.keyword1;
    }

    public Context setKeyword2(Keyword keyword2) {
        this.keyword2 = keyword2;
        return this;
    }
    public Keyword getKeyword2() {
        return this.keyword2;
    }

    public Context setFirst(Keyword first) {
        this.first = first;
        return this;
    }
    public Keyword getFirst() {
        return this.first;
    }

    public Context setRemark(Keyword remark) {
        this.remark = remark;
        return this;
    }
    public Keyword getRemark() {
        return this.remark;
    }

}
