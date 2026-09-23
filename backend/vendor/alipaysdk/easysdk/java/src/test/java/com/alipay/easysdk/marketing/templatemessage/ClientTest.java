package com.alipay.easysdk.marketing.templatemessage;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Marketing;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.marketing.templatemessage.models.AlipayOpenAppMiniTemplatemessageSendResponse;
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
    public void testSend() throws Exception {
        AlipayOpenAppMiniTemplatemessageSendResponse response = Marketing.TemplateMessage().send(
                "2088102122458832",
                "2017010100000000580012345678",
                "MDI4YzIxMDE2M2I5YTQzYjUxNWE4MjA4NmU1MTIyYmM=",
                "page/component/index",
                "{\"keyword1\": {\"value\" : \"12:00\"},\"keyword2\": {\"value\" : \"20180808\"},\"keyword3\": {\"value\" : \"支付宝\"}}");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("USER_TEMPLATE_ILLEGAL"));
        assertThat(response.subMsg, is("模板非法"));
        assertThat(response.httpBody, not(nullValue()));
    }
}