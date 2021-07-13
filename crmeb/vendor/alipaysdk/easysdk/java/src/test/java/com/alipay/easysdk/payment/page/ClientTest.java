/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.payment.page;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.page.models.AlipayTradePagePayResponse;
import org.junit.Assert;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {
    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testPay() throws Exception {
        AlipayTradePagePayResponse response = Factory.Payment.Page().pay("iPhone6 16G",
                "e5b5bd79-8310-447d-b63b-0fe3a393324d", "0.10", "https://www.taobao.com");

        Assert.assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.body.contains("<form name=\"punchout_form\" method=\"post\" "
                + "action=\"https://openapi.alipay.com/gateway.do?"), is(true));
        assertThat(response.body.contains("notify_url"), is(true));
        assertThat(response.body.contains("return_url"), is(true));
        assertThat(response.body.contains("<input type=\"hidden\" name=\"biz_content\" value=\"{&quot;out_trade_no&quot;:&quot;"
                + "e5b5bd79-8310-447d-b63b-0fe3a393324d&quot;,&quot;total_amount&quot;:&quot;0.10&quot;,&quot;subject&quot;:&quot;iPhone6"
                + " 16G&quot;,&quot;product_code&quot;:&quot;FAST_INSTANT_TRADE_PAY&quot;}\">"), is(true));
        assertThat(response.body.contains("<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >"), is(true));
        assertThat(response.body.contains("<script>document.forms[0].submit();</script>"), is(true));
    }

    @Test
    public void testPayWithOptionalNotify() throws Exception {
        AlipayTradePagePayResponse response = Factory.Payment.Page().asyncNotify("https://www.test2.com/newCallback")
                .pay("iPhone6 16G", "e5b5bd79-8310-447d-b63b-0fe3a393324d",
                        "0.10", "https://www.taobao.com");

        Assert.assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.body.contains("<form name=\"punchout_form\" method=\"post\" "
                + "action=\"https://openapi.alipay.com/gateway.do?"), is(true));
        assertThat(response.body.contains("notify_url=https%3A%2F%2Fwww.test2.com%2FnewCallback"), is(true));
        assertThat(response.body.contains("return_url"), is(true));
        assertThat(response.body.contains("<input type=\"hidden\" name=\"biz_content\" value=\"{&quot;out_trade_no&quot;:&quot;"
                + "e5b5bd79-8310-447d-b63b-0fe3a393324d&quot;,&quot;total_amount&quot;:&quot;0.10&quot;,&quot;subject&quot;:&quot;iPhone6"
                + " 16G&quot;,&quot;product_code&quot;:&quot;FAST_INSTANT_TRADE_PAY&quot;}\">"), is(true));
        assertThat(response.body.contains("<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >"), is(true));
        assertThat(response.body.contains("<script>document.forms[0].submit();</script>"), is(true));
    }
}