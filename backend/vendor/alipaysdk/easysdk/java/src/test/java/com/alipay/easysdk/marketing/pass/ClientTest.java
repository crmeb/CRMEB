package com.alipay.easysdk.marketing.pass;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.marketing.pass.models.AlipayPassInstanceAddResponse;
import com.alipay.easysdk.marketing.pass.models.AlipayPassInstanceUpdateResponse;
import com.alipay.easysdk.marketing.pass.models.AlipayPassTemplateAddResponse;
import com.alipay.easysdk.marketing.pass.models.AlipayPassTemplateUpdateResponse;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.containsString;
import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {
    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.Mini.CONFIG);
    }

    @Test
    public void testCreateTemplate() throws Exception {
        AlipayPassTemplateAddResponse response = Factory.Marketing.Pass().createTemplate("123456789", getTplContent());

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.success, is(true));
        assertThat(response.result, containsString("tpl_id"));
    }

    @Test
    public void testUpdateTemplate() throws Exception {
        AlipayPassTemplateUpdateResponse response = Factory.Marketing.Pass().updateTemplate("2020012014534017917956080", getTplContent());

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.success, is(true));
        assertThat(response.result, containsString("tpl_id"));
    }

    @Test
    public void testAddInstance() throws Exception {
        AlipayPassInstanceAddResponse response = Factory.Marketing.Pass().addInstance("2020012014534017917956080", "{}",
                "1", "{\"partner_id\":\"2088102114633762\",\"out_trade_no\":\"1234567\"}");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.success, is(false));
    }

    @Test
    public void testUpdateInstance() throws Exception {
        AlipayPassInstanceUpdateResponse response = Factory.Marketing.Pass().updateInstance("209919213",
                "2088918273", "{}", "USED", "8612231273", "wave");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("KP.AE_ALIPASS_NOTEXIST"));
        assertThat(response.subMsg, is("卡券不存在"));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.success, is(false));
        assertThat(response.result, containsString("{\"operate\":\"UPDATE\"}"));
    }

    private String getTplContent() {
        return "{\"logo\":\"http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX\",\"strip\":null,\"icon\":null,"
                + "\"content\":{\"evoucherInfo\":{\"goodsId\":\"\",\"title\":\"test\",\"type\":\"boardingPass\","
                + "\"product\":\"air\",\"startDate\":\"2020-01-20 13:45:56\",\"endDate\":\"2020-01-25 13:45:56\","
                + "\"operation\":[{\"message\":{\"img\":\"http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX\","
                + "\"target\":\"\"},\"format\":\"img\",\"messageEncoding\":\"utf-8\",\"altText\":\"\"}],"
                + "\"einfo\":{\"logoText\":\"test\",\"headFields\":[{\"key\":\"test\",\"label\":\"测试\",\"value\":\"\","
                + "\"type\":\"text\"}],\"primaryFields\":[{\"key\":\"from\",\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"},"
                + "{\"key\":\"to\",\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"}],\"secondaryFields\":[{\"key\":\"fltNo\","
                + "\"label\":\"航班号\",\"value\":\"CA123\",\"type\":\"text\"}],\"auxiliaryFields\":[{\"key\":\"test\","
                + "\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"}],\"backFields\":[]},\"locations\":[]},"
                + "\"merchant\":{\"mname\":\"钟雨\",\"mtel\":\"\",\"minfo\":\"\"},\"platform\":{\"channelID\":\"2088201564809153\","
                + "\"webServiceUrl\":\"https://alipass.alipay.com/builder/syncRecord.htm?tempId=2020012013442621326446216\"},"
                + "\"style\":{\"backgroundColor\":\"RGB(26,150,219)\"},\"fileInfo\":{\"formatVersion\":\"2\",\"canShare\":true,"
                + "\"canBuy\":false,\"canPresent\":true,\"serialNumber\":\"2020012013520759738677158\",\"supportTaxi\":\"true\","
                + "\"taxiSchemaUrl\":\"alipays://platformapi/startapp?appId=20000778&bizid=260&channel=71322\"},"
                + "\"appInfo\":{\"app\":{\"android_appid\":\"\",\"ios_appid\":\"\",\"android_launch\":\"\",\"ios_launch\":\"\","
                + "\"android_download\":\"\",\"ios_download\":\"\"},\"label\":\"测试\",\"message\":\"\"},"
                + "\"source\":\"alipassprod\",\"alipayVerify\":[\"qrcode\"]}}";
    }
}