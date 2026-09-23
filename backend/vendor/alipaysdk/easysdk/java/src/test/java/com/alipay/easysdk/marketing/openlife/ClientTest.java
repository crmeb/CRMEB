package com.alipay.easysdk.marketing.openlife;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Marketing;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicLifeMsgRecallResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicMessageContentCreateResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicMessageContentModifyResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicMessageSingleSendResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicMessageTotalSendResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicSettingCategoryQueryResponse;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicTemplateMessageIndustryModifyResponse;
import com.alipay.easysdk.marketing.openlife.models.Article;
import com.alipay.easysdk.marketing.openlife.models.Context;
import com.alipay.easysdk.marketing.openlife.models.Keyword;
import com.alipay.easysdk.marketing.openlife.models.Template;
import com.google.common.collect.Lists;
import org.junit.Before;
import org.junit.Ignore;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.notNullValue;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.junit.Assert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(TestAccount.OpenLife.CONFIG);
    }

    @Test
    public void testCreateImageTextContent() throws Exception {
        AlipayOpenPublicMessageContentCreateResponse response = Marketing.OpenLife().createImageTextContent("标题",
                "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                "示例", "T", "activity", "满100减10",
                "关键,热度", "13434343432,xxx@163.com");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.contentId, is(notNullValue()));
        assertThat(response.contentUrl, is(notNullValue()));
    }

    @Test
    public void testModifyImageTextContent() throws Exception {
        AlipayOpenPublicMessageContentModifyResponse response = Marketing.OpenLife().modifyImageTextContent(
                "2021002177673029ecae71f5-9af3-4317-b9ce-d3a8f238f8ce", "新标题",
                "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                "新示例", "T", "activity", "满100减20",
                "关键,热度", "13434343432,xxx@163.com");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.contentId, is("2021002177673029ecae71f5-9af3-4317-b9ce-d3a8f238f8ce"));
        assertThat(response.contentUrl, is(notNullValue()));
    }

    @Test
    public void testSendText() throws Exception {
        AlipayOpenPublicMessageTotalSendResponse response = Marketing.OpenLife().sendText("测试");

        if (response.code.equals("10000")) {
            assertThat(ResponseChecker.success(response), is(true));
            assertThat(response.code, is("10000"));
            assertThat(response.msg, is("Success"));
            assertThat(response.subCode, is(nullValue()));
            assertThat(response.subMsg, is(nullValue()));
            assertThat(response.httpBody, not(nullValue()));
            assertThat(response.messageId, is(notNullValue()));
        } else {
            assertThat(ResponseChecker.success(response), is(false));
            assertThat(response.code, is("40004"));
            assertThat(response.msg, is("Business Failed"));
            assertThat(response.subCode, is("PUB.MSG_BATCH_SD_OVER"));
            assertThat(response.subMsg, is("批量发送消息频率超限"));
            assertThat(response.httpBody, not(nullValue()));
            assertThat(response.messageId, is(nullValue()));
        }
    }

    @Test
    public void testSendImageText() throws Exception {
        Article article = new Article();
        article.actionName = "测试";
        article.desc = "测试";
        article.title = "测试";
        article.imageUrl = "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED";
        article.url = "https://docs.open.alipay.com/api_6/alipay.open.public.message.total.send";
        AlipayOpenPublicMessageTotalSendResponse response = Marketing.OpenLife().sendImageText(Lists.newArrayList(article));

        if (response.code.equals("10000")) {
            assertThat(ResponseChecker.success(response), is(true));
            assertThat(response.code, is("10000"));
            assertThat(response.msg, is("Success"));
            assertThat(response.subCode, is(nullValue()));
            assertThat(response.subMsg, is(nullValue()));
            assertThat(response.httpBody, not(nullValue()));
            assertThat(response.messageId, is(notNullValue()));
        } else {
            assertThat(ResponseChecker.success(response), is(false));
            assertThat(response.code, is("40004"));
            assertThat(response.msg, is("Business Failed"));
            assertThat(response.subCode, is("PUB.MSG_BATCH_SD_OVER"));
            assertThat(response.subMsg, is("批量发送消息频率超限"));
            assertThat(response.httpBody, not(nullValue()));
            assertThat(response.messageId, is(nullValue()));
        }
    }

    @Test
    public void testSendSingleMessage() throws Exception {
        Keyword keyword = new Keyword();
        keyword.color = "#85be53";
        keyword.value = "HU7142";

        Context context = new Context();
        context.headColor = "#85be53";
        context.url = "https://docs.open.alipay.com/api_6/alipay.open.public.message.single.send";
        context.actionName = "查看详情";
        context.keyword1 = keyword;
        context.keyword2 = keyword;
        context.first = keyword;
        context.remark = keyword;

        Template template = new Template();
        template.templateId = "TM9f1a1af9c3d9418bb0f66af22c817f41";
        template.context = context;

        AlipayOpenPublicMessageSingleSendResponse response = Marketing.OpenLife().sendSingleMessage(
                "2088002656718920", template);

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("MSG_TEMPLATE_NOT_EXIST"));
        assertThat(response.subMsg, is("消息模板不存在"));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testRecallMessage() throws Exception {
        AlipayOpenPublicLifeMsgRecallResponse response = Marketing.OpenLife().recallMessage(
                "201905106452100327f456f6-8dd2-4a06-8b0e-ec8a3a85c46a");

        assertThat(ResponseChecker.success(response), is(false));
        assertThat(response.code, is("40004"));
        assertThat(response.msg, is("Business Failed"));
        assertThat(response.subCode, is("PUB.RCD_MSG_NOT_EXIST"));
        assertThat(response.subMsg, is("撤回的消息不存在"));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testSetIndustry() throws Exception {
        AlipayOpenPublicTemplateMessageIndustryModifyResponse response = Marketing.OpenLife().setIndustry(
                "10001/20102", "IT科技/IT软件与服务",
                "10001/20102", "IT科技/IT软件与服务");

        if (response.code.equals("10000")) {
            assertThat(ResponseChecker.success(response), is(true));
            assertThat(response.code, is("10000"));
            assertThat(response.msg, is("Success"));
            assertThat(response.subCode, is(nullValue()));
            assertThat(response.subMsg, is(nullValue()));
            assertThat(response.httpBody, not(nullValue()));
        } else {
            assertThat(ResponseChecker.success(response), is(false));
            assertThat(response.code, is("40004"));
            assertThat(response.msg, is("Business Failed"));
            assertThat(response.subCode, is("3002"));
            assertThat(response.subMsg, is("模板消息行业一月只能修改一次"));
            assertThat(response.httpBody, not(nullValue()));
        }
    }

    @Test
    public void testGetIndustry() throws Exception {
        AlipayOpenPublicSettingCategoryQueryResponse response = Marketing.OpenLife().getIndustry();

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.primaryCategory, is("IT科技/IT软件与服务"));
        assertThat(response.secondaryCategory, is("IT科技/IT软件与服务"));
    }
}