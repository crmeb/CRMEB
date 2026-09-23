using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Payment.Common.Models;
using System;
using System.Collections.Generic;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Payment.Common
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestCreate()
        {
            string outTradeNo = Guid.NewGuid().ToString();
            AlipayTradeCreateResponse response = Factory.Payment.Common().Create("iPhone6 16G",
                    outTradeNo, "88.88", "2088002656718920");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.OutTradeNo, outTradeNo);
            Assert.True(response.TradeNo.StartsWith("202"));
        }

        [Test]
        public void TestCreateWithOptional()
        {
            string outTradeNo = Guid.NewGuid().ToString();
            AlipayTradeCreateResponse response = Factory.Payment.Common().Optional("goods_detail", GetGoodsDetail())
                    .Create("iPhone6 16G", outTradeNo, "0.01", "2088002656718920");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.OutTradeNo, outTradeNo);
            Assert.True(response.TradeNo.StartsWith("202"));
        }

        private List<object> GetGoodsDetail()
        {
            List<object> goodsDetail = new List<object>();
            Dictionary<string, object> goodDetail = new Dictionary<string, object>();
            goodDetail.Add("goods_id", "apple-01");
            goodDetail.Add("goods_name", "iPhone6 16G");
            goodDetail.Add("quantity", 1);
            goodDetail.Add("price", "0.01");
            goodsDetail.Add(goodDetail);
            return goodsDetail;
        }

        [Test]
        public void TestQuery()
        {
            AlipayTradeQueryResponse response = Factory.Payment.Common().Query("6f149ddb-ab8c-4546-81fb-5880b4aaa318");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.OutTradeNo, "6f149ddb-ab8c-4546-81fb-5880b4aaa318");
        }

        [Test]
        public void TestCancel()
        {
            AlipayTradeCancelResponse response = Factory.Payment.Common().Cancel(CreateNewAndReturnOutTradeNo());

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.Action, "close");
        }

        [Test]
        public void TestClose()
        {
            AlipayTradeCloseResponse response = Factory.Payment.Common().Close(CreateNewAndReturnOutTradeNo());

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestRefund()
        {
            AlipayTradeRefundResponse response = Factory.Payment.Common().Refund(CreateNewAndReturnOutTradeNo(), "0.01");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40004");
            Assert.AreEqual(response.Msg, "Business Failed");
            Assert.AreEqual(response.SubCode, "ACQ.TRADE_STATUS_ERROR");
            Assert.AreEqual(response.SubMsg, "交易状态不合法");
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestQueryRefund()
        {
            AlipayTradeFastpayRefundQueryResponse response = Factory.Payment.Common().QueryRefund(
                "64628156-f784-4572-9540-485b7c91b850", "64628156-f784-4572-9540-485b7c91b850");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.RefundAmount, "0.01");
            Assert.AreEqual(response.TotalAmount, "0.01");
        }

        [Test]
        public void TestDownloadBill()
        {
            AlipayDataDataserviceBillDownloadurlQueryResponse response = Factory.Payment.Common().DownloadBill("trade", "2020-01");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.IsTrue(response.BillDownloadUrl.StartsWith("http://dwbillcenter.alipay.com/"));
        }

        private string CreateNewAndReturnOutTradeNo()
        {
            return Factory.Payment.Common().Create("iPhone6 16G", Guid.NewGuid().ToString(),
                    "88.88", "2088002656718920").OutTradeNo;
        }
    }
}
