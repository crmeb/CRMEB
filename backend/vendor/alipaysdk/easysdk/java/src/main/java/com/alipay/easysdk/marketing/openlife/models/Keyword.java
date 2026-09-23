// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class Keyword extends TeaModel {
    @NameInMap("color")
    @Validation(required = true)
    public String color;

    @NameInMap("value")
    @Validation(required = true)
    public String value;

    public static Keyword build(java.util.Map<String, ?> map) throws Exception {
        Keyword self = new Keyword();
        return TeaModel.build(map, self);
    }

    public Keyword setColor(String color) {
        this.color = color;
        return this;
    }
    public String getColor() {
        return this.color;
    }

    public Keyword setValue(String value) {
        this.value = value;
        return this;
    }
    public String getValue() {
        return this.value;
    }

}
