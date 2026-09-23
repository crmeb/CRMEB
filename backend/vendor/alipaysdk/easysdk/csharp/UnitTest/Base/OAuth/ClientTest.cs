using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Base.OAuth.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Base.OAuth
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestGetToken()
        {
            AlipaySystemOauthTokenResponse response = Factory.Base.OAuth().GetToken("1234567890");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40002");
            Assert.AreEqual(response.Msg, "Invalid Arguments");
            Assert.AreEqual(response.SubCode, "isv.code-invalid");
            Assert.AreEqual(response.SubMsg, "授权码code无效");
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestRefreshToken()
        {
            AlipaySystemOauthTokenResponse response = Factory.Base.OAuth().RefreshToken("1234567890");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40002");
            Assert.AreEqual(response.Msg, "Invalid Arguments");
            Assert.AreEqual(response.SubCode, "isv.refresh-token-invalid");
            Assert.AreEqual(response.SubMsg, "刷新令牌refresh_token无效");
            Assert.NotNull(response.HttpBody);
        }
    }
}
