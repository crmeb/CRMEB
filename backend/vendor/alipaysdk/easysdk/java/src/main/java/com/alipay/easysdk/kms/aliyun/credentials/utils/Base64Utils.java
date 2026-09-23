package com.alipay.easysdk.kms.aliyun.credentials.utils;

public class Base64Utils {
    private static final String BASE64_CODE = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
            + "abcdefghijklmnopqrstuvwxyz" + "0123456789" + "+/";

    private static byte[] zeroPad(int length, byte[] bytes) {
        byte[] padded = new byte[length];
        System.arraycopy(bytes, 0, padded, 0, bytes.length);
        return padded;
    }

    public synchronized static String encode(byte[] buff) {
        StringBuilder strBuilder = new StringBuilder("");
        int paddingCount = (3 - (buff.length % 3)) % 3;
        byte[] stringArray = zeroPad(buff.length + paddingCount, buff);
        for (int i = 0; i < stringArray.length; i += 3) {
            int j = ((stringArray[i] & 0xff) << 16) +
                    ((stringArray[i + 1] & 0xff) << 8) +
                    (stringArray[i + 2] & 0xff);
            strBuilder.append(BASE64_CODE.charAt((j >> 18) & 0x3f));
            strBuilder.append(BASE64_CODE.charAt((j >> 12) & 0x3f));
            strBuilder.append(BASE64_CODE.charAt((j >> 6) & 0x3f));
            strBuilder.append(BASE64_CODE.charAt(j & 0x3f));
        }
        int intPos = strBuilder.length();
        for (int i = paddingCount; i > 0; i--) {
            strBuilder.setCharAt(intPos - i, '=');
        }

        return strBuilder.toString();
    }
}
