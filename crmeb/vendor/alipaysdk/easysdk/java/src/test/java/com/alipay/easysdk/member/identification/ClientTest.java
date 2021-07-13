package com.alipay.easysdk.member.identification;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Member;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.member.identification.models.AlipayUserCertifyOpenCertifyResponse;
import com.alipay.easysdk.member.identification.models.AlipayUserCertifyOpenInitializeResponse;
import com.alipay.easysdk.member.identification.models.AlipayUserCertifyOpenQueryResponse;
import com.alipay.easysdk.member.identification.models.IdentityParam;
import com.alipay.easysdk.member.identification.models.MerchantConfig;
import org.junit.Before;
import org.junit.Test;

import java.util.UUID;

import static org.hamcrest.CoreMatchers.containsString;
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
    public void testInit() throws Exception {
        IdentityParam identityParam = new IdentityParam();
        identityParam.identityType = "CERT_INFO";
        identityParam.certType = "IDENTITY_CARD";
        identityParam.certName = "张三";
        identityParam.certNo = "5139011988090987631";
        MerchantConfig merchantConfig = new MerchantConfig();
        merchantConfig.returnUrl = "www.taobao.com";
        AlipayUserCertifyOpenInitializeResponse response = Member.Identification().init(
                UUID.randomUUID().toString(), "FACE", identityParam, merchantConfig);

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.certifyId, not(nullValue()));
    }

    @Test
    public void testCertify() throws Exception {
        AlipayUserCertifyOpenCertifyResponse response = Member.Identification().certify("1226a454daf65c2abbbe0b7b8dc30d20");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.body, containsString("https://openapi.alipay.com/gateway.do?alipay_sdk=alipay-easysdk-java-"));
        assertThat(response.body, containsString("sign"));
    }

    @Test
    public void testQuery() throws Exception {
        AlipayUserCertifyOpenQueryResponse response = Member.Identification().query("89ad1f1b8171d9741c3e5620fd77f9de");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("CERTIFY_ID_EXPIRED"));
        assertThat(response.subMsg, is("认证已失效"));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.passed, is(nullValue()));
        assertThat(response.identityInfo, is(nullValue()));
        assertThat(response.materialInfo, is(nullValue()));
    }
}