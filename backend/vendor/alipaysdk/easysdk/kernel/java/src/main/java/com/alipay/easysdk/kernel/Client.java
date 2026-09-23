// This file is auto-generated, don't edit it. Thanks.
package com.alipay.easysdk.kernel;

import com.alipay.easysdk.kernel.util.AES;
import com.alipay.easysdk.kernel.util.JsonUtil;
import com.alipay.easysdk.kernel.util.MultipartUtil;
import com.alipay.easysdk.kernel.util.PageUtil;
import com.alipay.easysdk.kernel.util.SignContentExtractor;
import com.alipay.easysdk.kernel.util.Signer;
import com.aliyun.tea.TeaResponse;
import com.google.common.base.Strings;
import com.google.common.io.Files;
import com.google.gson.Gson;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;
import java.util.TimeZone;
import java.util.TreeMap;

public class Client {
    private static final Logger LOGGER = LoggerFactory.getLogger(Client.class);

    /**
     * 构造成本较高的一些参数缓存在上下文中
     */
    private final Context context;

    /**
     * 注入的可选额外文本参数集合
     */
    private final Map<String, String> optionalTextParams = new HashMap<>();

    /**
     * 注入的可选业务参数集合
     */
    private final Map<String, Object> optionalBizParams = new HashMap<>();

    /**
     * 构造函数
     *
     * @param context 上下文对象
     */
    public Client(Context context) {
        this.context = context;
    }

    /**
     * 注入额外文本参数
     *
     * @param key   参数名称
     * @param value 参数的值
     * @return 本客户端本身，便于链路调用
     */
    public Client injectTextParam(String key, String value) {
        optionalTextParams.put(key, value);
        return this;
    }

    /**
     * 注入额外业务参数
     *
     * @param key   业务参数名称
     * @param value 业务参数的值
     * @return 本客户端本身，便于链式调用
     */
    public Client injectBizParam(String key, Object value) {
        optionalBizParams.put(key, value);
        return this;
    }

    /**
     * 获取时间戳，格式yyyy-MM-dd HH:mm:ss
     *
     * @return 当前时间戳
     */
    public String getTimestamp() throws Exception {
        DateFormat df = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        df.setTimeZone(TimeZone.getTimeZone("GMT+8"));
        return df.format(new Date());
    }

    /**
     * 获取Config中的配置项
     *
     * @param key 配置项的名称
     * @return 配置项的值
     */
    public String getConfig(String key) throws Exception {
        return context.getConfig(key);
    }

    /**
     * 获取SDK版本信息
     *
     * @return SDK版本信息
     */
    public String getSdkVersion() throws Exception {
        return context.getSdkVersion();
    }

    /**
     * 将业务参数和其他额外文本参数按www-form-urlencoded格式转换成HTTP Body中的字节数组，注意要做URL Encode
     *
     * @param bizParams 业务参数
     * @return HTTP Body中的字节数组
     */
    public byte[] toUrlEncodedRequestBody(java.util.Map<String, ?> bizParams) throws Exception {
        Map<String, String> sortedMap = getSortedMap(Collections.<String, String>emptyMap(), bizParams, null);
        return buildQueryString(sortedMap).getBytes(AlipayConstants.DEFAULT_CHARSET);
    }

    /**
     * 将网关响应发序列化成Map，同时将API的接口名称和响应原文插入到响应Map的method和body字段中
     *
     * @param response HTTP响应
     * @param method   调用的OpenAPI的接口名称
     * @return 响应反序列化的Map
     */
    @SuppressWarnings("unchecked")
    public java.util.Map<String, Object> readAsJson(TeaResponse response, String method) throws Exception {
        String responseBody = response.getResponseBody();
        Map map = new Gson().fromJson(responseBody, Map.class);
        map.put(AlipayConstants.BODY_FIELD, responseBody);
        map.put(AlipayConstants.METHOD_FIELD, method);
        closeConnection(response);
        return map;
    }

    private void closeConnection(TeaResponse response) {
        if (response.getResponse() != null) {
            try {
                response.getResponse().close();
            } catch (IOException e) {
                LOGGER.warn("关闭链接遭遇异常：" + e.getMessage(), e);
            }
        }
    }

