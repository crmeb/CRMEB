using System;
using NUnit.Framework;
using Alipay.EasySDK.Payment.Common.Models;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.FactoryTest
{
    public class FactoryTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestGetClient()
        {
            AlipayTradeFastpayRefundQueryResponse response = Factory.GetClient<Alipay.EasySDK.Payment.Common.Client>()
                .QueryRefund("64628156-f784-4572-9540-485b7c91b850", "64628156-f784-4572-9540-485b7c91b850");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.RefundAmount, "0.01");
            Assert.AreEqual(response.TotalAmount, "0.01");
        }
    }
}
