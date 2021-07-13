package com.alipay.easysdk.security.textrisk;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Security;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.security.textrisk.models.AlipaySecurityRiskContentDetectResponse;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.junit.Assert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testDetect() throws Exception {
        AlipaySecurityRiskContentDetectResponse response = Security.TextRisk().detect("test");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.action, is("PASSED"));
        assertThat(response.uniqueId, not(nullValue()));
    }
}