    /**
     * 从响应Map中提取返回值对象的Map，并将响应原文插入到body字段中
     *
     * @param respMap 响应Map
     * @return 返回值对象Map
     */
    public java.util.Map<String, Object> toRespModel(java.util.Map<String, Object> respMap) throws Exception {
        String methodName = (String) respMap.get(AlipayConstants.METHOD_FIELD);
        String responseNodeName = methodName.replace('.', '_') + AlipayConstants.RESPONSE_SUFFIX;
        String errorNodeName = AlipayConstants.ERROR_RESPONSE;

        //先找正常响应节点
        for (Entry<String, Object> pair : respMap.entrySet()) {
            if (responseNodeName.equals(pair.getKey())) {
                Map<String, Object> model = (Map<String, Object>) pair.getValue();
                model.put(AlipayConstants.BODY_FIELD, respMap.get(AlipayConstants.BODY_FIELD));
                return model;
            }
        }

        //再找异常响应节点
        for (Entry<String, Object> pair : respMap.entrySet()) {
            if (errorNodeName.equals(pair.getKey())) {
                Map<String, Object> model = (Map<String, Object>) pair.getValue();
                model.put(AlipayConstants.BODY_FIELD, respMap.get(AlipayConstants.BODY_FIELD));
                return model;
            }
        }

        throw new RuntimeException("响应格式不符合预期，找不到" + responseNodeName + "或" + errorNodeName + "节点");
    }

    /**
     * 生成随机分界符，用于multipart格式的HTTP请求Body的多个字段间的分隔
     *
     * @return 随机分界符
     */
    public String getRandomBoundary() throws Exception {
        return System.currentTimeMillis() + "";
    }

    /**
     * 将其他额外文本参数和文件参数按multipart/form-data格式转换成HTTP Body中的字节数组流
     *
     * @param textParams 其他额外文本参数
     * @param fileParams 业务文件参数
     * @param boundary   HTTP Body中multipart格式的分隔符
     * @return Multipart格式的字节流
     */
    public java.io.InputStream toMultipartRequestBody(java.util.Map<String, String> textParams,
                                                      java.util.Map<String, String> fileParams, String boundary) throws Exception {
        ByteArrayOutputStream stream = new ByteArrayOutputStream();

        //补充其他额外参数
        addOtherParams(textParams, null);

        for (Entry<String, String> pair : textParams.entrySet()) {
            if (!Strings.isNullOrEmpty(pair.getKey()) && !Strings.isNullOrEmpty(pair.getValue())) {
                stream.write(MultipartUtil.getEntryBoundary(boundary));
                stream.write(MultipartUtil.getTextEntry(pair.getKey(), pair.getValue()));
            }
        }

        //组装文件参数
        for (Entry<String, String> pair : fileParams.entrySet()) {
            if (!Strings.isNullOrEmpty(pair.getKey()) && pair.getValue() != null) {
                stream.write(MultipartUtil.getEntryBoundary(boundary));
                stream.write(MultipartUtil.getFileEntry(pair.getKey(), pair.getValue()));
                stream.write(Files.toByteArray(new File(pair.getValue())));
            }
        }

        //添加结束标记
        stream.write(MultipartUtil.getEndBoundary(boundary));

        return new ByteArrayInputStream(stream.toByteArray());
    }

    private void addOtherParams(Map<String, String> textParams, Map<String, ?> bizParams) throws Exception {
        //为null表示此处不是扩展此类参数的时机
        if (textParams != null) {
            for (Entry<String, String> pair : optionalTextParams.entrySet()) {
                if (!textParams.containsKey(pair.getKey())) {
                    textParams.put(pair.getKey(), pair.getValue());
                }
            }
            setNotifyUrl(textParams);
        }

        //为null表示此处不是扩展此类参数的时机
        if (bizParams != null) {
            for (Entry<String, Object> pair : optionalBizParams.entrySet()) {
                if (!bizParams.containsKey(pair.getKey())) {
                    ((Map<String, Object>) bizParams).put(pair.getKey(), pair.getValue());
                }
            }
        }
    }

