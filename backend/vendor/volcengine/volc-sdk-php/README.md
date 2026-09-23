<h1 align="center"><img src="https://iam.volccdn.com/obj/volcengine-public/pic/volcengine-icon.png"></h1>
<h1 align="center">火山引擎SDK for PHP</h1> 
欢迎使用火山引擎SDK for PHP，本文档为您介绍如何获取及调用SDK。

## 前置准备
### 服务开通
请确保您已开通了您需要访问的服务。您可前往[火山引擎控制台](https://console.volcengine.com/ )，在左侧菜单中选择或在顶部搜索栏中搜索您需要使用的服务，进入服务控制台内完成开通流程。
### 获取安全凭证
Access Key（访问密钥）是访问火山引擎服务的安全凭证，包含Access Key ID（简称为AK）和Secret Access Key（简称为SK）两部分。您可登录[火山引擎控制台](https://console.volcengine.com/ )，前往“[访问控制](https://console.volcengine.com/iam )”的“[访问密钥](https://console.volcengine.com/iam/keymanage/ )”中创建及管理您的Access Key。更多信息可参考[访问密钥帮助文档](https://www.volcengine.com/docs/6291/65568 )。
###环境检查
PHP版本需要不低于7.1。
## 获取与安装
推荐使用composer安装火山引擎SDK for PHP：
```shell
$ composer require volcengine/volc-sdk-php
```
## 相关配置
### 安全凭证配置
火山引擎SDK for PHP支持以下几种方式进行凭证管理：

*注意：代码中Your AK及Your SK需要分别替换为您的AK及SK。*

**方式一**：在Client中显式指定AK/SK **（推荐）**
```php
$client = Iam::getInstance();
$client->setAccessKey(Your AK);
$client->setSecretKey(Your SK);
```

**方式二**：从环境变量加载AK/SK
  ```bash
  VOLC_ACCESSKEY="Your AK"  
  VOLC_SECRETKEY="Your SK"
  ```
**方式三**：从HOME文件加载AK/SK

在本地的~/.volc/config中添加如下内容：
  ```json
    {
      "ak": "Your AK",
      "sk": "Your SK"
    }
  ```

##其它资源
示例参见[examples](./examples)



