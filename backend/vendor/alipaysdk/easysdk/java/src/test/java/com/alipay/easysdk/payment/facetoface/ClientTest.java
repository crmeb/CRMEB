package com.alipay.easysdk.payment.facetoface;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.facetoface.models.AlipayTradePayResponse;
import com.alipay.easysdk.payment.facetoface.models.AlipayTradePrecreateResponse;
import org.junit.Before;
import org.junit.Test;

import java.util.UUID;

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
    public void testPay() throws Exception {
        AlipayTradePayResponse response = Factory.Payment.FaceToFace().pay("iPhone6 16G",
                "64628156-f784-4572-9540-485b7c91b850", "0.01", "289821051157962364");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("ACQ.PAYMENT_AUTH_CODE_INVALID"));
        assertThat(response.subMsg, is("支付失败，获取顾客账户信息失败，请顾客刷新付款码后重新收款，如再次收款失败，请联系管理员处理。[SOUNDWAVE_PARSER_FAIL]"));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testPreCreate() throws Exception {
        AlipayTradePrecreateResponse response = Factory.Payment.FaceToFace().preCreate("iPhone6 16G",
                createNewAndReturnOutTradeNo(), "0.10");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.qrCode.startsWith("https://qr.alipay.com/"), is(true));
    }

    private String createNewAndReturnOutTradeNo() throws Exception {
        return Factory.Payment.Common().create("Iphone6 16G", UUID.randomUUID().toString(),
                "88.88", "2088002656718920").outTradeNo;
    }
}