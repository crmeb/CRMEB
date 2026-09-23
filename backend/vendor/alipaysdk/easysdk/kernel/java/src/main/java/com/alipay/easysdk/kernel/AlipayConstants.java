/**
 * Alipay.com Inc. Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel;

import java.nio.charset.Charset;
import java.nio.charset.StandardCharsets;

/**
 * 支付宝开放平台网关交互常用常量
 *
 * @author zhongyu
 * @version $Id: AlipayConstants.java, v 0.1 2020年01月02日 7:53 PM zhongyu Exp $
 */
public final class AlipayConstants {
    /**
     * Config配置参数Key值
     */
    public static final String PROTOCOL_CONFIG_KEY              = "protocol";
    public static final String HOST_CONFIG_KEY                  = "gatewayHost";
    public static final String ALIPAY_CERT_PATH_CONFIG_KEY      = "alipayCertPath";
    public static final String MERCHANT_CERT_PATH_CONFIG_KEY    = "merchantCertPath";
    public static final String ALIPAY_ROOT_CERT_PATH_CONFIG_KEY = "alipayRootCertPath";
    public static final String SIGN_TYPE_CONFIG_KEY             = "signType";
    public static final String NOTIFY_URL_CONFIG_KEY            = "notifyUrl";
    public static final String SIGN_PROVIDER_CONFIG_KEY         = "signProvider";

    /**
     * 与网关HTTP交互中涉及到的字段值
     */
    public static final String BIZ_CONTENT_FIELD    = "biz_content";
    public static final String ALIPAY_CERT_SN_FIELD = "alipay_cert_sn";
    public static final String SIGN_FIELD           = "sign";
    public static final String SIGN_TYPE_FIELD      = "sign_type";
    public static final String BODY_FIELD           = "http_body";
    public static final String NOTIFY_URL_FIELD     = "notify_url";
    public static final String METHOD_FIELD         = "method";
    public static final String RESPONSE_SUFFIX      = "_response";
    public static final String ERROR_RESPONSE       = "error_response";

    /**
     * 默认字符集编码，EasySDK统一固定使用UTF-8编码，无需用户感知编码，用户面对的总是String而不是bytes
     */
    public static final Charset DEFAULT_CHARSET = StandardCharsets.UTF_8;

    /**
     * 默认的签名算法，EasySDK统一固定使用RSA2签名算法（即SHA_256_WITH_RSA），但此参数依然需要用户指定以便用户感知，因为在开放平台接口签名配置界面中需要选择同样的算法
     */
    public static final String RSA2 = "RSA2";

    /**
     * RSA2对应的真实签名算法名称
     */
    public static final String SHA_256_WITH_RSA = "SHA256WithRSA";

    /**
     * RSA2对应的真实非对称加密算法名称
     */
    public static final String RSA = "RSA";

    /**
     * 申请生成的重定向网页的请求类型，GET表示生成URL
     */
    public static final String GET = "GET";

    /**
     * 申请生成的重定向网页的请求类型，POST表示生成form表单
     */
    public static final String POST = "POST";

    /**
     * 使用Aliyun KMS签名服务时签名提供方的名称
     */
    public static final String AliyunKMS = "AliyunKMS";
}