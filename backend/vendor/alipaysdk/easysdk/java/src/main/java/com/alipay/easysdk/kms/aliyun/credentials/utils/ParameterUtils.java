package com.alipay.easysdk.kms.aliyun.credentials.utils;

import com.alipay.easysdk.kms.aliyun.credentials.http.MethodType;

import java.io.UnsupportedEncodingException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.*;

public class ParameterUtils {
    private final static String TIME_ZONE = "UTC";
    private final static String FORMAT_ISO8601 = "yyyy-MM-dd'T'HH:mm:ss'Z'";
    private final static String SEPARATOR = "&";
    public static final String ENCODING = "UTF-8";
    private static final String ALGORITHM_NAME = "HmacSHA1";

    public static String getUniqueNonce() {
        StringBuilder uniqueNonce = new StringBuilder();
        UUID uuid = UUID.randomUUID();
        uniqueNonce.append(uuid.toString());
        uniqueNonce.append(System.currentTimeMillis());
        uniqueNonce.append(Thread.currentThread().getId());
        return uniqueNonce.toString();
    }

    public static String getISO8601Time(Date date) {
        SimpleDateFormat df = new SimpleDateFormat(FORMAT_ISO8601);
        df.setTimeZone(new SimpleTimeZone(0, TIME_ZONE));
        return df.format(date);
    }

    public static Date getUTCDate(String date) throws ParseException {
        SimpleDateFormat df = new SimpleDateFormat(FORMAT_ISO8601);
        df.setTimeZone(new SimpleTimeZone(0, TIME_ZONE));
        return df.parse(date);
    }

    public String composeStringToSign(MethodType method, Map<String, String> queries) throws UnsupportedEncodingException {
        String[] sortedKeys = queries.keySet().toArray(new String[]{});
        Arrays.sort(sortedKeys);
        StringBuilder canonicalizedQueryString = new StringBuilder();

        for (String key : sortedKeys) {
            if (!StringUtils.isEmpty(queries.get(key))) {
                canonicalizedQueryString.append("&")
                        .append(AcsURLEncoder.percentEncode(key)).append("=")
                        .append(AcsURLEncoder.percentEncode(queries.get(key)));
            }
        }

        return method.toString() +
                SEPARATOR +
                AcsURLEncoder.percentEncode("/") +
                SEPARATOR +
                AcsURLEncoder.percentEncode(canonicalizedQueryString.toString().substring(1));
    }

    public String composeUrl(String endpoint, Map<String, String> queries, String protocol) throws UnsupportedEncodingException {
        StringBuilder urlBuilder = new StringBuilder("");
        urlBuilder.append(protocol);
        urlBuilder.append("://").append(endpoint);
        urlBuilder.append("/?");
        StringBuilder builder = new StringBuilder("");

        for (Map.Entry<String, String> entry : queries.entrySet()) {
            String key = entry.getKey();
            String val = entry.getValue();
            if (val == null) {
                continue;
            }
            builder.append(AcsURLEncoder.encode(key));
            builder.append("=").append(AcsURLEncoder.encode(val));
            builder.append("&");
        }

        int strIndex = builder.length();
        builder.deleteCharAt(strIndex - 1);
        String query = builder.toString();
        return urlBuilder.append(query).toString();
    }
}
