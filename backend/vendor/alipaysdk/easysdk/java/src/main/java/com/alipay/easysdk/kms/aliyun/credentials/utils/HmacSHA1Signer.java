package com.alipay.easysdk.kms.aliyun.credentials.utils;

import com.alipay.easysdk.kms.aliyun.credentials.ICredentials;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.io.UnsupportedEncodingException;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;

public class HmacSHA1Signer {
    public static final String ENCODING = "UTF-8";
    private static final String ALGORITHM_NAME = "HmacSHA1";

    public static String signString(String stringToSign, ICredentials credential) {
        return signString(stringToSign, credential.getAccessKeySecret());
    }

    public static String signString(String stringToSign, String accessKeySecret) {
        try {
            Mac mac = Mac.getInstance(ALGORITHM_NAME);
            mac.init(new SecretKeySpec(accessKeySecret.getBytes(ENCODING), ALGORITHM_NAME));
            byte[] signData = mac.doFinal(stringToSign.getBytes(ENCODING));
            return Base64Utils.encode(signData);
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalArgumentException(e.toString());
        } catch (UnsupportedEncodingException e) {
            throw new IllegalArgumentException(e.toString());
        } catch (InvalidKeyException e) {
            throw new IllegalArgumentException(e.toString());
        }
    }

    public static String getSignerName() {
        return "HMAC-SHA1";
    }

    public static String getSignerVersion() {
        return "1.0";
    }
}
