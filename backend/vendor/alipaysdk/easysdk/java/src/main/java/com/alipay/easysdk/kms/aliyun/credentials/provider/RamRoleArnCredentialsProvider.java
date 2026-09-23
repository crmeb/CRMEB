package com.alipay.easysdk.kms.aliyun.credentials.provider;

import com.alipay.easysdk.kms.aliyun.credentials.BasicSessionCredentials;
import com.alipay.easysdk.kms.aliyun.credentials.ICredentials;
import com.alipay.easysdk.kms.aliyun.credentials.exceptions.CredentialException;
import com.alipay.easysdk.kms.aliyun.credentials.http.CompatibleUrlConnClient;
import com.alipay.easysdk.kms.aliyun.credentials.http.HttpRequest;
import com.alipay.easysdk.kms.aliyun.credentials.http.HttpResponse;
import com.alipay.easysdk.kms.aliyun.credentials.http.MethodType;
import com.alipay.easysdk.kms.aliyun.credentials.utils.HmacSHA1Signer;
import com.alipay.easysdk.kms.aliyun.credentials.utils.ParameterUtils;
import com.google.gson.Gson;

import java.util.Map;

public class RamRoleArnCredentialsProvider implements ICredentialsProvider {
    public static final int DEFAULT_DURATION_SECONDS = 3600;
    private static final int DEFAULT_TIMEOUT_IN_MILLISECONDS = 5000;
    private static final String DEFAULT_STS_ENDPOINT = "sts.aliyuncs.com";
    private final String roleArn;
    private final String roleSessionName;
    private final String accessKeyId;
    private final String accessKeySecret;
    private final String stsEndpoint;
    private String policy;
    private Integer connectTimeout;
    private Integer readTimeout;
    private BasicSessionCredentials credential = null;

    public RamRoleArnCredentialsProvider(String accessKeyId, String accessKeySecret, String roleArn) {
        this.roleArn = roleArn;
        this.accessKeyId = accessKeyId;
        this.accessKeySecret = accessKeySecret;
        this.roleSessionName = getNewRoleSessionName();
        this.stsEndpoint = DEFAULT_STS_ENDPOINT;
        this.connectTimeout = DEFAULT_TIMEOUT_IN_MILLISECONDS;
        this.readTimeout = DEFAULT_TIMEOUT_IN_MILLISECONDS * 2;
    }

    public RamRoleArnCredentialsProvider(String accessKeyId, String accessKeySecret, String roleArn, String policy) {
        this(accessKeyId, accessKeySecret, roleArn);
        this.policy = policy;
    }

    public RamRoleArnCredentialsProvider withConnectionTimeout(int milliseconds) {
        this.connectTimeout = milliseconds;
        this.readTimeout = milliseconds * 2;
        return this;
    }

    public static String getNewRoleSessionName() {
        return "kms-credentials-" + System.currentTimeMillis();
    }

    @Override
    public ICredentials getCredentials() throws Exception {
        if (credential == null || credential.willSoonExpire()) {
            CompatibleUrlConnClient client = new CompatibleUrlConnClient();
            credential = getNewSessionCredential(client);
        }
        return credential;
    }

    public BasicSessionCredentials getNewSessionCredential(CompatibleUrlConnClient client) throws Exception {
        ParameterUtils parameterUtils = new ParameterUtils();
        HttpRequest httpRequest = new HttpRequest();
        httpRequest.setUrlParameter("Action", "AssumeRole");
        httpRequest.setUrlParameter("Format", "JSON");
        httpRequest.setUrlParameter("Version", "2015-04-01");
        httpRequest.setUrlParameter("DurationSeconds", String.valueOf(DEFAULT_DURATION_SECONDS));
        httpRequest.setUrlParameter("RoleArn", this.roleArn);
        httpRequest.setUrlParameter("AccessKeyId", this.accessKeyId);
        httpRequest.setUrlParameter("RoleSessionName", this.roleSessionName);
        if (this.policy != null) {
            httpRequest.setUrlParameter("Policy", this.policy);
        }
        httpRequest.setMethod(MethodType.GET);
        httpRequest.setConnectTimeout(this.connectTimeout);
        httpRequest.setReadTimeout(this.readTimeout);
        String strToSign = parameterUtils.composeStringToSign(MethodType.GET, httpRequest.getUrlParameters());
        String signature = HmacSHA1Signer.signString(strToSign, this.accessKeySecret + "&");
        httpRequest.setUrlParameter("Signature", signature);
        httpRequest.setUrl(parameterUtils.composeUrl(this.stsEndpoint, httpRequest.getUrlParameters(), "https"));
        HttpResponse httpResponse = client.syncInvoke(httpRequest);
        Gson gson = new Gson();
        Map<String, Object> map = gson.fromJson(httpResponse.getHttpContentString(), Map.class);
        if (map.containsKey("Credentials")) {
            Map<String, String> credential = (Map<String, String>) map.get("Credentials");
            Long expiration = ParameterUtils.getUTCDate(credential.get("Expiration")).getTime();
            return new BasicSessionCredentials(credential.get("AccessKeyId"), credential.get("AccessKeySecret"),
                    credential.get("SecurityToken"), expiration);
        } else {
            throw new CredentialException(gson.toJson(map));
        }
    }

}
