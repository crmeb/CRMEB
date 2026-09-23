package com.alipay.easysdk.kms.aliyun.credentials.provider;

import com.alipay.easysdk.kms.aliyun.credentials.EcsRamRoleCredentials;
import com.alipay.easysdk.kms.aliyun.credentials.exceptions.CredentialException;
import com.alipay.easysdk.kms.aliyun.credentials.http.CompatibleUrlConnClient;
import com.alipay.easysdk.kms.aliyun.credentials.http.HttpRequest;
import com.alipay.easysdk.kms.aliyun.credentials.http.HttpResponse;
import com.alipay.easysdk.kms.aliyun.credentials.http.MethodType;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;

import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;

public class ECSMetadataServiceCredentialsFetcher {
    private static final String URL_IN_ECS_METADATA = "/latest/meta-data/ram/security-credentials/";
    private static final int DEFAULT_TIMEOUT_IN_MILLISECONDS = 1000;
    private static final String ECS_METADAT_FETCH_ERROR_MSG = "Failed to get RAM session credentials from ECS metadata service.";
    private static final int DEFAULT_ECS_SESSION_TOKEN_DURATION_SECONDS = 3600 * 6;
    private URL credentialUrl;
    private String roleName;
    private String metadataServiceHost = "100.100.100.200";
    private int connectionTimeoutInMilliseconds;

    public ECSMetadataServiceCredentialsFetcher() {
        this.connectionTimeoutInMilliseconds = DEFAULT_TIMEOUT_IN_MILLISECONDS;
    }

    public void setRoleName(String roleName) {
        if (null == roleName) {
            throw new NullPointerException("You must specifiy a valid role name.");
        }
        this.roleName = roleName;
        setCredentialUrl();
    }

    private void setCredentialUrl() {
        try {
            this.credentialUrl = new URL("http://" + metadataServiceHost + URL_IN_ECS_METADATA + roleName);
        } catch (MalformedURLException e) {
            throw new IllegalArgumentException(e.toString());
        }
    }

    public ECSMetadataServiceCredentialsFetcher withECSMetadataServiceHost(String host) {
        System.err.println("withECSMetadataServiceHost() method is only for testing, please don't use it");
        this.metadataServiceHost = host;
        setCredentialUrl();
        return this;
    }

    public ECSMetadataServiceCredentialsFetcher withConnectionTimeout(int milliseconds) {
        this.connectionTimeoutInMilliseconds = milliseconds;
        return this;
    }

    public String getMetadata() throws CredentialException {
        HttpRequest request = new HttpRequest(credentialUrl.toString());
        request.setMethod(MethodType.GET);
        request.setConnectTimeout(connectionTimeoutInMilliseconds);
        request.setReadTimeout(connectionTimeoutInMilliseconds);
        HttpResponse response;

        try {
            response = CompatibleUrlConnClient.compatibleGetResponse(request);
        } catch (Exception e) {
            throw new CredentialException("Failed to connect ECS Metadata Service: " + e.toString());
        }

        if (response.getStatus() != HttpURLConnection.HTTP_OK) {
            throw new CredentialException(ECS_METADAT_FETCH_ERROR_MSG + " HttpCode=" + response.getStatus());
        }

        return new String(response.getHttpContent());
    }

    public EcsRamRoleCredentials fetch() throws CredentialException {
        String jsonContent = getMetadata();
        JsonObject jsonObject = null;
        jsonObject = new JsonParser().parse(jsonContent).getAsJsonObject();

        if (jsonObject.has("Code") && jsonObject.has("AccessKeyId") && jsonObject.has("AccessKeySecret") && jsonObject
                .has("SecurityToken") && jsonObject.has("Expiration")) {

        } else {
            throw new CredentialException("Invalid json got from ECS Metadata service.");
        }

        if (!"Success".equals(jsonObject.get("Code").getAsString())) {
            throw new CredentialException(ECS_METADAT_FETCH_ERROR_MSG);
        }
        return new EcsRamRoleCredentials(jsonObject.get("AccessKeyId").getAsString(), jsonObject.get(
                "AccessKeySecret").getAsString(), jsonObject.get("SecurityToken").getAsString(), jsonObject.get(
                "Expiration").getAsString(), DEFAULT_ECS_SESSION_TOKEN_DURATION_SECONDS);
    }

    public EcsRamRoleCredentials fetch(int retryTimes) throws CredentialException {
        for (int i = 0; i <= retryTimes; i++) {
            try {
                return fetch();
            } catch (CredentialException e) {
                if (i == retryTimes) {
                    throw e;
                }
            }
        }
        throw new CredentialException("Failed to connect ECS Metadata Service: Max retry times exceeded.");
    }
}
