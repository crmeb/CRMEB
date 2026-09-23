using System;
using System.Text;

namespace Alipay.EasySDK.Kernel
{
    /// <summary>
    /// 支付宝开放平台网关交互常用常量
    /// </summary>
    public static class AlipayConstants
    {
        /// <summary>
        /// Config配置参数Key值
        /// </summary>
        public const string PROTOCOL_CONFIG_KEY = "protocol";
        public const string HOST_CONFIG_KEY = "gatewayHost";
        public const string ALIPAY_CERT_PATH_CONFIG_KEY = "alipayCertPath";
        public const string MERCHANT_CERT_PATH_CONFIG_KEY = "merchantCertPath";
        public const string ALIPAY_ROOT_CERT_PATH_CONFIG_KEY = "alipayRootCertPath";
        public const string SIGN_TYPE_CONFIG_KEY = "signType";
        public const string NOTIFY_URL_CONFIG_KEY = "notifyUrl";

        /// <summary>
        /// 与网关HTTP交互中涉及到的字段值
        /// </summary>
        public const string BIZ_CONTENT_FIELD = "biz_content";
        public const string ALIPAY_CERT_SN_FIELD = "alipay_cert_sn";
        public const string SIGN_FIELD = "sign";
        public const string SIGN_TYPE_FIELD = "sign_type";
        public const string BODY_FIELD = "http_body";
        public const string NOTIFY_URL_FIELD = "notify_url";
        public const string METHOD_FIELD = "method";
        public const string RESPONSE_SUFFIX = "_response";
        public const string ERROR_RESPONSE = "error_response";

        /// <summary>
        /// 默认字符集编码，EasySDK统一固定使用UTF-8编码，无需用户感知编码，用户面对的总是String而不是bytes
        /// </summary>
        public readonly static Encoding DEFAULT_CHARSET = Encoding.UTF8;

        /// <summary>
        /// 默认的签名算法，EasySDK统一固定使用RSA2签名算法（即SHA_256_WITH_RSA），但此参数依然需要用户指定以便用户感知，因为在开放平台接口签名配置界面中需要选择同样的算法
        /// </summary>
        public const string RSA2 = "RSA2";

        /// <summary>
        /// RSA2对应的真实签名算法名称
        /// </summary>
        public const string SHA_256_WITH_RSA = "SHA256WithRSA";

        /// <summary>
        /// RSA2对应的真实非对称加密算法名称
        /// </summary>
        public const string RSA = "RSA";

        /// <summary>
        /// 申请生成的重定向网页的请求类型，GET表示生成URL
        /// </summary>
        public const string GET = "GET";

        /// <summary>
        /// 申请生成的重定向网页的请求类型，POST表示生成form表单
        /// </summary>
        public const string POST = "POST";
    }
}
