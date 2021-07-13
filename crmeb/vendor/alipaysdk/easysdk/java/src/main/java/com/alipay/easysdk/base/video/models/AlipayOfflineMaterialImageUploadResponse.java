// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.video.models;

import com.aliyun.tea.*;

public class AlipayOfflineMaterialImageUploadResponse extends TeaModel {
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

    @NameInMap("image_id")
    @Validation(required = true)
    public String imageId;

    @NameInMap("image_url")
    @Validation(required = true)
    public String imageUrl;

    public static AlipayOfflineMaterialImageUploadResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayOfflineMaterialImageUploadResponse self = new AlipayOfflineMaterialImageUploadResponse();
        return TeaModel.build(map, self);
    }

}
