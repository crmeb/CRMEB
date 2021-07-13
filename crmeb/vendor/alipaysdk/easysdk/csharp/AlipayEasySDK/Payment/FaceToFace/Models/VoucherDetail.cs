// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections.Generic;
using System.IO;

using Tea;

namespace Alipay.EasySDK.Payment.FaceToFace.Models
{
    public class VoucherDetail : TeaModel {
        [NameInMap("id")]
        [Validation(Required=true)]
        public string Id { get; set; }

        [NameInMap("name")]
        [Validation(Required=true)]
        public string Name { get; set; }

        [NameInMap("type")]
        [Validation(Required=true)]
        public string Type { get; set; }

        [NameInMap("amount")]
        [Validation(Required=true)]
        public string Amount { get; set; }

        [NameInMap("merchant_contribute")]
        [Validation(Required=true)]
        public string MerchantContribute { get; set; }

        [NameInMap("other_contribute")]
        [Validation(Required=true)]
        public string OtherContribute { get; set; }

        [NameInMap("memo")]
        [Validation(Required=true)]
        public string Memo { get; set; }

        [NameInMap("template_id")]
        [Validation(Required=true)]
        public string TemplateId { get; set; }

        [NameInMap("purchase_buyer_contribute")]
        [Validation(Required=true)]
        public string PurchaseBuyerContribute { get; set; }

        [NameInMap("purchase_merchant_contribute")]
        [Validation(Required=true)]
        public string PurchaseMerchantContribute { get; set; }

        [NameInMap("purchase_ant_contribute")]
        [Validation(Required=true)]
        public string PurchaseAntContribute { get; set; }

    }

}
