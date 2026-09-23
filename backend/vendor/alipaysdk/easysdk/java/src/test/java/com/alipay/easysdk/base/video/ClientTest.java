package com.alipay.easysdk.base.video;

import com.alipay.easysdk.TestAccount.Mini;
import com.alipay.easysdk.base.video.models.AlipayOfflineMaterialImageUploadResponse;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Base;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.CoreMatchers.not;
import static org.hamcrest.CoreMatchers.nullValue;
import static org.hamcrest.CoreMatchers.startsWith;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Factory.setOptions(Mini.CONFIG);
    }

    @Test
    public void testUpload() throws Exception {
        AlipayOfflineMaterialImageUploadResponse response = Base.Video().upload("测试视频",
                "src/test/resources/fixture/sample.mp4");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.imageId, not(nullValue()));
        assertThat(response.imageUrl, startsWith("https://"));
    }
}