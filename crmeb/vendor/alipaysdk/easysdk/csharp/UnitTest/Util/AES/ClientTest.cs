using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Kernel;

namespace UnitTest.Util.AES
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Config config = TestAccount.Mini.GetConfig();
            config.EncryptKey = "aa4BtZ4tspm2wnXLb1ThQA==";
            Factory.SetOptions(config);
        }

        [Test]
        public void TestDecrypt()
        {
            string plainText = Factory.Util.AES().Decrypt("ILpoMowjIQjfYMR847rnFQ==");
            Assert.AreEqual(plainText, "test1234567");
        }

        [Test]
        public void TestEncrypt()
        {
            string cipherText = Factory.Util.AES().Encrypt("test1234567");
            Assert.AreEqual(cipherText, "ILpoMowjIQjfYMR847rnFQ==");
        }
    }
}
