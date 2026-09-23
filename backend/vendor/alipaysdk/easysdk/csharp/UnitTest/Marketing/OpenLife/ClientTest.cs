using NUnit.Framework;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Marketing.OpenLife.Models;
using System.Collections.Generic;
using Alipay.EasySDK.Kernel.Util;

namespace UnitTest.Marketing.OpenLife
{
    public class ClientTest
    {
        [SetUp]
        public void SetUp()
        {
            Factory.SetOptions(TestAccount.OpenLife.CONFIG);
        }

        [Test]
        public void TestCreateImageTextContent()
        {
            AlipayOpenPublicMessageContentCreateResponse response = Factory.Marketing.OpenLife().CreateImageTextContent("标题",
                    "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                    "示例", "T", "activity", "满100减10",
                    "关键,热度", "13434343432,xxx@163.com");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.NotNull(response.ContentId);
            Assert.NotNull(response.ContentUrl);
        }

        [Test]
        public void TestModifyImageTextContent()
        {
            AlipayOpenPublicMessageContentModifyResponse response = Factory.Marketing.OpenLife().ModifyImageTextContent(
                    "20190510645210035577f788-d6cd-4020-9dba-1a195edb7342", "新标题",
                    "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                    "新示例", "T", "activity", "满100减20",
                    "关键,热度", "13434343432,xxx@163.com");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.ContentId, "20190510645210035577f788-d6cd-4020-9dba-1a195edb7342");
            Assert.NotNull(response.ContentUrl);
        }

        [Test]
        public void TestSendText()
        {
            AlipayOpenPublicMessageTotalSendResponse response = Factory.Marketing.OpenLife().SendText("测试");

            if (response.Code.Equals("10000"))
            {
                Assert.IsTrue(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "10000");
                Assert.AreEqual(response.Msg, "Success");
                Assert.Null(response.SubCode);
                Assert.Null(response.SubMsg);
                Assert.NotNull(response.HttpBody);
                Assert.NotNull(response.MessageId);
            }
            else
            {
                Assert.IsFalse(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "40004");
                Assert.AreEqual(response.Msg, "Business Failed");
                Assert.AreEqual(response.SubCode, "PUB.MSG_BATCH_SD_OVER");
                Assert.AreEqual(response.SubMsg, "批量发送消息频率超限");
                Assert.NotNull(response.HttpBody);
                Assert.Null(response.MessageId);
            }
        }

        [Test]
        public void TestSendImageText()
        {
            Article article = new Article
            {
                ActionName = "测试",
                Desc = "测试",
                Title = "测试",
                ImageUrl = "http://dl.django.t.taobao.com/rest/1.0/image?fileIds=hOTQ1lT1TtOjcxGflvnUXgAAACMAAQED",
                Url = "https://docs.open.alipay.com/api_6/alipay.open.public.message.total.send"
            };
            AlipayOpenPublicMessageTotalSendResponse response = Factory.Marketing.OpenLife().SendImageText(new List<Article> { article });

            if (response.Code.Equals("10000"))
            {
                Assert.IsTrue(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "10000");
                Assert.AreEqual(response.Msg, "Success");
                Assert.Null(response.SubCode);
                Assert.Null(response.SubMsg);
                Assert.NotNull(response.HttpBody);
                Assert.NotNull(response.MessageId);
            }
            else
            {
                Assert.IsFalse(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "40004");
                Assert.AreEqual(response.Msg, "Business Failed");
                Assert.AreEqual(response.SubCode, "PUB.MSG_BATCH_SD_OVER");
                Assert.AreEqual(response.SubMsg, "批量发送消息频率超限");
                Assert.NotNull(response.HttpBody);
                Assert.Null(response.MessageId);
            }
        }

        [Test]
        public void TestSendSingleMessage()
        {
            Keyword keyword = new Keyword
            {
                Color = "#85be53",
                Value = "HU7142"
            };
            Context context = new Context
            {
                HeadColor = "#85be53",
                Url = "https://docs.open.alipay.com/api_6/alipay.open.public.message.single.send",
                ActionName = "查看详情",
                Keyword1 = keyword,
                Keyword2 = keyword,
                First = keyword,
                Remark = keyword
            };
            Alipay.EasySDK.Marketing.OpenLife.Models.Template template = new Alipay.EasySDK.Marketing.OpenLife.Models.Template
            {
                TemplateId = "e44cd3e52ffa46b1a50afc145f55d1ea",
                Context = context
            };
            AlipayOpenPublicMessageSingleSendResponse response = Factory.Marketing.OpenLife().SendSingleMessage(
                    "2088002656718920", template);

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestRecallMessage()
        {
            AlipayOpenPublicLifeMsgRecallResponse response = Factory.Marketing.OpenLife().RecallMessage("201905106452100327f456f6-8dd2-4a06-8b0e-ec8a3a85c46a");

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.IsNull(response.SubCode);
            Assert.IsNull(response.SubMsg);
            Assert.NotNull(response.HttpBody);
        }

        [Test]
        public void TestSetIndustry()
        {
            AlipayOpenPublicTemplateMessageIndustryModifyResponse response = Factory.Marketing.OpenLife().SetIndustry(
                "10001/20102", "IT科技/IT软件与服务",
                    "10001/20102", "IT科技/IT软件与服务");

            if (response.Code.Equals("10000"))
            {
                Assert.IsTrue(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "10000");
                Assert.AreEqual(response.Msg, "Success");
                Assert.Null(response.SubCode);
                Assert.Null(response.SubMsg);
                Assert.NotNull(response.HttpBody);
            }
            else
            {
                Assert.IsFalse(ResponseChecker.Success(response));
                Assert.AreEqual(response.Code, "40004");
                Assert.AreEqual(response.Msg, "Business Failed");
                Assert.AreEqual(response.SubCode, "3002");
                Assert.AreEqual(response.SubMsg, ("模板消息行业一月只能修改一次"));
                Assert.NotNull(response.HttpBody);
            }
        }

        [Test]
        public void TestGetIndustry()
        {
            AlipayOpenPublicSettingCategoryQueryResponse response = Factory.Marketing.OpenLife().GetIndustry();

            Assert.IsTrue(ResponseChecker.Success(response));
            Assert.AreEqual(response.Code, "10000");
            Assert.AreEqual(response.Msg, "Success");
            Assert.Null(response.SubCode);
            Assert.Null(response.SubMsg);
            Assert.NotNull(response.HttpBody);
            Assert.AreEqual(response.PrimaryCategory, "IT科技/IT软件与服务");
            Assert.AreEqual(response.SecondaryCategory, "IT科技/IT软件与服务");
        }
    }
}
