/**
 * Alipay.com Inc. Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk;

import com.alipay.easysdk.kernel.Config;
import com.alipay.easysdk.kms.aliyun.AliyunKMSConfig;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;

import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.Map;

/**
 * @author zhongyu
 * @version $Id: TestAccount.java, v 0.1 2020年01月19日 4:42 PM zhongyu Exp $
 */
public class TestAccount {
    /**
     * 从文件中读取私钥
     * <p>
     * 注意：实际开发过程中，请务必注意不要将私钥信息配置在源码中（比如配置为常量或储存在配置文件的某个字段中等），因为私钥的保密等级往往比源码高得多，将会增加私钥泄露的风险。推荐将私钥信息储存在专用的私钥文件中，
     * 将私钥文件通过安全的流程分发到服务器的安全储存区域上，仅供自己的应用运行时读取。
     * <p>
     * 此处为了单元测试执行的环境普适性，私钥文件配置在resources资源下，实际过程中请不要这样做。
     *
     * @param appId 私钥对应的APP_ID
     * @return 私钥字符串
     */
    private static String getPrivateKey(String appId) {
        InputStream stream = TestAccount.class.getResourceAsStream("/fixture/privateKey.json");
        Map<String, String> result = new Gson().fromJson(new InputStreamReader(stream), new TypeToken<Map<String, String>>() {}.getType());
        return result.get(appId);
    }

    /**
     * 从文件中读取阿里云AccessKey配置信息
     * 此处为了单元测试执行的环境普适性，AccessKey信息配置在resources资源下，实际过程中请不要这样做。
     * @param key AccessKey配置对应的key
     * @return AccessKey配置字符串
     */
    private static String getAliyunAccessKey(String key){
            InputStream stream = TestAccount.class.getResourceAsStream("/fixture/aliyunAccessKey.json");
            Map<String, String> result = new Gson().fromJson(new InputStreamReader(stream), new TypeToken<Map<String, String>>() {}.getType());
            return result.get(key);
    }

    /**
     * 线上小程序测试账号
     */
    public static class Mini {
        public static final Config CONFIG = getConfig();

        public static Config getConfig() {
            Config config = new Config();
            config.protocol = "https";
            config.gatewayHost = "openapi.alipay.com";
            config.appId = "2019022663440152";
            config.signType = "RSA2";

            config.alipayPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAumX1EaLM4ddn1Pia4SxTRb62aVYxU8I2mHMqrc"
                    + "pQU6F01mIO/DjY7R4xUWcLi0I2oH/BK/WhckEDCFsGrT7mO+JX8K4sfaWZx1aDGs0m25wOCNjp+DCVBXotXSCurqgGI/9UrY+"
                    + "QydYDnsl4jB65M3p8VilF93MfS01omEDjUW+1MM4o3FP0khmcKsoHnYGs21btEeh0LK1gnnTDlou6Jwv3Ew36CbCNY2cYkuyP"
                    + "AW0j47XqzhWJ7awAx60fwgNBq6ZOEPJnODqH20TAdTLNxPSl4qGxamjBO+RuInBy+Bc2hFHq3pNv6hTAfktggRKkKzDlDEUwg"
                    + "SLE7d2eL7P6rwIDAQAB";
            config.merchantPrivateKey = getPrivateKey(config.appId);
            config.notifyUrl = "https://www.test.com/callback";
            return config;
        }
    }

    /**
     * 线上生活号测试账号
     */
    public static class OpenLife {
        public static final Config CONFIG = getConfig();

        private static Config getConfig() {
            Config config = new Config();
            config.protocol = "https";
            config.gatewayHost = "openapi.alipay.com";
            config.appId = "2021002177673029";
            config.signType = "RSA2";

            config.alipayCertPath = "src/test/resources/fixture/alipayCertPublicKey_RSA2.crt";
            config.alipayRootCertPath = "src/test/resources/fixture/alipayRootCert.crt";
            config.merchantCertPath = "src/test/resources/fixture/appCertPublicKey_2021002177673029.crt";
            config.merchantPrivateKey = getPrivateKey(config.appId);
            return config;
        }
    }

    /**
     * Aliyun KMS签名测试账号
     */
    public static class AliyunKMS {
        public static final AliyunKMSConfig CONFIG = getConfig();

        private static AliyunKMSConfig getConfig() {
            AliyunKMSConfig config = new AliyunKMSConfig();
            config.protocol = "https";
            config.gatewayHost = "openapi.alipay.com";
            config.appId = "2021001163614348";
            config.signType = "RSA2";
            config.notifyUrl = "https://www.test.com/callback";

            config.alipayPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiOgupSXhUE3GkMDeCpeDwoEM2z+krBpaKPFbfS" +
                    "JgFVoN/M1s62VC6LhFI9aL4F76bqMGilQPpe2ukW5UmLR+C3OmliuqE/v5/UEpasnndcZMEKadQbWOpQ4eBHGkKTASQhtbgYb3U" +
                    "WS+viD5MfHS0+3h+sko8cW06jONmjG2tvFpnmooIjMawXByK8/f4vBMBk4ZQQodo4TT18mhyyyIoilhLH2EatQp/lov54ZhwHi9" +
                    "8LXeLw7Yt4QK8q7u+lB34V8lsu9zVMEMZExhoblsdjgzFAY6KzCn/QGnQE5e54i59+wONAyf2npUkz4cpPDJPLQ7KBu1febsZjk" +
                    "g9vZrXwIDAQAB";

            //如果使用阿里云KMS签名，则不需要配置私钥
            //config.merchantPrivateKey = getPrivateKey(config.appId);

            //如果使用第三方签名服务，则需要指定签名提供方名称，阿里云KMS的名称为"AliyunKMS"
            config.signProvider = "AliyunKMS";

            //如果使用阿里云KMS签名，需要更换为您的阿里云账号信息
            config.aliyunAccessKeyId = getAliyunAccessKey("AccessKeyId");
            config.aliyunAccessKeySecret = getAliyunAccessKey("AccessKeySecret");
            config.kmsKeyId = "4358f298-8e30-4849-9791-52e68dbd9d1e";
            config.kmsKeyVersionId = "e71daa69-c321-4014-b0c4-ba070c7839ee";

            //如果使用阿里云KMS签名，需要更换为您的KMS服务地址
            // KMS服务地址列表详情，请参考：
            // https://help.aliyun.com/document_detail/69006.html?spm=a2c4g.11186623.2.9.783f77cfAoNhY6#concept-69006-zh
            config.kmsEndpoint = "kms.cn-hangzhou.aliyuncs.com";

            return config;
        }
    }
}