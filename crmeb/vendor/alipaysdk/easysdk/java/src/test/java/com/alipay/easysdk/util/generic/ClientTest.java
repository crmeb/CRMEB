package com.alipay.easysdk.util.generic;

import com.alipay.easysdk.TestAccount.Mini;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.util.generic.models.AlipayOpenApiGenericResponse;
import org.junit.Before;
import org.junit.Test;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(Mini.CONFIG);
    }

    @Test
    public void testExecuteWithoutAppAuthToken() throws Exception {
        String outTradeNo = UUID.randomUUID().toString();
        AlipayOpenApiGenericResponse response = Factory.Util.Generic().execute(
                "alipay.trade.create", null, getBizParams(outTradeNo));

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testExecuteWithAppAuthToken() throws Exception {
        String outTradeNo = UUID.randomUUID().toString();
        AlipayOpenApiGenericResponse response = Factory.Util.Generic().execute(
                "alipay.trade.create", getTextParams(), getBizParams(outTradeNo));

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("20001"));
        assertThat(response.msg, is("Insufficient Token Permissions"));
        assertThat(response.subCode, is("aop.invalid-app-auth-token"));
        assertThat(response.subMsg, is("无效的应用授权令牌"));
        assertThat(response.httpBody, not(nullValue()));
    }

    private Map<String, String> getTextParams() {
        Map<String, String> bizParams = new HashMap<>();
        bizParams.put("app_auth_token", "201712BB_D0804adb2e743078d1822d536956X34");
        return bizParams;
    }

    private Map<String, Object> getBizParams(String outTradeNo) {
        Map<String, Object> bizParams = new HashMap<>();
        bizParams.put("subject", "Iphone6 16G");
        bizParams.put("out_trade_no", outTradeNo);
        bizParams.put("total_amount", "0.10");
        bizParams.put("buyer_id", "2088002656718920");
        bizParams.put("extend_params", getHuabeiParams());
        return bizParams;
    }

    private Map<String, String> getHuabeiParams() {
        Map<String, String> extendParams = new HashMap<>();
        extendParams.put("hb_fq_num", "3");
        extendParams.put("hb_fq_seller_percent", "3");
        return extendParams;
    }
}