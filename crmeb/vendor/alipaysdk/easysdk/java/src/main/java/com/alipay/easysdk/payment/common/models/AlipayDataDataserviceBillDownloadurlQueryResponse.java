// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class AlipayDataDataserviceBillDownloadurlQueryResponse extends TeaModel {
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

}
