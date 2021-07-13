using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Marketing.TemplateMessage.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Marketing.TemplateMessage
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestSend()
        {
            AlipayOpenAppMiniTemplatemessageSendResponse response = Factory.Marketing.TemplateMessage().Send(
                "2088102122458832",
                    "2017010100000000580012345678",
                    "MDI4YzIxMDE2M2I5YTQzYjUxNWE4MjA4NmU1MTIyYmM=",
                    "page/component/index",
                    "{\"keyword1\": {\"value\" : \"12:00\"},\"keyword2\": {\"value\" : \"20180808\"},\"keyword3\": {\"value\" : \"支付宝\"}}");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40004");
            Assert.AreEqual(response.Msg, "Business Failed");
            Assert.AreEqual(response.SubCode, "USER_TEMPLATE_ILLEGAL");
            Assert.AreEqual(response.SubMsg, "模板非法");
            Assert.NotNull(response.HttpBody);
        }
    }
}
