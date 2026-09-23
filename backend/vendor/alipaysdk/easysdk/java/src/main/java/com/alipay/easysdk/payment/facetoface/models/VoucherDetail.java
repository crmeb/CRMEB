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

    public VoucherDetail setId(String id) {
        this.id = id;
        return this;
    }
    public String getId() {
        return this.id;
    }

    public VoucherDetail setName(String name) {
        this.name = name;
        return this;
    }
    public String getName() {
        return this.name;
    }

    public VoucherDetail setType(String type) {
        this.type = type;
        return this;
    }
    public String getType() {
        return this.type;
    }

    public VoucherDetail setAmount(String amount) {
        this.amount = amount;
        return this;
    }
    public String getAmount() {
        return this.amount;
    }

    public VoucherDetail setMerchantContribute(String merchantContribute) {
        this.merchantContribute = merchantContribute;
        return this;
    }
    public String getMerchantContribute() {
        return this.merchantContribute;
    }

    public VoucherDetail setOtherContribute(String otherContribute) {
        this.otherContribute = otherContribute;
        return this;
    }
    public String getOtherContribute() {
        return this.otherContribute;
    }

    public VoucherDetail setMemo(String memo) {
        this.memo = memo;
        return this;
    }
    public String getMemo() {
        return this.memo;
    }

    public VoucherDetail setTemplateId(String templateId) {
        this.templateId = templateId;
        return this;
    }
    public String getTemplateId() {
        return this.templateId;
    }

    public VoucherDetail setPurchaseBuyerContribute(String purchaseBuyerContribute) {
        this.purchaseBuyerContribute = purchaseBuyerContribute;
        return this;
    }
    public String getPurchaseBuyerContribute() {
        return this.purchaseBuyerContribute;
    }

    public VoucherDetail setPurchaseMerchantContribute(String purchaseMerchantContribute) {
        this.purchaseMerchantContribute = purchaseMerchantContribute;
        return this;
    }
    public String getPurchaseMerchantContribute() {
        return this.purchaseMerchantContribute;
    }

    public VoucherDetail setPurchaseAntContribute(String purchaseAntContribute) {
        this.purchaseAntContribute = purchaseAntContribute;
        return this;
    }
    public String getPurchaseAntContribute() {
        return this.purchaseAntContribute;
    }

}