    /**
     * 生成页面类请求所需URL或Form表单
     *
     * @param method       GET或POST，决定是生成URL还是Form表单
     * @param systemParams 系统参数集合
     * @param bizParams    业务参数集合
     * @param textParams   其他额外文本参数集合
     * @param sign         所有参数的签名值
     * @return 生成的URL字符串或表单
     */
    public String generatePage(String method, java.util.Map<String, String> systemParams, java.util.Map<String, ?> bizParams,
                               java.util.Map<String, String> textParams, String sign) throws Exception {
        if (AlipayConstants.GET.equalsIgnoreCase(method)) {
            //采集并排序所有参数
            Map<String, String> sortedMap = getSortedMap(systemParams, bizParams, textParams);
            sortedMap.put(AlipayConstants.SIGN_FIELD, sign);

            //将所有参数置于URL中
            return getGatewayServerUrl() + "?" + buildQueryString(sortedMap);
        } else if (AlipayConstants.POST.equalsIgnoreCase(method)) {
            //将系统参数、额外文本参数排序后置于URL中
            Map<String, String> urlParams = getSortedMap(systemParams, null, textParams);
            urlParams.put(AlipayConstants.SIGN_FIELD, sign);
            String actionUrl = getGatewayServerUrl() + "?" + buildQueryString(urlParams);

            //将业务参数置于form表单中
            addOtherParams(null, bizParams);
            Map<String, String> formParams = new TreeMap<>();
            formParams.put(AlipayConstants.BIZ_CONTENT_FIELD, JsonUtil.toJsonString(bizParams));
            return PageUtil.buildForm(actionUrl, formParams);
        } else {
            throw new RuntimeException("_generatePage中method只支持传入GET或POST");
        }
    }

    /**
     * 获取商户应用公钥证书序列号，从证书模式运行时环境对象中直接读取
     *
     * @return 商户应用公钥证书序列号
     */
    public String getMerchantCertSN() throws Exception {
        if (context.getCertEnvironment() == null) {
            return null;
        }
        return context.getCertEnvironment().getMerchantCertSN();
    }

    /**
     * 从响应Map中提取支付宝公钥证书序列号
     *
     * @param respMap 响应Map
     * @return 支付宝公钥证书序列号
     */
    public String getAlipayCertSN(java.util.Map<String, Object> respMap) throws Exception {
        return (String) respMap.get(AlipayConstants.ALIPAY_CERT_SN_FIELD);
    }

    /**
     * 获取支付宝根证书序列号，从证书模式运行时环境对象中直接读取
     *
     * @return 支付宝根证书序列号
     */
    public String getAlipayRootCertSN() throws Exception {
        if (context.getCertEnvironment() == null) {
            return null;
        }
        return context.getCertEnvironment().getRootCertSN();
    }

    /**
     * 是否是证书模式
     *
     * @return true：是；false：不是
     */
    public Boolean isCertMode() throws Exception {
        return context.getCertEnvironment() != null;
    }

    /**
     * 获取支付宝公钥，从证书运行时环境对象中直接读取
     * 如果缓存的用户指定的支付宝公钥证书的序列号与网关响应中携带的支付宝公钥证书序列号不一致，需要报错给出提示或自动更新支付宝公钥证书
     *
     * @param alipayCertSN 网关响应中携带的支付宝公钥证书序列号
     * @return 支付宝公钥
     */
    public String extractAlipayPublicKey(String alipayCertSN) throws Exception {
        if (context.getCertEnvironment() == null) {
            return null;
        }
        return context.getCertEnvironment().getAlipayPublicKey(alipayCertSN);
    }

    /**
     * 验证签名
     *
     * @param respMap         响应Map，可以从中提取出sign和body
     * @param alipayPublicKey 支付宝公钥
     * @return true：验签通过；false：验签不通过
     */
    public Boolean verify(java.util.Map<String, Object> respMap, String alipayPublicKey) throws Exception {
        String sign = (String) respMap.get(AlipayConstants.SIGN_FIELD);
        String content = SignContentExtractor.getSignSourceData((String) respMap.get(AlipayConstants.BODY_FIELD),
                (String) respMap.get(AlipayConstants.METHOD_FIELD));
        return Signer.verify(content, sign, alipayPublicKey);
    }

    /**
     * 计算签名，注意要去除key或value为null的键值对
     *
     * @param systemParams       系统参数集合
     * @param bizParams          业务参数集合
     * @param textParams         其他额外文本参数集合
     * @param merchantPrivateKey 私钥
     * @return 签名值的Base64串
     */
    public String sign(java.util.Map<String, String> systemParams, java.util.Map<String, ?> bizParams,
                       java.util.Map<String, String> textParams, String merchantPrivateKey) throws Exception {
        Map<String, String> sortedMap = getSortedMap(systemParams, bizParams, textParams);

        StringBuilder content = new StringBuilder();
        int index = 0;
        for (Entry<String, String> pair : sortedMap.entrySet()) {
            if (!Strings.isNullOrEmpty(pair.getKey()) && !Strings.isNullOrEmpty(pair.getValue())) {
                content.append(index == 0 ? "" : "&").append(pair.getKey()).append("=").append(pair.getValue());
                index++;
            }
        }
        return context.getSigner().sign(content.toString(), merchantPrivateKey);
    }

