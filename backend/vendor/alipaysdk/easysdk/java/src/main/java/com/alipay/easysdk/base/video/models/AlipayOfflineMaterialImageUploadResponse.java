// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.base.video.models;

import com.aliyun.tea.*;

public class AlipayOfflineMaterialImageUploadResponse extends TeaModel {
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

    public AlipayOfflineMaterialImageUploadResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayOfflineMaterialImageUploadResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayOfflineMaterialImageUploadResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayOfflineMaterialImageUploadResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayOfflineMaterialImageUploadResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayOfflineMaterialImageUploadResponse setImageId(String imageId) {
        this.imageId = imageId;
        return this;
    }
    public String getImageId() {
        return this.imageId;
    }

    public AlipayOfflineMaterialImageUploadResponse setImageUrl(String imageUrl) {
        this.imageUrl = imageUrl;
        return this;
    }
    public String getImageUrl() {
        return this.imageUrl;
    }

}
