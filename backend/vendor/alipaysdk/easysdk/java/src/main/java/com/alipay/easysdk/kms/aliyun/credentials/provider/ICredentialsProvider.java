package com.alipay.easysdk.kms.aliyun.credentials.provider;

import com.alipay.easysdk.kms.aliyun.credentials.ICredentials;

public interface ICredentialsProvider {
    public ICredentials getCredentials() throws Exception;
}
