// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayDataDataserviceBillDownloadurlQueryResponse extends TeaModel {
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

    @NameInMap("bill_download_url")
    @Validation(required = true)
    public String billDownloadUrl;

    public static AlipayDataDataserviceBillDownloadurlQueryResponse build(java.util.Map<String, ?> map) throws Exception {
        AlipayDataDataserviceBillDownloadurlQueryResponse self = new AlipayDataDataserviceBillDownloadurlQueryResponse();
        return TeaModel.build(map, self);
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setHttpBody(String httpBody) {
        this.httpBody = httpBody;
        return this;
    }
    public String getHttpBody() {
        return this.httpBody;
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setCode(String code) {
        this.code = code;
        return this;
    }
    public String getCode() {
        return this.code;
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setMsg(String msg) {
        this.msg = msg;
        return this;
    }
    public String getMsg() {
        return this.msg;
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setSubCode(String subCode) {
        this.subCode = subCode;
        return this;
    }
    public String getSubCode() {
        return this.subCode;
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setSubMsg(String subMsg) {
        this.subMsg = subMsg;
        return this;
    }
    public String getSubMsg() {
        return this.subMsg;
    }

    public AlipayDataDataserviceBillDownloadurlQueryResponse setBillDownloadUrl(String billDownloadUrl) {
        this.billDownloadUrl = billDownloadUrl;
        return this;
    }
    public String getBillDownloadUrl() {
        return this.billDownloadUrl;
    }

}
