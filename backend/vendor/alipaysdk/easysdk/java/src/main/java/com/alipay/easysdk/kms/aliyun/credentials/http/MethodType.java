package com.alipay.easysdk.kms.aliyun.credentials.http;

public enum MethodType {
    GET(false),
    PUT(true),
    POST(true);

    private final boolean hasContent;

    MethodType(boolean hasContent) {
        this.hasContent = hasContent;
    }

    public boolean hasContent() {
        return hasContent;
    }
}
