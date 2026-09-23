[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk?ref=badge_shield)
[![Maven Central](https://img.shields.io/maven-central/v/com.alipay.sdk/alipay-easysdk.svg)](https://mvnrepository.com/artifact/com.alipay.sdk/alipay-easysdk)
[![NuGet](https://badge.fury.io/nu/AlipayEasySDK.svg)](https://badge.fury.io/nu/AlipayEasySDK)
[![Packagist](https://poser.pugx.org/alipaysdk/easysdk/v/stable)](https://packagist.org/packages/alipaysdk/easysdk)

欢迎使用 Alipay **Easy** SDK。

打造**最好用**的支付宝开放平台**服务端SDK**，Alipay Easy SDK让您享受**极简编程**体验，快速访问支付宝开放平台开放的各项**核心能力**。

## 设计理念
不同于原有的Alipay SDK通用而全面的设计理念，Alipay Easy SDK对开放能力的API进行了更加贴近高频场景的精心设计与裁剪，简化了服务端调用方式，让调用API像使用语言内置的函数一样简便。

同时，您也不必担心面向高频场景提炼的API可能无法完全契合自己的个性化场景，Alipay Easy SDK支持灵活的[动态扩展](#extension)方式，同样可以满足低频参数、低频API的使用需求。

Alipay Easy SDK提供了与[能力地图](https://opendocs.alipay.com/mini/00am3f)相对应的代码组织结构，让开发者可以快速找到不同能力对应的API。

Alipay Easy SDK主要目标是提升开发者在**服务端**集成支付宝开放平台开放的各类核心能力的效率。

### 化繁为简

| Alipay Easy SDK  | Alipay SDK                                                     |
|------------------|----------------------------------------------------------------|
| 极简代码风格，更贴近自然语言阅读习惯  | 传统代码风格，需要多行代码完成一个接口的调用 |
| Factory单例全局任何地方都可直接引用 | AlipayClient实例需自行创建并在上下文中传递 |
| API中只保留高频场景下的必备参数，同时提供低频可选参数的装配能力    | 没有区分高低频参数，单API最多可达数十个入参，对普通开发者的干扰较大 |


* Alipay Easy SDK :smiley:

```java
Factory.Payment.Common().create("Iphone6 16G", "202003019443", "0.10", "2088002656718920");
```

* Alipay SDK :confused:

```java
AlipayTradeCreateRequest request = new AlipayTradeCreateRequest();

AlipayTradeCreateModel model = new AlipayTradeCreateModel();
model.setSubject("Iphone6 16G");
model.setOutTradeNo("202003019443");
model.setTotalAmount("0.10");
model.setBuyerId("2088002656718920");
...

request.setBizModel(model);
...

alipayClient.execute(request);
```

### 如何切换
* 无论是Alipay Easy SDK还是Alipay SDK，本质都是发送HTTP请求访问Open API网关，所以只需将原来通过Alipay SDK调用Open API的代码，替换为Alipay Easy SDK中对应API的调用即可。Alipay Easy SDK和Alipay SDK并无冲突，可以共存。

* 如果您所需对接的开放平台能力，Alipay Easy SDK尚未提炼出API支持（[已支持的API列表](#apiList)），您可以通过[通用接口](./APIDoc.md#generic)完成调用。

* 我们会持续挖掘高频场景，不断丰富Alipay Easy SDK支持的API，让您在绝大多数常见场景下，都能享受Alipay Easy SDK带来的便捷。

## 技术特点
### 纯语言开发
所有Alipay Easy SDK的具体编程语言的实现，均只采用纯编程语言进行开发，不引入任何重量级框架，减少潜在的框架冲突，让SDK可以自由集成进任何代码环境中。

### 结构清晰
我们按照能力类别和场景类别对API进行了归类，结构更加清晰，一目了然。
> 更多信息请参见[API组织规范](#spec)。

### 参数精简
Alipay Easy SDK对每个API都精心打磨，剔除了`Open API`中不常用的可选参数，减少普通用户的无效选择，提升开发效率。

<a name="extension"/>

### 灵活扩展
开发者可以通过Fluent风格的API链式调用，在为高频场景打造的API基础上，不断扩展自己的个性化场景需求。

```java
// 通过调用agent方法，扩展支持ISV代调用场景
Factory.Payment.FaceToFace().agent("ca34ea491e7146cc87d25fca24c4cD11").preCreate(...)

// 通过调用optional方法，扩展支持个性化可选参数
Factory.Payment.FaceToFace().optional("extend_params", extendParams).preCreate(...)

// 多种扩展可灵活搭配，不同扩展方法功能详细说明请前往各语言主页中的“快速开始-扩展调用”栏目中查看
Factory.Payment.FaceToFace()
	.agent(...)
	.optionalArgs(...)
	.auth(...)
	.asyncNotify(...)
	.preCreate(...)
```

### 测试/示例完备
每个API都有对应的单元测试进行覆盖，良好的单元测试天生就是最好的示例。

同时您也可以前往[API Doc](./APIDoc.md)查看每个API的详细使用说明。

> 注：单元测试中使用到的私钥均进行了脱敏处理，会导致单元测试无法直接执行。您可以自行更改单元测试项目中的`TestAccout类`和`privateKey.json`文件中的相关账号与私钥配置后再执行单元测试。

### 多语言
Alipay Easy SDK基于阿里集团研发的[`Darabonba`](https://github.com/aliyun/darabonba)进行架构，通过DSL中间语言定义API模型，再基于DSL语言自动生成不同编程语言（Java、C#、PHP、TS等）实现的SDK，极大地提升了SDK能力的扩展效率和适用范围，同时也保证了相同的`Easy API`在不同语言生态中体验的一致性。

API模型的DSL描述可以进入[tea](./tea)目录查看。

### 快速集成
各语言SDK均会在各自的中央仓库（Maven、NuGet、Composer、NPM etc.）中同步发布，让您使用各语言主流依赖管理工具即可一键安装集成SDK。

## 语言支持情况
Alipay Easy SDK首发暂只支持`Java`、`C#`、`PHP`编程语言，更多编程语言支持正在积极新增中，敬请期待。

各语言具体的**使用说明**和**详细介绍**请点击如下链接进入各语言主目录查看。

[Java](./java)

[C#](./csharp)

[PHP](./php)

<a name="spec"/>

## API组织规范

在Alipay Easy SDK中，API的引用路径与能力地图的组织层次一致，遵循如下规范

> Factory.能力类别.场景类别.接口方法名称( ... )

比如，如果您想要使用[能力地图](https://opendocs.alipay.com/mini/00am3f)中`营销能力`下的`模板消息`场景中的`小程序发送模板消息`，只需按如下形式编写调用代码即可（不同编程语言的连接符号可能不同）。

`Factory.Marketing.TemplateMessage().send( ... )`

其中，接口方法名称通常是对其依赖的OpenAPI功能的一个最简概况，接口方法的出入参与OpenAPI中同名参数含义一致，可参照OpenAPI相关参数的使用说明。

Alipay Easy SDK将致力于保持良好的API命名，以符合开发者的编程直觉。

<a name="apiList"/>

## 已支持的API列表

| 能力类别      | 场景类别            | 接口方法名称                 | 调用的OpenAPI名称                                              |
|-----------|-----------------|------------------------|-----------------------------------------------------------|
| Base<br/>基础能力      | OAuth<br/>用户授权           | getToken<br/>获取授权访问令牌和用户user_id               | alipay\.system\.oauth\.token                              |
| Base<br/>基础能力     | OAuth<br/>用户授权           | refreshToken<br/>刷新授权访问令牌           | alipay\.system\.oauth\.token                              |
| Base<br/>基础能力      | Qrcode<br/>小程序二维码          | create<br/>创建小程序二维码                 | alipay\.open\.app\.qrcode\.create                         |
| Base<br/>基础能力      | Image<br/>图片           | upload<br/>上传门店照片                 | alipay\.offline\.material\.image\.upload                  |
| Base<br/>基础能力      | Video<br/>视频           | upload<br/>上传门店视频                 | alipay\.offline\.material\.image\.upload                  |
| Member<br/>会员能力    | Identification<br/>支付宝身份认证  | init<br/>身份认证初始化                   | alipay\.user\.certify\.open\.initialize                   |
| Member<br/>会员能力    | Identification<br/>支付宝身份认证  | certify<br/>生成认证链接                | alipay\.user\.certify\.open\.certify                      |
| Member<br/>会员能力    | Identification<br/>支付宝身份认证  | query<br/>身份认证记录查询                  | alipay\.user\.certify\.open\.query                        |
| Payment<br/>支付能力   | Common<br/>通用          | create<br/>创建交易                 | alipay\.trade\.create                                     |
| Payment<br/>支付能力   | Common<br/>通用          | query<br/>查询交易                  | alipay\.trade\.query                                      |
| Payment<br/>支付能力   | Common<br/>通用          | refund<br/>交易退款                 | alipay\.trade\.refund                                     |
| Payment<br/>支付能力   | Common<br/>通用          | close<br/>关闭交易                  | alipay\.trade\.close                                      |
| Payment<br/>支付能力   | Common<br/>通用          | cancel<br/>撤销交易                 | alipay\.trade\.cancel                                     |
| Payment<br/>支付能力   | Common<br/>通用          | queryRefund<br/>交易退款查询            | alipay\.trade\.fastpay\.refund\.query                     |
| Payment<br/>支付能力   | Common<br/>通用          | downloadBill<br/>查询对账单下载地址           | alipay\.data\.dataservice\.bill\.downloadurl\.query       |
| Payment<br/>支付能力   | Common<br/>通用          | verifyNotify<br/>异步通知验签           | -                                                         |
| Payment<br/>支付能力   | Huabei<br/>花呗分期          | create<br/>创建花呗分期交易                 | alipay\.trade\.create                                     |
| Payment<br/>支付能力   | FaceToFace<br/>当面付      | pay<br/>扫用户出示的付款码，完成付款                    | alipay\.trade\.pay                                        |
| Payment<br/>支付能力   | FaceToFace<br/>当面付      | precreate<br/>生成交易付款码，待用户扫码付款              | alipay\.trade\.precreate                                  |
| Payment<br/>支付能力   | App<br/>手机APP             | pay<br/>生成订单串，再使用客户端 SDK 凭此串唤起支付宝收银台                    | alipay\.trade\.app\.pay                                   |
| Payment<br/>支付能力   | Page<br/>电脑网站            | pay<br/>生成交易表单，渲染后自动跳转支付宝网站引导用户完成支付                    | alipay\.trade\.page\.pay                                  |
| Payment<br/>支付能力   | Wap<br/>手机网站             | pay<br/>生成交易表单，渲染后自动跳转支付宝网站引导用户完成支付                    | alipay\.trade\.wap\.pay                                   |
| Security<br/>安全能力  | TextRisk<br/>文本内容安全        | detect<br/>检测内容风险                 | alipay\.security\.risk\.content\.detect                   |
| Marketing<br/>营销能力 | Pass<br/>支付宝卡包            | createTemplate<br/>卡券模板创建         | alipay\.pass\.template\.add                               |
| Marketing<br/>营销能力 | Pass<br/>支付宝卡包            | updateTemplate<br/>卡券模板更新         | alipay\.pass\.template\.update                            |
| Marketing<br/>营销能力 | Pass<br/>支付宝卡包            | addInstance<br/>卡券实例发放            | alipay\.pass\.instance\.add                               |
| Marketing<br/>营销能力 | Pass<br/>支付宝卡包            | updateInstance<br/>卡券实例更新         | alipay\.pass\.instance\.update                            |
| Marketing<br/>营销能力 | TemplateMessage<br/>小程序模板消息 | send <br/>发送模板消息| alipay\.open\.app\.mini\.templatemessage\.send            |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | createImageTextContent<br/>创建图文消息内容 | alipay\.open\.public\.message\.content\.create            |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | modifyImageTextContent<br/>更新图文消息内容 | alipay\.open\.public\.message\.content\.modify            |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | sendText<br/>群发本文消息               | alipay\.open\.public\.message\.total\.send                |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | sendImageText<br/>群发图文消息          | alipay\.open\.public\.message\.total\.send                |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | sendSingleMessage<br/>单发模板消息      | alipay\.open\.public\.message\.single\.send               |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | recallMessage<br/>生活号消息撤回          | alipay\.open\.public\.life\.msg\.recall                   |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | setIndustry<br/>模板消息行业设置            | alipay\.open\.public\.template\.message\.industry\.modify |
| Marketing<br/>营销能力 | OpenLife<br/>生活号        | getIndustry<br/>生活号查询行业设置            | alipay\.open\.public\.setting\.category\.query            |
| Util<br/>辅助工具      | AES<br/>加解密             | decrypt<br/>解密，常用于会员手机号解密                | -                                                         |
| Util<br/>辅助工具      | AES<br/>加解密             | encrypt<br/>加密                | -                                                         |
| Util<br/>辅助工具      | Generic<br/>通用接口         | execute<br/>自行拼接参数，执行OpenAPI调用                | -                                                         |

> 注：更多高频场景的API持续更新中，敬请期待。

您还可以前往[API Doc](./APIDoc.md)查看每个API的详细使用说明。

# 变更日志
每个版本的详细更改记录在[变更日志](./CHANGELOG)中。

> 版本号最末一位修订号的增加（比如从`1.0.0`升级为`1.0.1`），意味着SDK的功能没有发生任何变化，仅仅是修复了部分Bug。该类升级可能不会记录在变更日志中。

> 版本号中间一位次版本号的增加（比如从`1.0.0`升级为`1.1.0`），意味着SDK的功能发生了可向下兼容的新增或修改。

> 版本号首位主版本号的增加（比如从`1.0.0`升级为`2.0.0`），意味着SDK的功能可能发生了不向下兼容的较大调整，升级主版本号后请注意做好相关的回归测试工作。

# 相关
* [支付宝开放平台](https://open.alipay.com/platform/home.htm)
* [支付宝开放平台文档中心](https://docs.open.alipay.com/catalog)
* [最新源码](https://github.com/alipay/alipay-easysdk)

# 许可证
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Falipay%2Falipay-easysdk?ref=badge_large)

# 交流与技术支持
不管您在使用Alipay Easy SDK的过程中遇到任何问题，欢迎前往 [支付宝开放社区](https://forum.alipay.com/mini-app/channel/1100001)  发帖与支付宝工作人员和其他开发者一起交流。

注：为了提高开发者问题的响应时效，github本身的issue功能已关闭，支付宝开放社区中发帖的问题，通常会在2小时内响应。
