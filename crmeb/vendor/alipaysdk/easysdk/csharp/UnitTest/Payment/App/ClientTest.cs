using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Payment.App.Models;
using System.Collections.Generic;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Payment.App
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestPay()
        {
            AlipayTradeAppPayResponse response = Factory.Payment.App().Pay("iPhone6 16G",
                "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.IsTrue(response.Body.Contains("app_id=2019022663440152&biz_content=%7b%22subject%22%3a%22iPhone6+16G%22%2c%22" +
                "out_trade_no%22%3a%22f4833085-0c46-4bb0-8e5f-622a02a4cffc%22%2c%22total_amount%22%3a%220.10%22%7d&charset=UTF-8&" +
                "format=json&method=alipay.trade.app.pay&notify_url=https%3a%2f%2fwww.test.com%2fcallback&sign="));
        }

        [Test]
        public void TestPayWithOptional()
        {
            AlipayTradeAppPayResponse response = Factory.Payment.App()
                .Agent("ca34ea491e7146cc87d25fca24c4cD11")
                .Optional("extend_params", GetHuabeiConfig())
                .Pay("iPhone6 16G", "f4833085-0c46-4bb0-8e5f-622a02a4cffc", "0.10");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.IsTrue(response.Body.Contains("app_auth_token=ca34ea491e7146cc87d25fca24c4cD11&app_id=2019022663440152&biz_content=%7b%22subject%22%3a%22iPhone6+16G%22%2c"
                + "%22out_trade_no%22%3a%22f4833085-0c46-4bb0-8e5f-622a02a4cffc%22%2c%22total_amount%22%3a%220"
                + ".10%22%2c%22extend_params%22%3a%7b%22hb_fq_num%22%3a%223%22%2c%22hb_fq_seller_percent%22%3a%22100%22%7d%7d&charset=UTF"
                + "-8&format=json&method=alipay.trade.app.pay&notify_url=https%3a%2f%2fwww.test.com%2fcallback&sign="));
        }

        private Dictionary<string, string> GetHuabeiConfig()
        {
            Dictionary<string, string> extendParams = new Dictionary<string, string>
            {
                { "hb_fq_num", "3" },
                { "hb_fq_seller_percent", "100" }
            };
            return extendParams;
        }
    }
}
