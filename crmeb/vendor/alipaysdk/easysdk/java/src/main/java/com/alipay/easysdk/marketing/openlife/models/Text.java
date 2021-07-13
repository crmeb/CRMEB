// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.marketing.openlife.models;

import com.aliyun.tea.*;

public class Text extends TeaModel {
    @NameInMap("title")
    @Validation(required = true)
    public String title;

    @NameInMap("content")
    @Validation(required = true)
    public String content;

    public static Text build(java.util.Map<String, ?> map) throws Exception {
        Text self = new Text();
        return TeaModel.build(map, self);
    }

}
