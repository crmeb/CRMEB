/**
 * Alipay.com Inc. Copyright (c) 2004-2019 All Rights Reserved.
 */
package com.alipay.easysdk.kms.aliyun;

import com.alipay.easysdk.kernel.Config;
import com.aliyun.tea.NameInMap;

/**
 * Aliyun KMS配置参数模型
 */
public class AliyunKMSConfig extends Config {
    /**
     * 阿里云官方申请的AccessKey Id
     */
    @NameInMap("aliyunAccessKeyId")
    public String aliyunAccessKeyId;

    /**
     * 阿里云官方申请的AccessKey Secret
     */
    @NameInMap("aliyunAccessKeySecret")
    public String aliyunAccessKeySecret;

    /**
     * KMS主密钥ID
     */
    @NameInMap("kmsKeyId")
    public String kmsKeyId;

    /**
     * KMS主密钥版本ID
     */
    @NameInMap("kmsKeyVersionId")
    public String kmsKeyVersionId;

    /**
     * KMS服务地址
     * KMS服务地址列表详情，请参考：
     * https://help.aliyun.com/document_detail/69006.html?spm=a2c4g.11186623.2.9.783f77cfAoNhY6#concept-69006-zh
     */
    @NameInMap("kmsEndpoint")
    public String kmsEndpoint;
}
