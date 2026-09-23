/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.payment.app;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.app.models.AlipayTradeAppPayResponse;
import org.junit.Assert;
import org.junit.Before;
import org.junit.Test;

import java.util.HashMap;
import java.util.Map;

import static org.hamcrest.CoreMatchers.containsString;
import static org.hamcrest.CoreMatchers.is;
import static org.junit.Assert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testPay() throws Exception {
        AlipayTradeAppPayResponse response = Factory.Payment.App().pay("iPhone6 16G",
                "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");

        assertThat(ResponseChecker.success(response), is(true));
        Assert.assertThat(response.body, containsString("app_id=2019022663440152&biz_content=%7B%22"
                + "out_trade_no%22%3A%22f4833085-0c46-4bb0-8e5f-622a02a4cffc%22%2C%22"
                + "total_amount%22%3A%220.10%22%2C%22subject%22%3A%22iPhone6+16G%22%7D&"
                + "charset=UTF-8&format=json&method=alipay.trade.app.pay"
                + "&notify_url=https%3A%2F%2Fwww.test.com%2Fcallback&sign="));
    }

    @Test
    public void testPayWithOptional() throws Exception {
        AlipayTradeAppPayResponse response = Factory.Payment.App()
                .agent("ca34ea491e7146cc87d25fca24c4cD11")
                .optional("extend_params", getHuabeiConfig())
                .pay("iPhone6 16G", "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");

        Assert.assertThat(response.body, containsString("app_auth_token=ca34ea491e7146cc87d25fca24c4cD11&"
                + "app_id=2019022663440152&biz_content=%7B%22extend_params%22%3A%7B%22hb_fq_seller_percent%22%3A%22100%22%2C%22"
                + "hb_fq_num%22%3A%223%22%7D%2C%22out_trade_no%22%3A%22f4833085-0c46-4bb0-8e5f-622a02a4cffc%22%2C%22"
                + "total_amount%22%3A%220.10%22%2C%22subject%22%3A%22iPhone6+16G%22%7D&charset=UTF-8&format=json&"
                + "method=alipay.trade.app.pay&notify_url=https%3A%2F%2Fwww.test.com%2Fcallback&sign="));
    }

    private Map<String, String> getHuabeiConfig() {
        Map<String, String> extendParams = new HashMap<>();
        extendParams.put("hb_fq_num", "3");
        extendParams.put("hb_fq_seller_percent", "100");
        return extendParams;
    }
}