    /**
     * 将随机顺序的Map转换为有序的Map
     *
     * @param input 随机顺序的Map
     * @return 有序的Map
     */
    public java.util.Map<String, String> sortMap(java.util.Map<String, String> input) throws Exception {
        //GO语言的Map是随机顺序的，每次访问顺序都不同，才需排序
        return input;
    }

    /**
     * AES加密
     *
     * @param plainText 明文
     * @param key       密钥
     * @return 密文
     */
    public String aesEncrypt(String plainText, String key) throws Exception {
        return AES.encrypt(plainText, key);
    }

    /**
     * AES解密
     *
     * @param cipherText 密文
     * @param key        密钥
     * @return 明文
     */
    public String aesDecrypt(String cipherText, String key) throws Exception {
        return AES.decrypt(cipherText, key);
    }

    /**
     * 生成订单串
     *
     * @param systemParams 系统参数集合
     * @param bizParams    业务参数集合
     * @param textParams   额外文本参数集合
     * @param sign         所有参数的签名值
     * @return 订单串
     */
    public String generateOrderString(java.util.Map<String, String> systemParams, java.util.Map<String, Object> bizParams,
                                      java.util.Map<String, String> textParams, String sign) throws Exception {
        //采集并排序所有参数
        Map<String, String> sortedMap = getSortedMap(systemParams, bizParams, textParams);
        sortedMap.put(AlipayConstants.SIGN_FIELD, sign);

        //将所有参数置于URL中
        return buildQueryString(sortedMap);
    }

    /**
     * 对支付类请求的异步通知的参数集合进行验签
     *
     * @param parameters 参数集合
     * @param publicKey  支付宝公钥
     * @return true：验证成功；false：验证失败
     */
    public Boolean verifyParams(java.util.Map<String, String> parameters, String publicKey) throws Exception {
        return Signer.verifyParams(parameters, publicKey);
    }

    private Map<String, String> getSortedMap(Map<String, String> systemParams, Map<String, ?> bizParams,
                                             Map<String, String> textParams) throws Exception {
        addOtherParams(textParams, bizParams);

        Map<String, String> sortedMap = new TreeMap<>(systemParams);
        if (bizParams != null && !bizParams.isEmpty()) {
            sortedMap.put(AlipayConstants.BIZ_CONTENT_FIELD, JsonUtil.toJsonString(bizParams));
        }
        if (textParams != null) {
            sortedMap.putAll(textParams);
        }
        return sortedMap;
    }

    private void setNotifyUrl(Map<String, String> params) throws Exception {
        if (getConfig(AlipayConstants.NOTIFY_URL_CONFIG_KEY) != null && !params.containsKey(AlipayConstants.NOTIFY_URL_FIELD)) {
            params.put(AlipayConstants.NOTIFY_URL_FIELD, getConfig(AlipayConstants.NOTIFY_URL_CONFIG_KEY));
        }
    }

    /**
     * 字符串拼接
     *
     * @param a 字符串a
     * @param b 字符串b
     * @return 字符串a和b拼接后的字符串
     */
    public String concatStr(String a, String b) {
        return a + b;
    }

    private String buildQueryString(Map<String, String> sortedMap) throws UnsupportedEncodingException {
        StringBuilder content = new StringBuilder();
        int index = 0;
        for (Entry<String, String> pair : sortedMap.entrySet()) {
            if (!Strings.isNullOrEmpty(pair.getKey()) && !Strings.isNullOrEmpty(pair.getValue())) {
                content.append(index == 0 ? "" : "&")
                        .append(pair.getKey())
                        .append("=")
                        .append(URLEncoder.encode(pair.getValue(), AlipayConstants.DEFAULT_CHARSET.name()));
                index++;
            }
        }
        return content.toString();
    }

    private String getGatewayServerUrl() throws Exception {
        return getConfig(AlipayConstants.PROTOCOL_CONFIG_KEY) + "://" + getConfig(AlipayConstants.HOST_CONFIG_KEY) + "/gateway.do";
    }
}
