# 基础能力 Base
## 用户授权 OAuth
### 获取授权访问令牌
* API声明

getToken(code: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| code | string | 是  |  授权码，用户对应用授权后得到  |

* 出参说明

可前往[alipay.system.oauth.token](https://docs.open.alipay.com/api_9/alipay.system.oauth.token)查看更加详细的参数说明。

### 刷新授权访问令牌
* API声明

refreshToken(refreshToken: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| refreshToken | string | 是  |  刷新令牌，上次换取访问令牌时得到，见出参的refresh_token字段  |

* 出参说明

可前往[alipay.system.oauth.token](https://docs.open.alipay.com/api_9/alipay.system.oauth.token)查看更加详细的参数说明。

---

## 小程序二维码 Qrcode
### 创建小程序二维码
* API声明

create(urlParam: string, queryParam: string, describe: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| urlParam | string | 是  |  小程序中能访问到的页面路径，例如：page/component/component-pages/view/view  |
| queryParam | string | 是  |  小程序的启动参数，打开小程序的query ，在小程序 onLaunch的方法中获取  |
| describe | string | 是  |  二维码描述  |

* 出参说明

可前往[alipay.open.app.qrcode.create](https://docs.open.alipay.com/api_5/alipay.open.app.qrcode.create)查看更加详细的参数说明。

---

## 图片 Image
### 上传图片
* API声明

upload(imageName: string, imageFilePath: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| imageName | string | 是  |  图片名称  |
| imageFilePath | string | 是  |  待上传的本地图片文件路径 |

* 出参说明

可前往[alipay.offline.material.image.upload](https://docs.open.alipay.com/api_3/alipay.offline.material.image.upload)查看更加详细的参数说明。

---

## 视频 Video
### 上传视频
* API声明

upload(videoName: string, videoFilePath: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| videoName | string | 是  |  视频名称  |
| videoFilePath | string | 是  |  待上传的本地视频文件路径 |

* 出参说明

可前往[alipay.offline.material.image.upload](https://docs.open.alipay.com/api_3/alipay.offline.material.image.upload)查看更加详细的参数说明。

---

# 营销能力 Marketing
## 生活号 OpenLife
### 创建图文消息内容
* API声明

createImageTextContent(title: string, cover: string, content: string, contentComment: string, ctype: string, benefit: string, extTags: string, loginIds: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| title | string | 是  |  标题  |
| cover | string | 是  | 封面图URL, 尺寸为996*450，最大不超过3M，支持.jpg、.png格式，请先调用上传图片接口获得图片URL  |
| content | string | 是  |  消息正文（支持富文本）  |
| contentComment | string | 否  |  是否允许评论，T：允许，F：不允许，默认不允许  |
| ctype | string | 否  |  图文类型：填activity表示活动图文，不填默认普通图文  |
| benefit | string | 否  |  活动利益点，图文类型ctype为activity类型时才需要传，最多10个字符  |
| extTags | string | 否  |  关键词列表，英文逗号分隔，最多不超过5个  |
| loginIds | string | 否  |  可预览支付宝账号列表，需要预览时才填写， 英文逗号分隔，最多不超过10个  |

* 出参说明

可前往[alipay.open.public.message.content.create](https://docs.open.alipay.com/api_6/alipay.open.public.message.content.create)查看更加详细的参数说明。

### 更新图文消息内容
* API声明

modifyImageTextContent(contentId: string, title: string, cover: string, content: string, couldComment: string, ctype: string, benefit: string, extTags: string, loginIds: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| contentId | string | 是  |  内容ID，通过创建图文内容消息接口返回  |
| title | string | 是  |  标题  |
| cover | string | 是  | 封面图URL, 尺寸为996*450，最大不超过3M，支持.jpg、.png格式，请先调用上传图片接口获得图片URL  |
| content | string | 是  |  消息正文（支持富文本）  |
| contentComment | string | 否  |  是否允许评论，T：允许，F：不允许，默认不允许  |
| ctype | string | 否  |  图文类型：填activity表示活动图文，不填默认普通图文  |
| benefit | string | 否  |  活动利益点，图文类型ctype为activity类型时才需要传，最多10个字符  |
| extTags | string | 否  |  关键词列表，英文逗号分隔，最多不超过5个  |
| loginIds | string | 否  |  可预览支付宝账号列表，需要预览时才填写， 英文逗号分隔，最多不超过10个  |

* 出参说明

可前往[alipay.open.public.message.content.modify](https://docs.open.alipay.com/api_6/alipay.open.public.message.content.modify)查看更加详细的参数说明。

### 群发本文消息
* API声明

sendText(text: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| text | string | 是  |  文本消息内容  |

* 出参说明

可前往[alipay.open.public.message.total.send](https://docs.open.alipay.com/api_6/alipay.open.public.message.total.send)查看更加详细的参数说明。

### 群发图文消息
* API声明

sendImageText(articles: [ Article ])
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| articles | Article数组 | 是  |  图文消息内容  |

Article对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| title | string | 否  |   图文消息标题 |
| desc | string | 是  |  图文消息描述   |
| imageUrl | string | 特殊可选  |  图片链接，对于多条图文消息的第一条消息，该字段不能为空，请先调用上传图片接口获得图片URL  |
| url | string | 是  |  点击图文消息跳转的链接  |
| actionName | string | 否  |  链接文字   |

* 出参说明

可前往[alipay.open.public.message.total.send](https://docs.open.alipay.com/api_6/alipay.open.public.message.total.send)查看更加详细的参数说明。

### 单发模板消息
* API声明

sendSingleMessage(toUserId: string, template: Template)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| toUserId | string | 是  |  消息接收用户的UserId  |
| template | Template | 是  |  消息接收用户的UserId  |

Template对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| templateId | string | 是  |  消息模板ID |
| context | Context | 是  |  消息模板上下文，即模板中定义的参数及参数值 |

Context对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| headColor | string | 是  |  顶部色条的色值，比如#85be53 |
| url | string | 是  |  点击消息后承接页的地址 |
| actionName | string | 是 |  底部链接描述文字，如：“查看详情”，最多能传8个汉字或16个英文字符 |
| keyword1 | Keyword | 否  |  模板中占位符的值及文字颜色 |
| keyword2 | Keyword | 否  |  模板中占位符的值及文字颜色 |
| first | Keyword | 否  |  模板中占位符的值及文字颜色  |
| remark | Keyword | 否  |  模板中占位符的值及文字颜色 |

Keyword对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| color | string | 是  | 当前文字颜色，比如#85be53 |
| value | string | 是  |  模板中占位符的值  |

* 出参说明

可前往[alipay.open.public.message.single.send](https://docs.open.alipay.com/api_6/alipay.open.public.message.single.send)查看更加详细的参数说明。

### 生活号消息撤回
* API声明

recallMessage(messageId: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| messageId | string | 是  |  消息ID  |

* 出参说明

可前往[alipay.open.public.life.msg.recall](https://docs.open.alipay.com/api_6/alipay.open.public.life.msg.recall)查看更加详细的参数说明。

### 模板消息行业设置
* API声明

setIndustry(primaryIndustryCode: string, primaryIndustryName: string, secondaryIndustryCode: string, secondaryIndustryName: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| primaryIndustryCode | string | 是  |  服务窗消息模板所属主行业一级编码，查看[行业信息](https://alipay.open.taobao.com/doc2/detail?treeId=197&docType=1&articleId=105043)  |
| primaryIndustryName | string | 是  |  服务窗消息模板所属主行业一级名称  |
| secondaryIndustryCode | string | 是  |  服务窗消息模板所属主行业二级编码 |
| secondaryIndustryName | string | 是  |  服务窗消息模板所属主行业二级名称  |

* 出参说明

可前往[alipay.open.public.template.message.industry.modify](https://docs.open.alipay.com/api_6/alipay.open.public.template.message.industry.modify)查看更加详细的参数说明。

### 生活号查询行业设置
* API声明

getIndustry()
			
* 入参说明

无

* 出参说明

可前往[alipay.open.public.setting.category.query](https://docs.open.alipay.com/api_6/alipay.open.public.setting.category.query)查看更加详细的参数说明。

---


## 支付宝卡包 Pass
### 卡券模板创建
* API声明

createTemplate(uniqueId: string, tplContent: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| uniqueId | string | 是  |  商户用于控制模版的唯一性（可以使用时间戳保证唯一性）  |
| tplContent | string | 是  |  模板内容信息，遵循JSON规范，详情参见tpl_content[参数说明](https://doc.open.alipay.com/doc2/detail.htm?treeId=193&articleId=105249&docType=1#tpl_content)   |

* 出参说明

可前往[alipay.pass.template.add](https://docs.open.alipay.com/api_24/alipay.pass.template.add)查看更加详细的参数说明。

### 卡券模板更新
* API声明

updateTemplate(uniqueId: string, tplContent: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| uniqueId | string | 是  |  商户用于控制模版的唯一性（可以使用时间戳保证唯一性）  |
| tplContent | string | 是  |  模板内容信息，遵循JSON规范，详情参见tpl_content[参数说明](https://doc.open.alipay.com/doc2/detail.htm?treeId=193&articleId=105249&docType=1#tpl_content)   |

* 出参说明

可前往[alipay.pass.template.update](https://docs.open.alipay.com/api_24/alipay.pass.template.update)查看更加详细的参数说明。

### 卡券实例发放
* API声明

addInstance(tplId: string, tplParams: string, recognitionType: string, recognitionInfo: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| tplId | string | 是  |  支付宝pass模版ID，即调用模板创建接口时返回的tpl_id  |
| tplParams | string | 是  |  模版动态参数信息，对应模板中$变量名$的动态参数，见模板创建接口返回值中的tpl_params字段。示例：  |
| recognitionType | string | 是  |  Alipass添加对象识别类型，填写“1”表示订单信息  |
| recognitionInfo | string | 是  |  支付宝用户识别信息，参见[UID发券组件对接文档](https://docs.open.alipay.com/199/sy3hs4 ) |

* 出参说明

可前往[alipay.pass.instance.add](https://docs.open.alipay.com/api_24/alipay.pass.instance.add)查看更加详细的参数说明。

### 卡券实例更新
* API声明

updateInstance(serialNumber: string, channelId: string, tplParams: string, status: string, verifyCode: string, verifyType: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| serialNumber | string | 是  |  商户指定卡券唯一值，卡券JSON模板中fileInfo->serialNumber字段对应的值  |
| channelId | string | 是  |  代理商代替商户发放卡券后，再代替商户更新卡券时，此值为商户的PID/AppID  |
| tplParams | string | 否  |  Alipass添加对象识别类型，填写“1”表示订单信息  |
| status | string | 否  |  券状态，支持更新为USED、CLOSED两种状态 |
| verifyCode | string | 否  |  核销码串值（当状态变更为USED时，建议传），该值正常为模板中核销区域（Operation）对应的message值 |
| verifyType | string | 否  |  核销方式，该值正常为模板中核销区域（Operation）对应的format值，verifyCode和verifyType需同时传入 |

* 出参说明

可前往[alipay.pass.instance.update](https://docs.open.alipay.com/api_24/alipay.pass.instance.update)查看更加详细的参数说明。

---


## 小程序模板消息 TemplateMessage
### 发送模板消息
* API声明

send(toUserId: string, formId: string, userTemplateId: string, page: string, data: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| toUserId | string | 是  |  发送消息的支付宝账号  |
| formId | string | 是  |  用户发生的交易行为的交易号，或者用户在小程序产生表单提交的表单号，用于信息发送的校验  |
| userTemplateId | string | 是  |  用户申请的模板id号，固定的模板id会发送固定的消息  |
| page | string | 是  |  小程序的跳转页面，用于消息中心用户点击之后详细跳转的小程序页面，例如：page/component/index |
| data | string | 是  |  开发者需要发送模板消息中的自定义部分来替换模板的占位符，例如：{"keyword1": {"value" : "12:00"},"keyword2": {"value" : "20180808"},"keyword3": {"value" : "支付宝"}}  |

* 出参说明

可前往[alipay.open.app.mini.templatemessage.send](https://docs.open.alipay.com/api_5/alipay.open.app.mini.templatemessage.send)查看更加详细的参数说明。

---


# 会员能力 Member
## 支付宝身份认证 Identification
### 身份认证初始化
* API声明

init(outerOrderNo: string, bizCode: string, identityParam: IdentityParam, merchantConfig: MerchantConfig)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outerOrderNo | string | 是  |  商户请求的唯一标识，商户要保证其唯一性，值为32位长度的字母数字组合，建议前面几位字符是商户自定义的简称，中间可以使用一段时间，后段可以使用一个随机或递增序列  |
| bizCode | string | 是  |  认证场景码，入参支持的认证场景码和商户签约的认证场景相关，可选值有如下，FACE：多因子人脸认证；CERT_PHOTO：多因子证照认证；CERT_PHOTO_FACE：多因子证照和人脸认证；SMART_FACE：多因子快捷认证  |
| identityParam | IdentityParam | 是  |   需要验证的身份信息参数  |
| merchantConfig | MerchantConfig | 是  |  商户个性化配置  |

IdentityParam对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| identityType | string | 是  |  身份信息参数类型，必须传入CERT_INFO  |
| certType | string | 是  |  证件类型，当前支持身份证，必须传入IDENTITY_CARD  |
| certName | string | 是  |  真实姓名 |
| certNo | string | 是  |  证件号码  |

MerchantConfig对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| returnUrl | string | 是  |  需要回跳的目标URL地址，一般指定为商户业务页面  |

* 出参说明

可前往[alipay.user.certify.open.initialize](https://docs.open.alipay.com/api_2/alipay.user.certify.open.initialize)查看更加详细的参数说明。

### 生成认证链接
* API声明

certify(certifyId: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| certifyId | string | 是  |  本次申请操作的唯一标识，由身份认证初始化接口调用后生成，后续的操作都需要用到  |

* 出参说明

可前往[alipay.user.certify.open.certify](https://docs.open.alipay.com/api_2/alipay.user.certify.open.certify)查看更加详细的参数说明。

### 身份认证记录查询
* API声明

query(certifyId: string)
			
* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| certifyId | string | 是  |  身份认证操作的唯一标识，由身份认证初始化接口调用后生成  |

* 出参说明

可前往[alipay.user.certify.open.query](https://docs.open.alipay.com/api_2/alipay.user.certify.open.query)查看更加详细的参数说明。

---


# 支付能力 Payment
## 通用接口 Common
### 创建交易
* API声明

create(subject: string, outTradeNo: string, totalAmount: string, buyerId: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  | 商户订单号，64个字符以内，可包含字母、数字、下划线，需保证在商户端不重复  |
| totalAmount | string | 是  | 订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |
| buyerId | string | 是 | 买家的支付宝唯一用户号（2088开头的16位纯数字）  |

* 出参说明

可前往[alipay.trade.create](https://docs.open.alipay.com/api_1/alipay.trade.create)查看更加详细的参数说明。

### 查询交易
* API声明

query(outTradeNo: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |

* 出参说明

可前往[alipay.trade.query](https://docs.open.alipay.com/api_1/alipay.trade.query)查看更加详细的参数说明。

### 交易退款
* API声明

refund(outTradeNo: string, refundAmount: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| refundAmount | string | 是  |  需要退款的金额，该金额不能大于订单金额，单位为元，支持两位小数  |

* 出参说明

可前往[alipay.trade.refund](https://docs.open.alipay.com/api_1/alipay.trade.refund)查看更加详细的参数说明。

### 关闭交易
* API声明

close(outTradeNo: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |

* 出参说明

可前往[alipay.trade.close](https://docs.open.alipay.com/api_1/alipay.trade.close)查看更加详细的参数说明。

### 撤销交易
* API声明

cancel(outTradeNo: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |

* 出参说明

可前往[alipay.trade.cancel](https://docs.open.alipay.com/api_1/alipay.trade.cancel)查看更加详细的参数说明。

### 交易退款查询
* API声明

queryRefund(outTradeNo: string, outRequestNo: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| outRequestNo | string | 是  |  请求退款接口时，传入的退款请求号，如果在退款请求时未传入，则该值为创建交易时的外部交易号  |

* 出参说明

可前往[alipay.trade.fastpay.refund.query](https://opendocs.alipay.com/apis/api_1/alipay.trade.fastpay.refund.query)查看更加详细的参数说明。


### 查询对账单下载地址
* API声明

downloadBill(billType: string, billDate: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| billType | string | 是  |  账单类型，商户通过接口或商户经开放平台授权后其所属服务商通过接口可以获取以下账单类型：trade、signcustomer；trade指商户基于支付宝交易收单的业务账单；signcustomer是指基于商户支付宝余额收入及支出等资金变动的帐务账单  |
| billDate | string | 是  |  账单时间：日账单格式为yyyy-MM-dd，最早可下载2016年1月1日开始的日账单；月账单格式为yyyy-MM，最早可下载2016年1月开始的月账单  |

* 出参说明

可前往[alipay.data.dataservice.bill.downloadurl.query](https://opendocs.alipay.com/apis/api_15/alipay.data.dataservice.bill.downloadurl.query)查看更加详细的参数说明。


### 异步通知验签
* API声明

verifyNotify(parameters: map[string]string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| parameters | map[string]string | 是  |  异步通知中收到的待验签的所有参数 |

---

## 花呗分期 Huabei
### 创建花呗分期交易
* API声明

create(subject: string, outTradeNo: string, totalAmount: string, buyerId: string, extendParams: HuabeiConfig)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  | 商户订单号，64个字符以内，可包含字母、数字、下划线，需保证在商户端不重复  |
| totalAmount | string | 是  | 订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |
| buyerId | string | 是 | 买家的支付宝用户ID，如果为空，会从传入的码值信息中获取买家ID  |
| extendParams | HuabeiConfig | 是  |  花呗交易扩展参数  |

HuabeiConfig对象说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| hbFqNum | string | 是  | 花呗分期数，仅支持传入3、6、12  |
| hbFqSellerPercent | string | 是  | 代表卖家承担收费比例，商家承担手续费传入100，用户承担手续费传入0，仅支持传入100、0两种  |


* 出参说明

可前往[alipay.trade.create](https://docs.open.alipay.com/api_1/alipay.trade.create)查看更加详细的参数说明。

--- 

<a name="faceToFace"/>

## 当面付 FaceToFace
### 当面付交易付款
* API声明

pay(subject: string, outTradeNo: string, totalAmount: string, authCode: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| totalAmount | string | 是  |  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |
| authCode | string | 是  |  支付授权码，即买家的付款码数字  |

* 出参说明

可前往[alipay.trade.pay](https://docs.open.alipay.com/api_1/alipay.trade.pay)查看更加详细的参数说明。

* 返佣说明

ISV对接当面付产品需涉及返佣时，请先阅读[政策详情](https://opendocs.alipay.com/p/00fc2g)与[合作攻略](https://opendocs.alipay.com/open/300/taphxd)。


**对接时必须在支付接口的extend_params参数中设置sys_service_provider_id返佣参数 ，参数值为签约返佣协议的PID**，示例代码如下（Java为例）：

```java
Map<String, String> extendParams = new HashMap<>();
extendParams.put("sys_service_provider_id", "<--请填写ISV签约协议的PID，比如：2088511833207846-->");
AlipayTradePayResponse response = Factory.Payment.FaceToFace()
	.agent("<--请填写商户应用授权后获取到的app_auth_token，比如：ca34ea491e7146cc87d25fca24c4cD11-->")
	.optional("extend_params", extendParams)
	.pay("iPhone6 16G", "64628156-f784-4572-9540-485b7c91b850", "0.01", "289821051157962364");
```

--- 
### 交易预创建，生成正扫二维码
* API声明

precreate(subject: string, outTradeNo: string, totalAmount: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| totalAmount | string | 是  |  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |

* 出参说明

可前往[alipay.trade.precreate](https://docs.open.alipay.com/api_1/alipay.trade.precreate)查看更加详细的参数说明。

* 返佣说明

ISV对接当面付产品需涉及返佣时，请先阅读[政策详情](https://opendocs.alipay.com/p/00fc2g)与[合作攻略](https://opendocs.alipay.com/open/300/taphxd)。


**对接时必须在支付接口的extend_params参数中设置sys_service_provider_id返佣参数 ，参数值为签约返佣协议的PID**，示例代码如下（Java为例）：

```java
Map<String, String> extendParams = new HashMap<>();
extendParams.put("sys_service_provider_id", "<--请填写ISV签约协议的PID，比如：2088511833207846-->");
AlipayTradePrecreateResponse response = Payment.FaceToFace()
	.agent("<--请填写商户应用授权后获取到的app_auth_token，比如：ca34ea491e7146cc87d25fca24c4cD11-->")
	.optional("extend_params", extendParams)
	.preCreate("iPhone6 16G", "64628156-f784-4572-9540-485b7c91b850", "0.01");
```

--- 
## 电脑网站 Page
### 电脑网站支付
* API声明

pay(subject: string, outTradeNo: string, totalAmount: string, returnUrl: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| totalAmount | string | 是  |  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |
| returnUrl | string | 否  |  支付成功后同步跳转的页面，是一个http/https开头的字符串  |

* 出参说明

可前往[alipay.trade.page.pay](https://docs.open.alipay.com/api_1/alipay.trade.page.pay)查看更加详细的参数说明。

--- 

## 手机网站 Wap
### 手机网站支付
* API声明

pay(subject: string, outTradeNo: string, totalAmount: string, quitUrl: string, returnUrl: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| totalAmount | string | 是  |  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |
| quitUrl | string | 是  |  用户付款中途退出返回商户网站的地址  |
| returnUrl | string | 否 |  支付成功后同步跳转的页面，是一个http/https开头的字符串  |

* 出参说明

可前往[alipay.trade.wap.pay](https://docs.open.alipay.com/api_1/alipay.trade.wap.pay)查看更加详细的参数说明。

--- 

## App支付 App
### 手机APP支付
* API声明

pay(subject: string, outTradeNo: string, totalAmount: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| subject | string | 是  |  订单标题  |
| outTradeNo | string | 是  |  交易创建时传入的商户订单号  |
| totalAmount | string | 是  |  订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000]  |

* 出参说明

可前往[alipay.trade.app.pay](https://docs.open.alipay.com/api_1/alipay.trade.app.pay)查看更加详细的参数说明。

---

# 安全能力 Security
## 文本风险识别 TextRisk
### 检测内容风险
* API声明

detect(content: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| content | string | 是  |  待检测的文本内容 |

* 出参说明

可前往[alipay.security.risk.content.detect](https://docs.open.alipay.com/api_49/alipay.security.risk.content.detect)查看更加详细的参数说明。

---

# 辅助工具 Util


## 加解密 AES
### AES解密（常用于会员手机号解密）
* API声明

decrypt(cipherText: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| cipherText | string | 是  |  密文 |

* 出参说明

| 类型     |  说明 |
|------|----|
| string | 明文|

### AES加密
* API声明

encrypt(plainText: string)

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| plainText | string | 是  |  明文 |

* 出参说明

| 类型     |  说明 |
|------|----|
| string | 密文|


<a name="generic"/>

## 通用接口 Generic
### 执行OpenAPI调用
* API声明

execute(method: string, textParams: map[string]string, bizParams: map[string]any): AlipayOpenApiGenericResponse

* 接口说明

对于Alipay Easy SDK尚未支持的Open API，开发者可以通过调用此方法，通过自行拼装请求参数，完成大部分OpenAPI的调用，且调用时可按需设置所有可选参数。本接口同样会自动为您完成请求的加签和响应的验签工作。
注：本接口不支持文件型字段的上传。

* 入参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| method | string | 是  |  OpenAPI的名称，例如：alipay.trade.pay |
| textParams | map[string]string | 否  |  **没有**包装在`biz_content`下的请求参数集合，例如`app_auth_token`等参数 |
| bizParams | map[string]any | 否  |  被包装在`biz_content`下的请求参数集合 |

* 出参说明

| 字段名  | 类型     | 必填 | 说明 |
|------|--------|----|----|
| httpBody | string | 是  |  网关返回的HTTP响应，是一个JSON格式的字符串，开发者可按需从中解析出响应参数，响应示例：{"alipay_trade_create_response":{"code":"10000","msg":"Success","out_trade_no":"4ac9eac...","trade_no":"202003..."},"sign":"AUumfYgGSe7...02MA=="} |
| code | string | 是  |  [网关返回码](https://docs.open.alipay.com/common/105806) |
| msg | string | 是  |  [网关返回码描述](https://docs.open.alipay.com/common/105806) |
| subCode | string | 否  |  业务返回码，参见具体的API接口文档 |
| subMsg | string | 否  |  业务返回码描述，参见具体的API接口文档 |

---




