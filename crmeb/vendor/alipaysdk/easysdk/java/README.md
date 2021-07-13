[![Maven Central](https://img.shields.io/maven-central/v/com.alipay.sdk/alipay-easysdk.svg)](https://mvnrepository.com/artifact/com.alipay.sdk/alipay-easysdk)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk?ref=badge_shield)

欢迎使用 Alipay **Easy** SDK for Java 。

Alipay Esay SDK for Java让您不用复杂编程即可访支付宝开放平台开放的各项常用能力，SDK可以自动帮您满足能力调用过程中所需的证书校验、加签、验签、发送HTTP请求等非功能性要求。

下面向您介绍Alipay Easy SDK for Java 的基本设计理念和使用方法。

## 设计理念
不同于原有的[Alipay SDK](https://github.com/alipay/alipay-sdk-java-all)通用而全面的设计理念，Alipay Easy SDK对开放能力的API进行了更加贴近高频场景的精心设计与裁剪，简化了服务端调用方式，让调用API像使用语言内置的函数一样简便。

同时，您也不必担心面向高频场景提炼的API可能无法完全契合自己的个性化场景，Alipay Easy SDK支持灵活的动态扩展方式，同样可以满足低频参数、低频API的使用需求。

Alipay Easy SDK提供了与[能力地图](https://opendocs.alipay.com/mini/00am3f)相对应的代码组织结构，让开发者可以快速找到不同能力对应的API。

Alipay Easy SDK主要目标是提升开发者在**服务端**集成支付宝开放平台开放的各类核心能力的效率。

## 环境要求
1. Alipay Easy SDK for Java 需要配合`JDK 1.7`或其以上版本。

2. 使用 Alipay Easy SDK for Java 之前 ，您需要先前往[支付宝开发平台-开发者中心](https://openhome.alipay.com/platform/developerIndex.htm)完成开发者接入的一些准备工作，包括创建应用、为应用添加功能包、设置应用的接口加签方式等。

3. 准备工作完成后，注意保存如下信息，后续将作为使用SDK的输入。

* 加签模式为公钥证书模式时（推荐）

`AppId`、`应用的私钥`、`应用公钥证书文件`、`支付宝公钥证书文件`、`支付宝根证书文件`

* 加签模式为公钥模式时

`AppId`、`应用的私钥`、`支付宝公钥`

## 安装依赖
### 通过[Maven](https://mvnrepository.com/artifact/com.alipay.sdk/alipay-easysdk)来管理项目依赖（推荐）
推荐通过Maven来管理项目依赖，您只需在项目的`pom.xml`文件中声明如下依赖

```xml
<dependency>
    <groupId>com.alipay.sdk</groupId>
    <artifactId>alipay-easysdk</artifactId>
    <version>Use the version shown in the maven badge</version>
</dependency>
```

### 本地手动集成依赖（适用于自己修改源码后的本地重新打包安装）
1. 本机安装配置[Maven](https://maven.apache.org/)工具。
2. 在本`README.md`所在目录下，执行`mvn package -DskipTests`，将SDK源码编译打包。
3. `target`目录下生成的`alipay-easysdk-java-[version].jar`文件（大小在15MB左右）即为打包SDK得到的Jar文件。
4. 将打包SDK得到的Jar文件拷贝到需要集成本SDK的Java服务端应用的`CLASS_PATH`路径下。

> 使用Intellij IDEA的用户可以在自己服务端Java应用的项目根节点处点击右键 -> Open Module Settings -> Libraries -> 点击+号 -> 选择Java -> 从弹出的资源管理器中浏览选中打包SDK得到的Jar文件 -> 点击确定即可完成SDK的依赖安装。


## 快速开始
### 普通调用
以下这段代码示例向您展示了使用Alipay Easy SDK for Java调用一个API的3个主要步骤：

1. 设置参数（全局只需设置一次）。
2. 发起API调用。
3. 处理响应或异常。

```java
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.factory.Factory.Payment;
import com.alipay.easysdk.kernel.Config;
import com.alipay.easysdk.kernel.util.ResponseChecker;
import com.alipay.easysdk.payment.facetoface.models.AlipayTradePrecreateResponse;

public class Main {
    public static void main(String[] args) throws Exception {
        // 1. 设置参数（全局只需设置一次）
        Factory.setOptions(getOptions());
        try {
            // 2. 发起API调用（以创建当面付收款二维码为例）
            AlipayTradePrecreateResponse response = Payment.FaceToFace()
                    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
            // 3. 处理响应或异常
            if (ResponseChecker.success(response)) {
                System.out.println("调用成功");
            } else {
                System.err.println("调用失败，原因：" + response.msg + "，" + response.subMsg);
            }
        } catch (Exception e) {
            System.err.println("调用遭遇异常，原因：" + e.getMessage());
            throw new RuntimeException(e.getMessage(), e);
        }
    }

    private static Config getOptions() {
        Config config = new Config();
        config.protocol = "https";
        config.gatewayHost = "openapi.alipay.com";
        config.signType = "RSA2";

        config.appId = "<-- 请填写您的AppId，例如：2019091767145019 -->";

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        config.merchantPrivateKey = "<-- 请填写您的应用私钥，例如：MIIEvQIBADANB ... ... -->";

        //注：证书文件路径支持设置为文件系统中的路径或CLASS_PATH中的路径，优先从文件系统中加载，加载失败后会继续尝试从CLASS_PATH中加载
        config.merchantCertPath = "<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->";
        config.alipayCertPath = "<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->";
        config.alipayRootCertPath = "<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt -->";

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        // config.alipayPublicKey = "<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->";

        //可设置异步通知接收服务地址（可选）
        config.notifyUrl = "<-- 请填写您的支付类接口异步通知接收服务地址，例如：https://www.test.com/callback -->";

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        config.encryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->";

        return config;
    }
}
```

### 扩展调用
#### ISV代调用

```java
Factory.Payment.FaceToFace()
    // 调用agent扩展方法，设置app_auth_token，完成ISV代调用
    .agent("ca34ea491e7146cc87d25fca24c4cD11")
    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置独立的异步通知地址

```java
Factory.Payment.FaceToFace()
    // 调用asyncNotify扩展方法，可以为每此API调用，设置独立的异步通知地址
    // 此处设置的异步通知地址的优先级高于全局Config中配置的异步通知地址
    .asyncNotify("https://www.test.com/callback")
    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置可选业务参数

```java
List<Object> goodsDetailList = new ArrayList<>();
Map<String, Object> goodsDetail = new HashMap<>();
goodsDetail.put("goods_id", "apple-01");
goodsDetail.put("goods_name", "Apple iPhone11 128G");
goodsDetail.put("quantity", 1);
goodsDetail.put("price", "5799.00");
goodsDetailList.add(goodsDetail);

Factory.Payment.FaceToFace()
    // 调用optional扩展方法，完成可选业务参数（biz_content下的可选字段）的设置
    .optional("seller_id", "2088102146225135")
    .optional("discountable_amount", "8.88")
    .optional("goods_detail", goodsDetailList)
    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");


Map<String, Object> optionalArgs = new HashMap<>();
optionalArgs.put("seller_id", "2088102146225135");
optionalArgs.put("discountable_amount", "8.88");
optionalArgs.put("goods_detail", goodsDetailList);

Factory.Payment.FaceToFace()
    // 也可以调用batchOptional扩展方法，批量设置可选业务参数（biz_content下的可选字段）
    .batchOptional(optionalArgs)
    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```
#### 多种扩展灵活组合

```java
// 多种扩展方式可灵活组装（对扩展方法的调用顺序没有要求）
Factory.Payment.FaceToFace()
    .agent("ca34ea491e7146cc87d25fca24c4cD11")
    .asyncNotify("https://www.test.com/callback")
    .optional("seller_id", "2088102146225135")
    .preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

## API组织规范
在Alipay Easy SDK中，API的引用路径与能力地图的组织层次一致，遵循如下规范

> Factory.能力名称.场景名称().接口方法名称( ... )

比如，如果您想要使用[能力地图](https://opendocs.alipay.com/mini/00am3f)中`营销能力`下的`模板消息`场景中的`小程序发送模板消息`，只需按如下形式编写调用代码即可。

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
