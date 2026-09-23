/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel;

import com.aliyun.tea.NameInMap;
import com.aliyun.tea.TeaModel;
import com.aliyun.tea.Validation;

/**
 * @author zhongyu
 * @version : Config.java, v 0.1 2020年05月22日 4:25 下午 zhongyu Exp $
 */
public class Config extends TeaModel {

    /**
     * 通信协议，通常填写https
     */
    @NameInMap("protocol")
    @Validation(required = true)
    public String protocol;

    /**
     * 网关域名
     * 线上为：openapi.alipay.com
     * 沙箱为：openapi.alipaydev.com
     */
    @NameInMap("gatewayHost")
    @Validation(required = true)
    public String gatewayHost;

    /**
     * AppId
     */
    @NameInMap("appId")
    @Validation(required = true)
    public String appId;

    /**
     * 签名类型，Alipay Easy SDK只推荐使用RSA2，估此处固定填写RSA2
     */
    @NameInMap("signType")
    @Validation(required = true)
    public String signType;

    /**
     * 支付宝公钥
     */
    @NameInMap("alipayPublicKey")
    public String alipayPublicKey;

    /**
     * 应用私钥
     */
    @NameInMap("merchantPrivateKey")
    @Validation(required = true)
    public String merchantPrivateKey;

    /**
     * 应用公钥证书文件路径
     */
    @NameInMap("merchantCertPath")
    public String merchantCertPath;

    /**
     * 支付宝公钥证书文件路径
     */
    @NameInMap("alipayCertPath")
    public String alipayCertPath;

    /**
     * 支付宝根证书文件路径
     */
    @NameInMap("alipayRootCertPath")
    public String alipayRootCertPath;

    /**
     * 异步通知回调地址（可选）
     */
    @NameInMap("notifyUrl")
    public String notifyUrl;

    /**
     * AES密钥（可选）
     */
    @NameInMap("encryptKey")
    public String encryptKey;

    /**
     * 签名提供方的名称(可选)，例：Aliyun KMS签名，signProvider = "AliyunKMS"
     */
    @NameInMap("signProvider")
    public String signProvider;

    /**
     * 代理地址（可选）
     * 例如：http://127.0.0.1:8080
     */
    @NameInMap("httpProxy")
    public String httpProxy;


    /**
     * 忽略证书校验（可选）
     */
    @NameInMap("ignoreSSL")
    public boolean ignoreSSL;

}