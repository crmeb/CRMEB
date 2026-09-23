<?php


namespace Alipay\EasySDK\Kernel;


class AlipayConstants
{
    /**
     * Config配置参数Key值
     */
    const PROTOCOL_CONFIG_KEY              = "protocol";
    const HOST_CONFIG_KEY                  = "gatewayHost";
    const ALIPAY_CERT_PATH_CONFIG_KEY      = "alipayCertPath";
    const MERCHANT_CERT_PATH_CONFIG_KEY    = "merchantCertPath";
    const ALIPAY_ROOT_CERT_PATH_CONFIG_KEY = "alipayRootCertPath";
    const SIGN_TYPE_CONFIG_KEY             = "signType";
    const NOTIFY_URL_CONFIG_KEY            = "notifyUrl";

    /**
     * 与网关HTTP交互中涉及到的字段值
     */
    const BIZ_CONTENT_FIELD    = "biz_content";
    const ALIPAY_CERT_SN_FIELD = "alipay_cert_sn";
    const SIGN_FIELD           = "sign";
    const BODY_FIELD           = "http_body";
    const NOTIFY_URL_FIELD     = "notify_url";
    const METHOD_FIELD         = "method";
    const RESPONSE_SUFFIX      = "_response";
    const ERROR_RESPONSE       = "error_response";
    const SDK_VERSION          = "alipay-easysdk-php-2.2.3";

    /**
     * 默认字符集编码，EasySDK统一固定使用UTF-8编码，无需用户感知编码，用户面对的总是String而不是bytes
     */
    const DEFAULT_CHARSET = "UTF-8";

    /**
     * 默认的签名算法，EasySDK统一固定使用RSA2签名算法（即SHA_256_WITH_RSA），但此参数依然需要用户指定以便用户感知，因为在开放平台接口签名配置界面中需要选择同样的算法
     */
    const RSA2 = "RSA2";

    /**
     * RSA2对应的真实签名算法名称
     */
    const SHA_256_WITH_RSA = "SHA256WithRSA";

    /**
     * RSA2对应的真实非对称加密算法名称
     */
    const RSA = "RSA";

    /**
     * 申请生成的重定向网页的请求类型，GET表示生成URL
     */
    const GET = "GET";

    /**
     * 申请生成的重定向网页的请求类型，POST表示生成form表单
     */
    const POST = "POST";

}