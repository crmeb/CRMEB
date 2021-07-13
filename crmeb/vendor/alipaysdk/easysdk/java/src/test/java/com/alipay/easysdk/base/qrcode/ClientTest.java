package com.alipay.easysdk.base.qrcode;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.base.qrcode.models.AlipayOpenAppQrcodeCreateResponse;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
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
    public void testCreate() throws Exception {
        AlipayOpenAppQrcodeCreateResponse response = Factory.Base.Qrcode().create(
                "https://opendocs.alipay.com", "ageIndex=1", "文档站点");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.qrCodeUrl, not(nullValue()));
    }
}