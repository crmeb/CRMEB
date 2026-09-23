// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class TradeSettleDetail extends TeaModel {
    @NameInMap("operation_type")
    @Validation(required = true)
    public String operationType;

    @NameInMap("operation_serial_no")
    @Validation(required = true)
    public String operationSerial_no;

    @NameInMap("operation_dt")
    @Validation(required = true)
    public String operationDt;

    @NameInMap("trans_out")
    @Validation(required = true)
    public String transOut;

    @NameInMap("trans_in")
    @Validation(required = true)
    public String transIn;

    @NameInMap("amount")
    @Validation(required = true)
    public String amount;

    public static TradeSettleDetail build(java.util.Map<String, ?> map) throws Exception {
        TradeSettleDetail self = new TradeSettleDetail();
        return TeaModel.build(map, self);
    }

    public TradeSettleDetail setOperationType(String operationType) {
        this.operationType = operationType;
        return this;
    }
    public String getOperationType() {
        return this.operationType;
    }

    public TradeSettleDetail setOperationSerial_no(String operationSerial_no) {
        this.operationSerial_no = operationSerial_no;
        return this;
    }
    public String getOperationSerial_no() {
        return this.operationSerial_no;
    }

    public TradeSettleDetail setOperationDt(String operationDt) {
        this.operationDt = operationDt;
        return this;
    }
    public String getOperationDt() {
        return this.operationDt;
    }

    public TradeSettleDetail setTransOut(String transOut) {
        this.transOut = transOut;
        return this;
    }
    public String getTransOut() {
        return this.transOut;
    }

    public TradeSettleDetail setTransIn(String transIn) {
        this.transIn = transIn;
        return this;
    }
    public String getTransIn() {
        return this.transIn;
    }

    public TradeSettleDetail setAmount(String amount) {
        this.amount = amount;
        return this;
    }
    public String getAmount() {
        return this.amount;
    }

}
