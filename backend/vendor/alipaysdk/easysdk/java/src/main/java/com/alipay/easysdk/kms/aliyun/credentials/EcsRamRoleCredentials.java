package com.alipay.easysdk.kms.aliyun.credentials;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.TimeZone;

public class EcsRamRoleCredentials extends BasicSessionCredentials {
    private final Long expiration;
    private final Double expireFact = 0.95;
    private final Long refreshIntervalInMillSeconds = 180000L;
    private long lastFailedRefreshTime = 0;

    public EcsRamRoleCredentials(String accessKeyId, String accessKeySecret, String sessionToken,
                                 String expiration, long roleSessionDurationSeconds) {
        super(accessKeyId, accessKeySecret, sessionToken, roleSessionDurationSeconds);

        SimpleDateFormat parser = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        parser.setTimeZone(TimeZone.getTimeZone("GMT"));
        try {
            Date date = parser.parse(expiration.replace('T', ' ').replace('Z', ' '));
            this.expiration = date.getTime();
        } catch (ParseException e) {
            throw new IllegalArgumentException("Failed to get valid expiration time from ECS Metadata service.");
        }
    }

    @Override
    public boolean willSoonExpire() {
        long now = System.currentTimeMillis();
        return this.roleSessionDurationSeconds * (1 - expireFact) > (expiration - now) / 1000;
    }

    public boolean isExpired() {
        long now = System.currentTimeMillis();
        return now >= expiration - refreshIntervalInMillSeconds;
    }

    public boolean shouldRefresh() {
        long now = System.currentTimeMillis();
        return now - lastFailedRefreshTime > refreshIntervalInMillSeconds;
    }

    public void setLastFailedRefreshTime() {
        lastFailedRefreshTime = System.currentTimeMillis();
    }
}
