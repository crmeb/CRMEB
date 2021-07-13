using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Payment.Huabei.Models;
using System;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Payment.HuaBei
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestCrate()
        {
            string outTradeNo = Guid.NewGuid().ToString();
            HuabeiConfig config = new HuabeiConfig()
            {
                HbFqNum = "3",
                HbFqSellerPercent = "0"
            };
            AlipayTradeCreateResponse response = Factory.Payment.Huabei().Create("Iphone6 16G",
                    outTradeNo, "88.88", "2088002656718920", config);

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.OutTradeNo, outTradeNo);
            Assert.True(response.TradeNo.StartsWith("202"));
        }
    }
}
