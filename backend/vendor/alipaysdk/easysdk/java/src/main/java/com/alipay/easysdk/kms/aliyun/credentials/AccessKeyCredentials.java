package com.alipay.easysdk.kms.aliyun.credentials;

import com.alipay.easysdk.kms.aliyun.credentials.utils.StringUtils;

public class AccessKeyCredentials implements ICredentials {
    private  String accessKeyId;
    private  String accessKeySecret;

    public AccessKeyCredentials(String accessKeyId, String accessKeySecret) {
        if (StringUtils.isEmpty(accessKeyId)) {
            throw new IllegalArgumentException("Access key ID cannot be empty");
        }
        if (StringUtils.isEmpty(accessKeySecret)) {
            throw new IllegalArgumentException("Access key secret cannot be empty");
        }
        this.accessKeyId = accessKeyId;
        this.accessKeySecret = accessKeySecret;
    }

    public void setAccessKeyId(String accessKeyId) {
        this.accessKeyId = accessKeyId;
    }

    public void setAccessKeySecret(String accessKeySecret) {
        this.accessKeySecret = accessKeySecret;
    }

    @Override
    public String getAccessKeyId() {
        return accessKeyId;
    }

    @Override
    public String getAccessKeySecret() {
        return accessKeySecret;
    }

    @Override
    public String getSecurityToken() {
        return null;
    }
}
