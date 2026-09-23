using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Util.Generic.Models;
using System;
using System.Collections.Generic;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Util.Generic
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestExecuteWithoutAppAuthToken()
        {
            string outTradeNo = Guid.NewGuid().ToString();
            AlipayOpenApiGenericResponse response = Factory.Util.Generic().Execute(
                    "alipay.trade.create", null, GetBizParams(outTradeNo));

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestExecuteWithAppAuthToken()
        {
            string outTradeNo = Guid.NewGuid().ToString();
            AlipayOpenApiGenericResponse response = Factory.Util.Generic().Execute(
                    "alipay.trade.create", GetTextParams(), GetBizParams(outTradeNo));

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "20001");
            Assert.AreEqual(response.Msg, "Insufficient Token Permissions");
            Assert.AreEqual(response.SubCode, "aop.invalid-app-auth-token");
            Assert.AreEqual(response.SubMsg, "无效的应用授权令牌");
            Assert.NotNull(response.HttpBody);
        }

        private Dictionary<string, string> GetTextParams()
        {
            return new Dictionary<string, string>
            {
                { "app_auth_token", "201712BB_D0804adb2e743078d1822d536956X34" }
            };

        }

        private Dictionary<string, object> GetBizParams(string outTradeNo)
        {
            return new Dictionary<string, object>
            {
                { "subject", "Iphone6 16G" },
                { "out_trade_no", outTradeNo },
                { "total_amount", "0.10" },
                { "buyer_id", "2088002656718920" },
                { "extend_params", GetHuabeiParams() }
            };

        }

        private Dictionary<string, string> GetHuabeiParams()
        {
            return new Dictionary<string, string>
            {
                { "hb_fq_num", "3"},
                { "hb_fq_seller_percent", "3"}
            };
        }
    }
}