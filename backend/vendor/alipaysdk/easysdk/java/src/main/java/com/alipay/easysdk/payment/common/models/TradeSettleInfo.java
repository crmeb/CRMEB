// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.common.models;

import com.aliyun.tea.*;

public class TradeSettleInfo extends TeaModel {
    @NameInMap("trade_settle_detail_list")
    @Validation(required = true)
    public java.util.List<TradeSettleDetail> tradeSettleDetailList;

    public static TradeSettleInfo build(java.util.Map<String, ?> map) throws Exception {
        TradeSettleInfo self = new TradeSettleInfo();
        return TeaModel.build(map, self);
    }

    public TradeSettleInfo setTradeSettleDetailList(java.util.List<TradeSettleDetail> tradeSettleDetailList) {
        this.tradeSettleDetailList = tradeSettleDetailList;
        return this;
    }
    public java.util.List<TradeSettleDetail> getTradeSettleDetailList() {
        return this.tradeSettleDetailList;
    }

}
