// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.payment.facetoface.models;

import com.aliyun.tea.*;

public class VoucherDetail extends TeaModel {
    @NameInMap("id")
    @Validation(required = true)
    public String id;

    @NameInMap("name")
    @Validation(required = true)
    public String name;

    @NameInMap("type")
    @Validation(required = true)
    public String type;

    @NameInMap("amount")
    @Validation(required = true)
    public String amount;

    @NameInMap("merchant_contribute")
    @Validation(required = true)
    public String merchantContribute;

    @NameInMap("other_contribute")
    @Validation(required = true)
    public String otherContribute;

    @NameInMap("memo")
    @Validation(required = true)
    public String memo;

    @NameInMap("template_id")
    @Validation(required = true)
    public String templateId;

    @NameInMap("purchase_buyer_contribute")
    @Validation(required = true)
    public String purchaseBuyerContribute;

    @NameInMap("purchase_merchant_contribute")
    @Validation(required = true)
    public String purchaseMerchantContribute;

    @NameInMap("purchase_ant_contribute")
    @Validation(required = true)
    public String purchaseAntContribute;

    public static VoucherDetail build(java.util.Map<String, ?> map) throws Exception {
        VoucherDetail self = new VoucherDetail();
        return TeaModel.build(map, self);
    }

}
