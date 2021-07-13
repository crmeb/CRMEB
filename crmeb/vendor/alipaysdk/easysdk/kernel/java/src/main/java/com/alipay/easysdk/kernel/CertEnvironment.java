/**
 * Alipay.com Inc. Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel;

import com.alipay.easysdk.kernel.util.AntCertificationUtil;
import com.google.common.base.Strings;

import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * 证书模式运行时环境
 *
 * @author zhongyu
 * @version $Id: CertEnvironment.java, v 0.1 2020年01月02日 5:21 PM zhongyu Exp $
 */
public class CertEnvironment {
    /**
     * 支付宝根证书内容
     */
    private String rootCertContent;

    /**
     * 支付宝根证书序列号
     */
    private String rootCertSN;

    /**
     * 商户应用公钥证书序列号
     */
    private String merchantCertSN;

    /**
     * 缓存的不同支付宝公钥证书序列号对应的支付宝公钥
     */
    private Map<String, String> cachedAlipayPublicKey = new ConcurrentHashMap<String, String>();

    /**
     * 构造证书运行环境
     *
     * @param merchantCertPath   商户公钥证书路径
     * @param alipayCertPath     支付宝公钥证书路径
     * @param alipayRootCertPath 支付宝根证书路径
     */
    public CertEnvironment(String merchantCertPath, String alipayCertPath, String alipayRootCertPath) {
        if (Strings.isNullOrEmpty(merchantCertPath) || Strings.isNullOrEmpty(alipayCertPath) || Strings.isNullOrEmpty(alipayCertPath)) {
            throw new RuntimeException("证书参数merchantCertPath、alipayCertPath或alipayRootCertPath设置不完整。");
        }

        this.rootCertContent = AntCertificationUtil.readCertContent(alipayRootCertPath);
        this.rootCertSN = AntCertificationUtil.getRootCertSN(rootCertContent);
        this.merchantCertSN = AntCertificationUtil.getCertSN(AntCertificationUtil.readCertContent((merchantCertPath)));

        String alipayPublicCertContent = AntCertificationUtil.readCertContent(alipayCertPath);
        cachedAlipayPublicKey.put(AntCertificationUtil.getCertSN(alipayPublicCertContent),
                AntCertificationUtil.getCertPublicKey(alipayPublicCertContent));
    }

    public String getRootCertSN() {
        return rootCertSN;
    }

    public String getMerchantCertSN() {
        return merchantCertSN;
    }

    public String getAlipayPublicKey(String sn) {
        //如果没有指定sn，则默认取缓存中的第一个值
        if (Strings.isNullOrEmpty(sn)) {
            return cachedAlipayPublicKey.values().iterator().next();
        }

        if (cachedAlipayPublicKey.containsKey(sn)) {
            return cachedAlipayPublicKey.get(sn);
        } else {
            //网关在支付宝公钥证书变更前，一定会确认通知到商户并在商户做出反馈后，才会更新该商户的支付宝公钥证书
            //TODO: 后续可以考虑加入自动升级支付宝公钥证书逻辑，注意并发更新冲突问题
            throw new RuntimeException("支付宝公钥证书[" + sn + "]已过期，请重新下载最新支付宝公钥证书并替换原证书文件");
        }
    }
}