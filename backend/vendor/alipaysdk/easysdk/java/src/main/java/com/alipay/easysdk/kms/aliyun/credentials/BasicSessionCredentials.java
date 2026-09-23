package com.alipay.easysdk.kms.aliyun.credentials;

public class BasicSessionCredentials implements ICredentials {
    protected final Long roleSessionDurationSeconds;
    private final String accessKeyId;
    private final String accessKeySecret;
    private final String securityToken;
    private Long sessionStartedTimeInMilliSeconds = 0L;

    public BasicSessionCredentials(String accessKeyId, String accessKeySecret, String securityToken) {
        this(accessKeyId, accessKeySecret, securityToken, 0L);
    }

    public BasicSessionCredentials(String accessKeyId, String accessKeySecret, String securityToken, Long roleSessionDurationSeconds) {
        if (accessKeyId == null) {
            throw new IllegalArgumentException("Access key ID cannot be null.");
        }
        if (accessKeySecret == null) {
            throw new IllegalArgumentException("Access key secret cannot be null.");
        }
        this.accessKeyId = accessKeyId;
        this.accessKeySecret = accessKeySecret;
        this.securityToken = securityToken;
        this.roleSessionDurationSeconds = roleSessionDurationSeconds;
        this.sessionStartedTimeInMilliSeconds = System.currentTimeMillis();
    }

    @Override
    public String getAccessKeyId() {
        return accessKeyId;
    }

    @Override
    public String getAccessKeySecret() {
        return accessKeySecret;
    }

    @Override
    public String getSecurityToken() {
        return securityToken;
    }

    public boolean willSoonExpire() {
        if (roleSessionDurationSeconds == 0) {
            return false;
        }
        long now = System.currentTimeMillis();
        double expireFact = 0.95;
        return roleSessionDurationSeconds * expireFact < (now - sessionStartedTimeInMilliSeconds) / 1000.0;
    }
}
