package com.alipay.easysdk.payment.common;

import com.alipay.easysdk.TestAccount.Mini;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.common.models.AlipayDataDataserviceBillDownloadurlQueryResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeCancelResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeCloseResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeCreateResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeFastpayRefundQueryResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeQueryResponse;
import com.alipay.easysdk.payment.common.models.AlipayTradeRefundResponse;
import org.junit.Before;
import org.junit.Test;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.UUID;

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
    public void testCreate() throws Exception {
        String outTradeNo = UUID.randomUUID().toString();
        AlipayTradeCreateResponse response = Factory.Payment.Common().create(
                "iPhone6 16G", outTradeNo, "0.01", "2088002656718920");

        assertThat(ResponseChecker.success(response), is(true));
        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.outTradeNo, is(outTradeNo));
        assertThat(response.tradeNo, startsWith("202"));
    }

    @Test
    public void testCreateWithOptional() throws Exception {
        String outTradeNo = UUID.randomUUID().toString();
        AlipayTradeCreateResponse response = Factory.Payment.Common().optional("goods_detail", getGoodsDetail())
                .create("iPhone6 16G", outTradeNo, "0.01", "2088002656718920");

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.outTradeNo, is(outTradeNo));
        assertThat(response.tradeNo, startsWith("202"));
    }

    private List<Object> getGoodsDetail() {
        List<Object> goodsDetail = new ArrayList<>();
        Map<String, Object> goodDetail = new HashMap<>();
        goodDetail.put("goods_id", "apple-01");
        goodDetail.put("goods_name", "iPhone6 16G");
        goodDetail.put("quantity", 1);
        goodDetail.put("price", "0.01");
        goodsDetail.add(goodDetail);
        return goodsDetail;
    }

    @Test
    public void testQuery() throws Exception {
        AlipayTradeQueryResponse response = Factory.Payment.Common().query("6f149ddb-ab8c-4546-81fb-5880b4aaa318");

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.outTradeNo, is("6f149ddb-ab8c-4546-81fb-5880b4aaa318"));
    }

    @Test
    public void testCancel() throws Exception {
        AlipayTradeCancelResponse response = Factory.Payment.Common().cancel(createNewAndReturnOutTradeNo());

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.action, is("close"));
    }

    @Test
    public void testClose() throws Exception {
        AlipayTradeCloseResponse response = Factory.Payment.Common().close(createNewAndReturnOutTradeNo());

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
    }

    @Test
    public void testRefund() throws Exception {
        AlipayTradeRefundResponse response = Factory.Payment.Common().refund(
                "64628156-f784-4572-9540-485b7c91b850", "0.01");

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.refundFee, is("0.01"));
    }

    @Test
    public void testQueryRefund() throws Exception {
        AlipayTradeFastpayRefundQueryResponse response = Factory.Payment.Common().queryRefund(
                "64628156-f784-4572-9540-485b7c91b850", "64628156-f784-4572-9540-485b7c91b850");

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.refundAmount, is("0.01"));
        assertThat(response.totalAmount, is("0.01"));
    }

    @Test
    public void testDownloadBill() throws Exception {
        AlipayDataDataserviceBillDownloadurlQueryResponse response = Factory.Payment.Common().downloadBill("trade", "2020-01");

        assertThat(response.code, is("10000"));
        assertThat(response.msg, is("Success"));
        assertThat(response.subCode, is(nullValue()));
        assertThat(response.subMsg, is(nullValue()));
        assertThat(response.httpBody, not(nullValue()));
        assertThat(response.billDownloadUrl.startsWith("http://dwbillcenter.alipay.com/"), is(true));
    }

    private String createNewAndReturnOutTradeNo() throws Exception {
        return Factory.Payment.Common().create("iPhone6 16G", UUID.randomUUID().toString(),
                "88.88", "2088002656718920").outTradeNo;
    }
}