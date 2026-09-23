package com.alipay.easysdk.kms.aliyun.credentials.provider;

import com.alipay.easysdk.kms.aliyun.credentials.ICredentials;

public class StaticCredentialsProvider implements ICredentialsProvider{
    private final ICredentials credentials;

    public StaticCredentialsProvider(ICredentials credentials) {
        this.credentials = credentials;
    }

    @Override
    public ICredentials getCredentials() throws Exception {
        return this.credentials;
    }
}
