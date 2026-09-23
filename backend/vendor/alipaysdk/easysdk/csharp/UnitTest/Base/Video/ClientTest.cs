using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Base.Video.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Base.Video
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestUpload()
        {
            AlipayOfflineMaterialImageUploadResponse response = Factory.Base.Video().Upload(
                "测试视频", TestAccount.GetSolutionBasePath() + "/UnitTest/Fixture/sample.mp4");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.NotNull(response.ImageId);
            Assert.IsTrue(response.ImageUrl.StartsWith("https://"));
        }
    }
}
