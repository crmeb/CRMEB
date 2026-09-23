package com.alipay.easysdk.kms.aliyun.credentials.http;

import java.util.Arrays;

public enum FormatType {
    XML("application/xml", "text/xml"),
    JSON("application/json", "text/json"),
    RAW("application/octet-stream"),
    FORM("application/x-www-form-urlencoded");

    private final String[] formats;

    FormatType(String... formats) {
        this.formats = formats;
    }

    public static String mapFormatToAccept(FormatType format) {
        return format.formats[0];
    }

    public static FormatType mapAcceptToFormat(String accept) {
        for (FormatType value : values()) {
            if (Arrays.asList(value.formats).contains(accept)) {
                return value;
            }
        }
        return FormatType.RAW;
    }
}
