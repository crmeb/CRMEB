package com.alipay.easysdk.kms.aliyun;

import com.alipay.easysdk.kernel.util.Signer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * KMS签名器
 *
 * @author aliyunkms
 * @version $Id: AliyunKMSSigner.java, v 0.1 2020年05月08日 9:10 PM aliyunkms Exp $
 */
public class AliyunKMSSigner extends Signer {
    private AliyunKMSClient client;
    private static final Logger LOGGER = LoggerFactory.getLogger(Signer.class);

    public AliyunKMSSigner(AliyunKMSClient aliyunKmsClient) {
        this.client = aliyunKmsClient;
    }

    /**
     * 计算签名
     *
     * @param content       待签名的内容
     * @param privateKeyPem 私钥，使用KMS签名不使用此参数
     * @return 签名值的Base64串
     */
    public String sign(String content, String privateKeyPem) {
        try {
            return this.client.sign(content);
        } catch (Exception e) {
            String errorMessage = "签名遭遇异常，content=" + content + " reason=" + e.getMessage();
            LOGGER.error(errorMessage, e);
            throw new RuntimeException(errorMessage, e);
        }
    }

    public AliyunKMSClient getClient() {
        return this.client;
    }

    public void setClient(AliyunKMSClient client) {
        this.client = client;
    }
}
