# ChangeLog - Aliyun OSS SDK for PHP

## v2.3.0 / 2018-01-05

* 修复：putObject支持创建空文件
* 修复：createBucket支持IA/Archive
* 增加：支持restoreObject
* 增加：支持Symlink功能
* 增加：支持getBucketLocation
* 增加：支持getBucketMeta
* 增加：支持代理服务器Proxy

## v2.2.4 / 2017-04-25

* fix getObject to local file bug

## v2.2.3 / 2017-04-14

* fix md5 check

## v2.2.2 / 2017-01-18

* 解决在php7上运行连接数和内存bug

## v2.2.1 / 2016-12-01

* 禁止http curl自动填充Accept-Encoding

## v2.2.0 / 2016-11-22

* 修复PutObject/CompleteMultipartUpload的返回值问题(#26)

## v2.1.0 / 2016-11-12

* 增加[RTMP](https://help.aliyun.com/document_detail/44297.html)接口
* 增加支持[图片服务](https://help.aliyun.com/document_detail/44686.html)

## v2.0.7 / 2016-06-17

* Support append object

## v2.0.6

* Trim access key id/secret and endpoint
* Refine tests and setup travis CI

## v2.0.5

* 增加Add/Delete/Get BucketCname接口

## v2.0.4

* 增加Put/Get Object Acl接口

## v2.0.3

* 修复Util中的常量定义在低于5.6的PHP版本中报错的问题

## v2.0.2

* 修复multipart上传时无法指定Content-Type的问题

## v2.0.1

* 增加对ListObjects/ListMultipartUploads时特殊字符的处理
* 提供接口获取OssException中的详细信息


## 2015.11.25

* **大版本升级，不再兼容以前接口，新版本对易用性做了很大的改进，建议用户迁移到新版本。**

## 修改内容

* 不再支持PHP 5.2版本

### 新增内容

* 引入命名空间
* 接口命名修正，采用驼峰式命名
* 接口入参修改，把常用参数从Options参数中提出来
* 接口返回结果修改，对返回结果进行处理，用户可以直接得到容易处理的数据结构　
* OssClient的构造函数变更
* 支持CNAME和IP格式的Endpoint地址
* 重新整理sample文件组织结构，使用function组织功能点
* 增加设置连接超时，请求超时的接口
* 去掉Object Group相关的已经过时的接口
* OssException中的message改为英文

### 问题修复

* object名称校验不完备
