using System;
using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Member.Identification.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Member.Identification
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestInit()
        {
            IdentityParam identityParam = new IdentityParam()
            {
                IdentityType = "CERT_INFO",
                CertType = "IDENTITY_CARD",
                CertName = "张三",
                CertNo = "513901198008089876"
            };

            MerchantConfig merchantConfig = new MerchantConfig()
            {
                ReturnUrl = "www.taobao.com"
            };


            AlipayUserCertifyOpenInitializeResponse response = Factory.Member.Identification().Init(
                Guid.NewGuid().ToString(), "FACE", identityParam, merchantConfig);

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.NotNull(response.CertifyId);
        }

        [Test]
        public void TestCertify()
        {
            AlipayUserCertifyOpenCertifyResponse response = Factory.Member.Identification().Certify("bbdb57e87211279e2c22de5846d85161");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.IsTrue(response.Body.Contains("https://openapi.alipay.com/gateway.do?alipay_sdk=alipay-easysdk-net"));
            Assert.IsTrue(response.Body.Contains("sign"));
        }

        [Test]
        public void TestQuery()
        {
            AlipayUserCertifyOpenQueryResponse response = Factory.Member.Identification().Query("89ad1f1b8171d9741c3e5620fd77f9de");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40004");
            Assert.AreEqual(response.Msg, "Business Failed");
            Assert.AreEqual(response.SubCode, "CERTIFY_ID_EXPIRED");
            Assert.AreEqual(response.SubMsg, "认证已失效");
            Assert.NotNull(response.HttpBody);
            Assert.IsNull(response.Passed);
            Assert.IsNull(response.IdentityInfo);
            Assert.IsNull(response.MaterialInfo);
        }
    }
}
