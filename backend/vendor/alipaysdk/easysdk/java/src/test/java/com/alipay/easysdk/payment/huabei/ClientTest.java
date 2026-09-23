package com.alipay.easysdk.payment.huabei;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.huabei.models.AlipayTradeCreateResponse;
import com.alipay.easysdk.payment.huabei.models.HuabeiConfig;
import org.junit.Before;
import org.junit.Test;

import java.util.UUID;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.hamcrest.CoreMatchers.startsWith;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testCreate() throws Exception {
        String outTradeNo = UUID.randomUUID().toString();

        HuabeiConfig config = new HuabeiConfig();
        config.hbFqNum = "3";
        config.hbFqSellerPercent = "0";
        AlipayTradeCreateResponse response = Factory.Payment.Huabei().create("Iphone6 16G",
                outTradeNo, "0.10", "2088002656718920", config);

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.outTradeNo, is(outTradeNo));
        assertThat(response.tradeNo, startsWith("202"));
    }
}