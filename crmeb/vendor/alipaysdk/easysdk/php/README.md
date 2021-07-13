[![Latest Stable Version](https://poser.pugx.org/alipaysdk/easysdk/v/stable)](https://packagist.org/packages/alipaysdk/easysdk)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk?ref=badge_shield)

欢迎使用 Alipay **Easy** SDK for PHP 。

Alipay Esay SDK for PHP让您不用复杂编程即可访支付宝开放平台开放的各项常用能力，SDK可以自动帮您满足能力调用过程中所需的证书校验、加签、验签、发送HTTP请求等非功能性要求。

下面向您介绍Alipay Easy SDK for PHP 的基本设计理念和使用方法。

## 设计理念
不同于原有的[Alipay SDK](https://openhome.alipay.com/doc/sdkDownload.resource?sdkType=PHP)通用而全面的设计理念，Alipay Easy SDK对开放能力的API进行了更加贴近高频场景的精心设计与裁剪，简化了服务端调用方式，让调用API像使用语言内置的函数一样简便。

Alipay Easy SDK提供了与[能力地图](https://opendocs.alipay.com/mini/00am3f)相对应的代码组织结构，让开发者可以快速找到不同能力对应的API。

Alipay Easy SDK主要目标是提升开发者在**服务端**集成支付宝开放平台开放的各类核心能力的效率。

## 环境要求
1. Alipay Easy SDK for PHP 需要配合`PHP 7.0`或其以上版本。

2. 使用 Alipay Easy SDK for PHP 之前 ，您需要先前往[支付宝开发平台-开发者中心](https://openhome.alipay.com/platform/developerIndex.htm)完成开发者接入的一些准备工作，包括创建应用、为应用添加功能包、设置应用的接口加签方式等。

3. 准备工作完成后，注意保存如下信息，后续将作为使用SDK的输入。

* 加签模式为公钥证书模式时（推荐）

`AppId`、`应用的私钥`、`应用公钥证书文件`、`支付宝公钥证书文件`、`支付宝根证书文件`

* 加签模式为公钥模式时

`AppId`、`应用的私钥`、`支付宝公钥`

## 安装依赖
### 通过[Composer](https://packagist.org/packages/alipaysdk/easysdk/)在线安装依赖（推荐）

`composer require alipaysdk/easysdk:^2.0`

### 本地手动集成依赖（适用于自己修改源码后的本地重新打包安装）
1. 本机安装配置[Composer](https://getcomposer.org/)工具。
2. 在本`README.md`所在目录下，执行`composer install`，下载SDK依赖。
3. 依赖文件会下载到`vendor`目录下。

## 快速使用
以下这段代码示例向您展示了使用Alipay Easy SDK for PHP调用一个API的3个主要步骤：

1. 设置参数（全局只需设置一次）。
2. 发起API调用。
3. 处理响应或异常。

```php
<?php

require 'vendor/autoload.php';
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Kernel\Config;

//1. 设置参数（全局只需设置一次）
Factory::setOptions(getOptions());

try {
    //2. 发起API调用（以支付能力下的统一收单交易创建接口为例）
    $result = Factory::payment()->common()->create("iPhone6 16G", "20200326235526001", "88.88", "2088002656718920");
    $responseChecker = new ResponseChecker();
    //3. 处理响应或异常
    if ($responseChecker->success($result)) {
        echo "调用成功". PHP_EOL;
    } else {
        echo "调用失败，原因：". $result->msg."，".$result->subMsg.PHP_EOL;
    }
} catch (Exception $e) {
    echo "调用失败，". $e->getMessage(). PHP_EOL;;
}

function getOptions()
{
    $options = new Config();
    $options->protocol = 'https';
    $options->gatewayHost = 'openapi.alipay.com';
    $options->signType = 'RSA2';
    
    $options->appId = '<-- 请填写您的AppId，例如：2019022663440152 -->';
    
    // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
    $options->merchantPrivateKey = '<-- 请填写您的应用私钥，例如：MIIEvQIBADANB ... ... -->';
    
    $options->alipayCertPath = '<-- 请填写您的支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt -->';
    $options->alipayRootCertPath = '<-- 请填写您的支付宝根证书文件路径，例如：/foo/alipayRootCert.crt" -->';
    $options->merchantCertPath = '<-- 请填写您的应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt -->';
    
    //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
    // $options->alipayPublicKey = '<-- 请填写您的支付宝公钥，例如：MIIBIjANBg... -->';

    //可设置异步通知接收服务地址（可选）
    $options->notifyUrl = "<-- 请填写您的支付类接口异步通知接收服务地址，例如：https://www.test.com/callback -->";
    
    //可设置AES密钥，调用AES加解密相关接口时需要（可选）
    $options->encryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->";



    return $options;
}

```

### 扩展调用
#### ISV代调用

```php
Factory::payment()->faceToFace()
    // 调用agent扩展方法，设置app_auth_token，完成ISV代调用
    ->agent("ca34ea491e7146cc87d25fca24c4cD11")
    ->preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置独立的异步通知地址

```php
Factory::payment()->faceToFace()
    // 调用asyncNotify扩展方法，可以为每此API调用，设置独立的异步通知地址
    // 此处设置的异步通知地址的优先级高于全局Config中配置的异步通知地址
    ->asyncNotify("https://www.test.com/callback")
    ->preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

#### 设置可选业务参数

```php
$goodDetail = array(
            "goods_id" => "apple-01",
            "goods_name" => "iPhone6 16G",
            "quantity" => 1,
            "price" => "5799"
        );
        $goodsDetail[0] = $goodDetail;

Factory::payment()->faceToFace()
    // 调用optional扩展方法，完成可选业务参数（biz_content下的可选字段）的设置
    ->optional("seller_id", "2088102146225135")
    ->optional("discountable_amount", "8.88")
    ->optional("goods_detail", $goodsDetail)
    ->preCreate("Apple iPhone11 128G", "2234567890", "5799.00");


$optionalArgs = array(
            "timeout_express" => "10m",
            "body" => "Iphone6 16G"
        );

Factory::payment()->faceToFace()
    // 也可以调用batchOptional扩展方法，批量设置可选业务参数（biz_content下的可选字段）
    ->batchOptional($optionalArgs)
    ->preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```
#### 多种扩展灵活组合

```php
// 多种扩展方式可灵活组装（对扩展方法的调用顺序没有要求）
Factory::payment()->faceToFace()
    ->agent("ca34ea491e7146cc87d25fca24c4cD11")
    ->asyncNotify("https://www.test.com/callback")
    ->optional("seller_id", "2088102146225135")
    ->preCreate("Apple iPhone11 128G", "2234567890", "5799.00");
```

## API组织规范
在Alipay Easy SDK中，API的引用路径与能力地图的组织层次一致，遵循如下规范

> Factory::能力名称()->场景名称()->接口方法名称( ... )

比如，如果您想要使用[能力地图](https://opendocs.alipay.com/mini/00am3f)中`营销能力`下的`模板消息`场景中的`小程序发送模板消息`，只需按如下形式编写调用代码即可（不同编程语言的连接符号可能不同）。

`Factory::marketing()->templateMessage()->send( ... )`

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
