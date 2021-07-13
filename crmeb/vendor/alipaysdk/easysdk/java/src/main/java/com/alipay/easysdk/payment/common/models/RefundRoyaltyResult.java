// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class RefundRoyaltyResult extends TeaModel {
    @NameInMap("refund_amount")
    @Validation(required = true)
    public String refundAmount;

    @NameInMap("royalty_type")
    @Validation(required = true)
    public String royaltyType;

    @NameInMap("result_code")
    @Validation(required = true)
    public String resultCode;

    @NameInMap("trans_out")
    @Validation(required = true)
    public String transOut;

    @NameInMap("trans_out_email")
    @Validation(required = true)
    public String transOutEmail;

    @NameInMap("trans_in")
    @Validation(required = true)
    public String transIn;

    @NameInMap("trans_in_email")
    @Validation(required = true)
    public String transInEmail;

    public static RefundRoyaltyResult build(java.util.Map<String, ?> map) throws Exception {
        RefundRoyaltyResult self = new RefundRoyaltyResult();
        return TeaModel.build(map, self);
    }

}
