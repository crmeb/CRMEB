using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Base.Qrcode.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Base.Qrcode
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
            AlipayOpenAppQrcodeCreateResponse response = Factory.Base.Qrcode().Create(
                "https://opendocs.alipay.com", "ageIndex=1", "文档站点");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.NotNull(response.QrCodeUrl);
        }
    }
}
