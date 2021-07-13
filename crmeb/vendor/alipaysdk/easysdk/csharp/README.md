[![NuGet](https://badge.fury.io/nu/AlipayEasySDK.svg)](https://badge.fury.io/nu/AlipayEasySDK)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk?ref=badge_shield)

欢迎使用 Alipay **Easy** SDK for .NET 。

Alipay Esay SDK for .NET让您不用复杂编程即可访支付宝开放平台开放的各项常用能力，SDK可以自动帮您满足能力调用过程中所需的证书校验、加签、验签、发送HTTP请求等非功能性要求。

下面向您介绍Alipay Easy SDK for .NET 的基本设计理念和使用方法。

## 设计理念
不同于原有的[Alipay SDK](https://github.com/alipay/alipay-sdk-net-all)通用而全面的设计理念，Alipay Easy SDK对开放能力的API进行了更加贴近高频场景的精心设计与裁剪，简化了服务端调用方式，让调用API像使用语言内置的函数一样简便。

同时，您也不必担心面向高频场景提炼的API可能无法完全契合自己的个性化场景，Alipay Easy SDK支持灵活的动态扩展方式，同样可以满足低频参数、低频API的使用需求。

Alipay Easy SDK提供了与[能力地图](https://opendocs.alipay.com/mini/00am3f)相对应的代码组织结构，让开发者可以快速找到不同能力对应的API。

Alipay Easy SDK主要目标是提升开发者在**服务端**集成支付宝开放平台开放的各类核心能力的效率。

## 环境要求
1. Alipay Easy SDK for .NET基于`.Net Standard 2.0`开发，支持`.Net Framework 4.6.1`、.`Net Core 2.0`及其以上版本

2. 使用 Alipay Easy SDK for .NET 之前 ，您需要先前往[支付宝开发平台-开发者中心](https://openhome.alipay.com/platform/developerIndex.htm)完成开发者接入的一些准备工作，包括创建应用、为应用添加功能包、设置应用的接口加签方式等。

3. 准备工作完成后，注意保存如下信息，后续将作为使用SDK的输入。

* 加签模式为公钥证书模式时（推荐）

`AppId`、`应用的私钥`、`应用公钥证书文件`、`支付宝公钥证书文件`、`支付宝根证书文件`

* 加签模式为公钥模式时

`AppId`、`应用的私钥`、`支付宝公钥`

## 安装依赖
### 通过[NuGet](https://www.nuget.org/packages/AlipayEasySDK/)程序包管理器在线安装依赖（推荐）
* 在 `解决方案资源管理器面板` 中右击您的项目选择 `管理 NuGet 程序包` 菜单，在打开的 `NuGet 管理面板` 中点击 `浏览` 选项卡输入 `AlipayEasySDK`，在下方列表中选择 `Authors` 为 `antopen` 由官方发布的**最新稳定版**NuGet包，点击 **安装** 即可。

* 或者通过 .NET CLI 工具来安装
	> dotnet add package AlipayEasySDK

### 离线安装NuGet包（适用于自己修改源码后的本地重新打包安装）
1. 使用`Visual Studio`打开本`README.md`所在文件夹下的`AlipayEasySDK.sln`解决方案，在`生成`菜单栏下，执行`全部重新生成`。
2. 在`AlipayEasySDK/bin/Debug`或`AlipayEasySDK/bin/Release`目录下，找到`AlipayEasySDK.[version].nupkg`文件，该文件即为本SDK的NuGet离线包。
3. 参照[NuGet离线安装程序包使用指南](https://yq.aliyun.com/articles/689227)，在您的.NET应用项目工程中引入本SDK的NuGet离线包，即可完成SDK的依赖安装。

## 快速开始
### 普通调用
以下这段代码示例向您展示了使用Alipay Easy SDK for .NET调用一个API的3个主要步骤：

1. 设置参数（全局只需设置一次）。
2. 发起API调用。
3. 处理响应或异常。

```charp
using System;
using Alipay.EasySDK.Factory;
using Alipay.EasySDK.Kernel;
using Alipay.EasySDK.Kernel.Util;
using Alipay.EasySDK.Payment.FaceToFace.Models;

namespace SDKDemo
{
    class Program
    {
        static void Main(string[] args)
        {
            // 1. 设置参数（全局只需设置一次）
            Factory.SetOptions(GetConfig());
            try
            {
                // 2. 发起API调用（以创建当面付收款二维码为例）
                AlipayTradePrecreateResponse response = Factory.Payment.FaceToFace()
                    .PreCreate("Apple iPhone11 128G", "2234567234890", "5799.00");
                // 3. 处理响应或异常
                if (ResponseChecker.Success(response))
                {
                    Console.WriteLine("调用成功");
                }
                else
                {
                    Console.WriteLine("调用失败，原因：" + response.Msg + "，" + response.SubMsg);
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("调用遭遇异常，原因：" + ex.Message);
                throw ex;
            }
        }

        static private Config GetConfig()
        {
            return new Config()
            {
                Protocol = "https",
                GatewayHost = "openapi.alipay.com",
                SignType = "RSA2",

                AppId = "<-- 请填写您的AppId，例如：2019091767145019 -->",

                // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
                MerchantPrivateKey = "<-- 请填写您的应用私钥，例如：MIIEvQIBADANB ... ... -->",

                MerchantCertPath = "<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->",
                AlipayCertPath = "<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->",
                AlipayRootCertPath = "<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt -->",

                // 如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
                // AlipayPublicKey = "<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->"

                //可设置异步通知接收服务地址（可选）
                NotifyUrl = "<-- 请填写您的支付类接口异步通知接收服务地址，例如：https://www.test.com/callback -->",

                //可设置AES密钥，调用AES加解密相关接口时需要（可选）
                EncryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->"
            };
        }
    }
}
```

### 扩展调用
#### ISV代调用

```csharp
Factory.Payment.FaceToFace()
    //调用Agent扩展方法，设置app_auth_token，完成ISV代调用
    .Agent("ca34ea491e7146cc87d25fca24c4cD11")
    .PreCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置独立的异步通知地址

```csharp
Factory.Payment.FaceToFace()
    // 调用AsyncNotify扩展方法，可以为每此API调用，设置独立的异步通知地址
    // 此处设置的异步通知地址的优先级高于全局Config中配置的异步通知地址
    .AsyncNotify("https://www.test.com/callback")
    .PreCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置可选业务参数

```csharp
List<object> goodsDetailList = new List<object>();
Dictionary<string, object> goodsDetail = new Dictionary<string, object>
{
    { "goods_id", "apple-01" },
    { "goods_name", "Apple iPhone11 128G" },
    { "quantity", 1 },
    { "price", "5799.00" }
};
goodsDetailList.Add(goodsDetail);

Factory.Payment.FaceToFace()
    // 调用Optional扩展方法，完成可选业务参数（biz_content下的可选字段）的设置
    .Optional("seller_id", "2088102146225135")
    .Optional("discountable_amount", "8.88")
    .Optional("goods_detail", goodsDetailList)
    .PreCreate("Apple iPhone11 128G", "2234567890", "5799.00");

Dictionary<string, object> optionalArgs = new Dictionary<string, object>
{
    { "seller_id", "2088102146225135" },
    { "discountable_amount", "8.88" },
    { "goods_detail", goodsDetailList }
};

Factory.Payment.FaceToFace()
    // 也可以调用BatchOptional扩展方法，批量设置可选业务参数（biz_content下的可选字段）
    .BatchOptional(optionalArgs)
    .PreCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 多种扩展灵活组合

```csharp
// 多种扩展方式可灵活组装（对扩展方法的调用顺序没有要求）
Factory.Payment.FaceToFace()
    .Agent("ca34ea491e7146cc87d25fca24c4cD11")
    .AsyncNotify("https://www.test.com/callback")
    .Optional("seller_id", "2088102146225135")
    .PreCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

## API组织规范
在Alipay Easy SDK中，API的引用路径与能力地图的组织层次一致，遵循如下规则

> Factory.能力名称.场景名称().接口方法名称( ... )

比如，如果您想要使用[能力地图](https://opendocs.alipay.com/mini/00am3f)中`营销能力`下的`模板消息`场景中的`小程序发送模板消息`，只需按如下形式编写调用代码即可（不同编程语言的连接符号可能不同）。

`Factory.Marketing.TemplateMessage().send( ... )`

其中，接口方法名称通常是对其依赖的OpenAPI功能的一个最简概况，接口方法的出入参与OpenAPI中同名参数含义一致，可参照OpenAPI相关参数的使用说明。

Alipay Easy SDK将致力于保持良好的API命名，以符合开发者的编程直觉。

## 已支持的API列表

| 能力类别      | 场景类别            | 接口方法名称                 | 调用的OpenAPI名称                                              |
|-----------|-----------------|------------------------|-----------------------------------------------------------|
| Base      | OAuth           | getToken               | alipay\.system\.oauth\.token                              |
| Base      | OAuth           | refreshToken           | alipay\.system\.oauth\.token                              |
| Base      | Qrcode          | create                 | alipay\.open\.app\.qrcode\.create                         |
| Base      | Image           | upload                 | alipay\.offline\.material\.image\.upload                  |
| Base      | Video           | upload                 | alipay\.offline\.material\.image\.upload                  |
| Member    | Identification  | init                   | alipay\.user\.certify\.open\.initialize                   |
| Member    | Identification  | certify                | alipay\.user\.certify\.open\.certify                      |
| Member    | Identification  | query                  | alipay\.user\.certify\.open\.query                        |
| Payment   | Common          | create                 | alipay\.trade\.create                                     |
| Payment   | Common          | query                  | alipay\.trade\.query                                      |
| Payment   | Common          | refund                 | alipay\.trade\.refund                                     |
| Payment   | Common          | close                  | alipay\.trade\.close                                      |
| Payment   | Common          | cancel                 | alipay\.trade\.cancel                                     |
| Payment   | Common          | queryRefund            | alipay\.trade\.fastpay\.refund\.query                     |
| Payment   | Common          | downloadBill           | alipay\.data\.dataservice\.bill\.downloadurl\.query       |
| Payment   | Common          | verifyNotify           | -                                                         |
| Payment   | Huabei          | create                 | alipay\.trade\.create                                     |
| Payment   | FaceToFace      | pay                    | alipay\.trade\.pay                                        |
| Payment   | FaceToFace      | precreate              | alipay\.trade\.precreate                                  |
| Payment   | App             | pay                    | alipay\.trade\.app\.pay                                   |
| Payment   | Page            | pay                    | alipay\.trade\.page\.pay                                  |
| Payment   | Wap             | pay                    | alipay\.trade\.wap\.pay                                   |
| Security  | TextRisk        | detect                 | alipay\.security\.risk\.content\.detect                   |
| Marketing | Pass            | createTemplate         | alipay\.pass\.template\.add                               |
| Marketing | Pass            | updateTemplate         | alipay\.pass\.template\.update                            |
| Marketing | Pass            | addInstance            | alipay\.pass\.instance\.add                               |
| Marketing | Pass            | updateInstance         | alipay\.pass\.instance\.update                            |
| Marketing | TemplateMessage | send                   | alipay\.open\.app\.mini\.templatemessage\.send            |
| Marketing | OpenLife        | createImageTextContent | alipay\.open\.public\.message\.content\.create            |
| Marketing | OpenLife        | modifyImageTextContent | alipay\.open\.public\.message\.content\.modify            |
| Marketing | OpenLife        | sendText               | alipay\.open\.public\.message\.total\.send                |
| Marketing | OpenLife        | sendImageText          | alipay\.open\.public\.message\.total\.send                |
| Marketing | OpenLife        | sendSingleMessage      | alipay\.open\.public\.message\.single\.send               |
| Marketing | OpenLife        | recallMessage          | alipay\.open\.public\.life\.msg\.recall                   |
| Marketing | OpenLife        | setIndustry            | alipay\.open\.public\.template\.message\.industry\.modify |
| Marketing | OpenLife        | getIndustry            | alipay\.open\.public\.setting\.category\.query            |
| Util      | AES             | decrypt                | -                                                         |
| Util      | AES             | encrypt                | -                                                         |
| Util      | Generic         | execute                | -                                                         |

> 注：更多高频场景的API持续更新中，敬请期待。

## 文档
[API Doc](./../APIDoc.md)

[Alipay Easy SDK](./../README.md)
