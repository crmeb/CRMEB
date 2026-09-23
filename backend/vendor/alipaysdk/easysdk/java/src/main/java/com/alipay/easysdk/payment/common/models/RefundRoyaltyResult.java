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

    public RefundRoyaltyResult setRefundAmount(String refundAmount) {
        this.refundAmount = refundAmount;
        return this;
    }
    public String getRefundAmount() {
        return this.refundAmount;
    }

    public RefundRoyaltyResult setRoyaltyType(String royaltyType) {
        this.royaltyType = royaltyType;
        return this;
    }
    public String getRoyaltyType() {
        return this.royaltyType;
    }

    public RefundRoyaltyResult setResultCode(String resultCode) {
        this.resultCode = resultCode;
        return this;
    }
    public String getResultCode() {
        return this.resultCode;
    }

    public RefundRoyaltyResult setTransOut(String transOut) {
        this.transOut = transOut;
        return this;
    }
    public String getTransOut() {
        return this.transOut;
    }

    public RefundRoyaltyResult setTransOutEmail(String transOutEmail) {
        this.transOutEmail = transOutEmail;
        return this;
    }
    public String getTransOutEmail() {
        return this.transOutEmail;
    }

    public RefundRoyaltyResult setTransIn(String transIn) {
        this.transIn = transIn;
        return this;
    }
    public String getTransIn() {
        return this.transIn;
    }

    public RefundRoyaltyResult setTransInEmail(String transInEmail) {
        this.transInEmail = transInEmail;
        return this;
    }
    public String getTransInEmail() {
        return this.transInEmail;
    }

}
