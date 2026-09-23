using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Marketing.Pass.Models;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Marketing.Pass
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.Mini.CONFIG);
        }

        [Test]
        public void TestCreateTemplate()
        {
            AlipayPassTemplateAddResponse response = Factory.Marketing.Pass().CreateTemplate("123456789", GetTplContent());

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.True(response.Success);
            Assert.True(response.Result.Contains("tpl_id"));
        }

        [Test]
        public void TestUpdateTemplate()
        {
            AlipayPassTemplateUpdateResponse response = Factory.Marketing.Pass().UpdateTemplate("2020012014534017917956080", GetTplContent());

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.True(response.Success);
            Assert.True(response.Result.Contains("tpl_id"));
        }

        [Test]
        public void TestAddInstance()
        {
            AlipayPassInstanceAddResponse response = Factory.Marketing.Pass().AddInstance("2020012014534017917956080", "{}",
                    "1", "{\"partner_id\":\"2088102114633762\",\"out_trade_no\":\"1234567\"}");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40004");
            Assert.AreEqual(response.Msg, "Business Failed");
            Assert.AreEqual(response.SubCode, "KP.AE_ALIPASS_APPID_NOSUPPORT");
            Assert.AreEqual(response.SubMsg, "该AppId不支持");
            Assert.NotNull(response.HttpBody);
            Assert.False(response.Success);
            Assert.True(response.Result.Contains("该AppId不支持"));
        }

        [Test]
        public void TestUpdateInstance()
        {
            AlipayPassInstanceUpdateResponse response = Factory.Marketing.Pass().UpdateInstance("209919213",
                    "2088918273", "{}", "USED", "8612231273", "wave");

            Assert.IsFalse(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "40004");
            Assert.AreEqual(response.Msg, "Business Failed");
            Assert.AreEqual(response.SubCode, "KP.AE_ALIPASS_NOTEXIST");
            Assert.AreEqual(response.SubMsg, "卡券不存在");
            Assert.NotNull(response.HttpBody);
            Assert.False(response.Success);
            Assert.True(response.Result.Contains("{\"operate\":\"UPDATE\"}"));
        }

        private string GetTplContent()
        {
            return "{\"logo\":\"http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX\",\"strip\":null,\"icon\":null,"
                    + "\"content\":{\"evoucherInfo\":{\"goodsId\":\"\",\"title\":\"test\",\"type\":\"boardingPass\","
                    + "\"product\":\"air\",\"startDate\":\"2020-01-20 13:45:56\",\"endDate\":\"2020-01-25 13:45:56\","
                    + "\"operation\":[{\"message\":{\"img\":\"http://img01.taobaocdn.com/top/i1/LB1NDJuQpXXXXbYXFXXXXXXXXXX\","
                    + "\"target\":\"\"},\"format\":\"img\",\"messageEncoding\":\"utf-8\",\"altText\":\"\"}],"
                    + "\"einfo\":{\"logoText\":\"test\",\"headFields\":[{\"key\":\"test\",\"label\":\"测试\",\"value\":\"\","
                    + "\"type\":\"text\"}],\"primaryFields\":[{\"key\":\"from\",\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"},"
                    + "{\"key\":\"to\",\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"}],\"secondaryFields\":[{\"key\":\"fltNo\","
                    + "\"label\":\"航班号\",\"value\":\"CA123\",\"type\":\"text\"}],\"auxiliaryFields\":[{\"key\":\"test\","
                    + "\"label\":\"测试\",\"value\":\"\",\"type\":\"text\"}],\"backFields\":[]},\"locations\":[]},"
                    + "\"merchant\":{\"mname\":\"钟雨\",\"mtel\":\"\",\"minfo\":\"\"},\"platform\":{\"channelID\":\"2088201564809153\","
                    + "\"webServiceUrl\":\"https://alipass.alipay.com/builder/syncRecord.htm?tempId=2020012013442621326446216\"},"
                    + "\"style\":{\"backgroundColor\":\"RGB(26,150,219)\"},\"fileInfo\":{\"formatVersion\":\"2\",\"canShare\":true,"
                    + "\"canBuy\":false,\"canPresent\":true,\"serialNumber\":\"2020012013520759738677158\",\"supportTaxi\":\"true\","
                    + "\"taxiSchemaUrl\":\"alipays://platformapi/startapp?appId=20000778&bizid=260&channel=71322\"},"
                    + "\"appInfo\":{\"app\":{\"android_appid\":\"\",\"ios_appid\":\"\",\"android_launch\":\"\",\"ios_launch\":\"\","
                    + "\"android_download\":\"\",\"ios_download\":\"\"},\"label\":\"测试\",\"message\":\"\"},"
                    + "\"source\":\"alipassprod\",\"alipayVerify\":[\"qrcode\"]}}";
        }
    }
}
