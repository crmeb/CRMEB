using Tea;

namespace Alipay.EasySDK.Kernel
{
    /// <summary>
    /// 客户端配置参数模型
    /// </summary>
    public class Config : TeaModel
    {
        /// <summary>
        /// 通信协议，通常填写https
        /// </summary>
        [NameInMap("protocol")]
        [Validation(Required = true)]
        public string Protocol { get; set; } = "https";

        /// <summary>
        /// 网关域名
        /// 线上为：openapi.alipay.com
        /// 沙箱为：openapi.alipaydev.com
        /// </summary>
        [NameInMap("gatewayHost")]
        [Validation(Required = true)]
        public string GatewayHost { get; set; } = "openapi.alipay.com";

        /// <summary>
        /// AppId
        /// </summary>
        [NameInMap("appId")]
        [Validation(Required = true)]
        public string AppId { get; set; }

        /// <summary>
        /// 签名类型，Alipay Easy SDK只推荐使用RSA2，估此处固定填写RSA2
        /// </summary>
        [NameInMap("signType")]
        [Validation(Required = true)]
        public string SignType { get; set; } = "RSA2";

        /// <summary>
        /// 支付宝公钥
        /// </summary>
        [NameInMap("alipayPublicKey")]
        [Validation(Required = true)]
        public string AlipayPublicKey { get; set; }

        /// <summary>
        /// 应用私钥
        /// </summary>
        [NameInMap("merchantPrivateKey")]
        [Validation(Required = true)]
        public string MerchantPrivateKey { get; set; }

        /// <summary>
        /// 应用公钥证书文件路径
        /// </summary>
        [NameInMap("merchantCertPath")]
        [Validation(Required = true)]
        public string MerchantCertPath { get; set; }

        /// <summary>
        /// 支付宝公钥证书文件路径
        /// </summary>
        [NameInMap("alipayCertPath")]
        [Validation(Required = true)]
        public string AlipayCertPath { get; set; }

        /// <summary>
        /// 支付宝根证书文件路径
        /// </summary>
        [NameInMap("alipayRootCertPath")]
        [Validation(Required = true)]
        public string AlipayRootCertPath { get; set; }

        /// <summary>
        /// 异步通知回调地址（可选）
        /// </summary>
        [NameInMap("notifyUrl")]
        [Validation(Required = true)]
        public string NotifyUrl { get; set; }

        /// <summary>
        /// AES密钥（可选）
        /// </summary>
        [NameInMap("encryptKey")]
        [Validation(Required = true)]
        public string EncryptKey { get; set; }
    }
}
