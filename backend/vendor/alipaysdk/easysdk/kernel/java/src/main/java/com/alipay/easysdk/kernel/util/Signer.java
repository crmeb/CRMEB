/**
 * Alipay.com Inc. Copyright (c) 2004-2019 All Rights Reserved.
 */
package com.alipay.easysdk.kernel.util;

import com.alipay.easysdk.kernel.AlipayConstants;
import org.bouncycastle.util.encoders.Base64;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.security.KeyFactory;
import java.security.PrivateKey;
import java.security.PublicKey;
import java.security.Signature;
import java.security.spec.PKCS8EncodedKeySpec;
import java.security.spec.X509EncodedKeySpec;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Map;

/**
 * SHA256WithRSA签名器
 *
 * @author zhongyu
 * @version $Id: Signer.java, v 0.1 2019年12月19日 9:10 PM zhongyu Exp $
 */
public class Signer {
    private static final Logger LOGGER = LoggerFactory.getLogger(Signer.class);

    public static String getSignCheckContent(Map<String, String> params) {
        if (params == null) {
            return null;
        }

        StringBuilder content = new StringBuilder();
        List<String> keys = new ArrayList<>(params.keySet());
        Collections.sort(keys);
        for (int i = 0; i < keys.size(); i++) {
            String key = keys.get(i);
            String value = params.get(key);
            content.append(i == 0 ? "" : "&").append(key).append("=").append(value);
        }
        return content.toString();
    }

    /**
     * 验证签名
     *
     * @param content      待验签的内容
     * @param sign         签名值的Base64串
     * @param publicKeyPem 支付宝公钥
     * @return true：验证成功；false：验证失败
     */
    public static boolean verify(String content, String sign, String publicKeyPem) {
        try {
            KeyFactory keyFactory = KeyFactory.getInstance(AlipayConstants.RSA);
            byte[] encodedKey = publicKeyPem.getBytes();
            encodedKey = Base64.decode(encodedKey);
            PublicKey publicKey = keyFactory.generatePublic(new X509EncodedKeySpec(encodedKey));

            Signature signature = Signature.getInstance(AlipayConstants.SHA_256_WITH_RSA);
            signature.initVerify(publicKey);
            signature.update(content.getBytes(AlipayConstants.DEFAULT_CHARSET));
            return signature.verify(Base64.decode(sign.getBytes()));
        } catch (Exception e) {
            String errorMessage = "验签遭遇异常，content=" + content + " sign=" + sign +
                    " publicKey=" + publicKeyPem + " reason=" + e.getMessage();
            LOGGER.error(errorMessage, e);
            throw new RuntimeException(errorMessage, e);
        }
    }

    /**
     * 计算签名
     *
     * @param content       待签名的内容
     * @param privateKeyPem 私钥
     * @return 签名值的Base64串
     */
    public String sign(String content, String privateKeyPem) {
        try {
            byte[] encodedKey = privateKeyPem.getBytes();
            encodedKey = Base64.decode(encodedKey);
            PrivateKey privateKey = KeyFactory.getInstance(AlipayConstants.RSA).generatePrivate(new PKCS8EncodedKeySpec(encodedKey));

            Signature signature = Signature.getInstance(AlipayConstants.SHA_256_WITH_RSA);
            signature.initSign(privateKey);
            signature.update(content.getBytes(AlipayConstants.DEFAULT_CHARSET));
            byte[] signed = signature.sign();
            return new String(Base64.encode(signed));
        } catch (Exception e) {
            String errorMessage = "签名遭遇异常，content=" + content + " privateKeySize=" + privateKeyPem.length() + " reason=" + e.getMessage();
            LOGGER.error(errorMessage, e);
            throw new RuntimeException(errorMessage, e);
        }
    }

    /**
     * 对参数集合进行验签
     *
     * @param parameters 参数集合
     * @param publicKey  支付宝公钥
     * @return true：验证成功；false：验证失败
     */
    public static boolean verifyParams(Map<String, String> parameters, String publicKey) {
        String sign = parameters.get(AlipayConstants.SIGN_FIELD);
        parameters.remove(AlipayConstants.SIGN_FIELD);
        parameters.remove(AlipayConstants.SIGN_TYPE_FIELD);

        String content = getSignCheckContent(parameters);

        return verify(content, sign, publicKey);
    }
}