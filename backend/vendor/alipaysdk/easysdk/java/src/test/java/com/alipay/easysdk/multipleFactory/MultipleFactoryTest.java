/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.multipleFactory;

import com.alipay.easysdk.TestAccount;
import com.alipay.easysdk.base.image.models.AlipayOfflineMaterialImageUploadResponse;
import com.alipay.easysdk.factory.MultipleFactory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.marketing.openlife.models.AlipayOpenPublicMessageContentCreateResponse;
import org.junit.Assert;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.*;
import static org.hamcrest.MatcherAssert.assertThat;

/**
 * @author junying
 * @version : MultipleFactoryTest.java, v 0.1 2020年12月23日 4:19 下午 junying Exp $
 */
public class MultipleFactoryTest {

    @Test
    public void testImageUpload() throws Exception {

        MultipleFactory factory = new MultipleFactory();
        factory.setOptions(TestAccount.Mini.CONFIG);

        AlipayOfflineMaterialImageUploadResponse response = factory.Image().upload("测试图片",
                "src/test/resources/fixture/sample.png");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.imageId, not(nullValue()));
        assertThat(response.imageUrl, startsWith("https://"));
    }

    @Test
    public void testCreateImageTextContent() throws Exception {
        MultipleFactory factory = new MultipleFactory();
        factory.setOptions(TestAccount.OpenLife.CONFIG);

        AlipayOpenPublicMessageContentCreateResponse response = factory.OpenLife().createImageTextContent("标题",
                "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                "示例", "T", "activity", "满100减10",
                "关键,热度", "13434343432,xxx@163.com");

        Assert.assertThat(ResponseChecker.success(response), is(true));
        Assert.assertThat(response.code, is("10000"));
        Assert.assertThat(response.msg, is("Success"));
        Assert.assertThat(response.subCode, is(nullValue()));
        Assert.assertThat(response.subMsg, is(nullValue()));
        Assert.assertThat(response.httpBody, not(nullValue()));
        Assert.assertThat(response.contentId, is(notNullValue()));
        Assert.assertThat(response.contentUrl, is(notNullValue()));
    }
}