package com.alipay.easysdk.base.oauth;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.base.oauth.models.AlipaySystemOauthTokenResponse;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {
    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testGetToken() throws Exception {
        AlipaySystemOauthTokenResponse response = Factory.Base.OAuth().getToken("fe1ae5abacd54ba2a6c8f6902533TX64");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40002"));
        assertThat(response.msg, is("Invalid Arguments"));
        assertThat(response.subCode, is("isv.code-invalid"));
        assertThat(response.subMsg, is("授权码code无效"));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testRefreshToken() throws Exception {
        AlipaySystemOauthTokenResponse response = Factory.Base.OAuth().refreshToken("1234567890");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40002"));
        assertThat(response.msg, is("Invalid Arguments"));
        assertThat(response.subCode, is("isv.refresh-token-invalid"));
        assertThat(response.subMsg, is("刷新令牌refresh_token无效"));
        assertThat(response.httpBody, not(nullValue()));
    }
}