/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.payment.wap;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.wap.models.AlipayTradeWapPayResponse;
import org.junit.Before;
import org.junit.Test;

import java.util.HashMap;
import java.util.Map;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {
    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testPay() throws Exception {
        AlipayTradeWapPayResponse response = Factory.Payment.Wap().pay("iPhone6 16G",
                "b7f4bc7d-ea4b-4efd-9072-d8ea913c8946", "0.10",
                "https://www.taobao.com", "https://www.taobao.com");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.body.contains("<form name=\"punchout_form\" method=\"post\" "
                + "action=\"https://openapi.alipay.com/gateway.do?"), is(true));
        assertThat(response.body.contains("notify_url"), is(true));
        assertThat(response.body.contains("return_url"), is(true));
        assertThat(response.body.contains("<input type=\"hidden\" name=\"biz_content\" value=\"{&quot;out_trade_no&quot;:&quot;"
                + "b7f4bc7d-ea4b-4efd-9072-d8ea913c8946&quot;,&quot;total_amount&quot;:&quot;0.10&quot;,&quot;quit_url&quot;:&quot;"
                + "https://www.taobao.com&quot;,&quot;subject&quot;:&quot;iPhone6 16G&quot;,&quot;product_code&quot;:&quot;"
                + "QUICK_WAP_WAY&quot;}\">"), is(true));
        assertThat(response.body.contains("<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >"), is(true));
        assertThat(response.body.contains("<script>document.forms[0].submit();</script>"), is(true));
    }

    @Test
    public void testPayWithOptional() throws Exception {
        AlipayTradeWapPayResponse response = Factory.Payment.Wap()
                .agent("ca34ea491e7146cc87d25fca24c4cD11").batchOptional(getOptionalArgs())
                .pay("iPhone6 16G", "b7f4bc7d-ea4b-4efd-9072-d8ea913c8946", "0.10",
                        "https://www.taobao.com", "https://www.taobao.com");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.body.contains("<form name=\"punchout_form\" method=\"post\" "
                + "action=\"https://openapi.alipay.com/gateway.do?"), is(true));
        assertThat(response.body.contains("notify_url"), is(true));
        assertThat(response.body.contains("return_url"), is(true));
        assertThat(response.body.contains("app_auth_token"), is(true));
        assertThat(response.body.contains("timeout_express"), is(true));
        assertThat(response.body.contains("body"), is(true));
        assertThat(response.body.contains("<input type=\"hidden\" name=\"biz_content\" value=\"{&quot;out_trade_no&quot;:&quot;"
                + "b7f4bc7d-ea4b-4efd-9072-d8ea913c8946&quot;,&quot;total_amount&quot;:&quot;0.10&quot;,&quot;quit_url&quot;:&quot;"
                + "https://www.taobao.com&quot;,&quot;subject&quot;:&quot;iPhone6 16G&quot;,&quot;timeout_express&quot;:&quot;10m&quot;,"
                + "&quot;product_code&quot;:&quot;QUICK_WAP_WAY&quot;,&quot;body&quot;:&quot;iPhone6 16G&quot;}\">"), is(true));
        assertThat(response.body.contains("<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >"), is(true));
        assertThat(response.body.contains("<script>document.forms[0].submit();</script>"), is(true));
    }

    private Map<String, Object> getOptionalArgs() {
        Map<String, Object> optionalArgs = new HashMap<>();
        optionalArgs.put("timeout_express", "10m");
        optionalArgs.put("body", "iPhone6 16G");
        return optionalArgs;
    }
}