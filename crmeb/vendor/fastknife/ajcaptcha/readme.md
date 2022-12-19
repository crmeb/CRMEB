AJ-Captcha · php
----

#### 介绍

这个类库使用 PHP实现了行为验证码。基于gd扩展生成滑动验证码和文字验证码。允许 phper定制验证码规则，并且不再使用 curl来请求第三方验证。

Java实现： https://gitee.com/anji-plus/captcha

PHP实现： https://gitee.com/fastknife/aj-captcha

##### 官方预览效果

![block](https://gitee.com/anji-plus/captcha/raw/master/images/%E6%BB%91%E5%8A%A8%E6%8B%BC%E5%9B%BE.gif) &emsp;&emsp;![click](https://gitee.com/anji-plus/captcha/raw/master/images/%E7%82%B9%E9%80%89%E6%96%87%E5%AD%97.gif)

#### 注意事项
* 你需要打开 gd、 openssl扩展
* PHP版本至少需要7.1
* 此软件包自带缓存，如有需要请自行更换
* anji-plus/captcha前端默认请求头是 application/json 需替换为 application/x-www-form-urlencode

* 滑动验证图响应时间慢？
   > 性能慢的主要原因是受php GD库的imagecolorat函数与imagesetpixel函数性能的影响
  1. 您可以尝试将修改内存`ini_set('memory_limit', '256M')`
  2. 将本包升级到1.1.x版本，开启像素缓存`block_puzzle.is_cache_pixel = true`, 若还是性能还是慢，则将干扰图片关闭`block_puzzle.is_interfere => false`
#### 如何使用
test 目录下示例了三种使用方式，phper可以参考使用。[查看demo](./demo.md)
> 本软件包需要配合composer一起使用
1. 非框架使用的场景，直接使用git下载这个软件包。然后执行composer命令`composer install`安装本软件包依赖，接着手动引入对应的 service层文件即可（同test目录里的原生引用方式）。

2. 基于框架使用的场景,输入安装命令`composer require fastknife/ajcaptcha`（稳定版） 或者`composer require fastknife/ajcaptcha dev-master`（最新版） ，建议使用composer阿里源（`https://mirrors.aliyun.com/composer`）
    * 支持各种前沿框架（ThinkPHP, YII, Laravel, Hyperf，IMI,Swoft,EasySwoole）
    * 本软件包内，未使用单例、注册树（容器）模式，不含任何全局变量，基于swoole开发的同学不用担心内存泄露。

#### 项目结构
> 本软件包基于整洁架构理念，设计了下文的目录结构。Domain(领域层)作为内层同心圆承担所有业务逻辑功能，Service（服务层）并向最外层Controller（需自行实现）提供粗颗粒度服务。  
    区别于DDD(领域驱动设计),本软件包的领域层不含Entity(实体)，以Logic（逻辑层）实现单元逻辑，为了方便管理作者将逻辑层的数据处理与图形处理分隔，以达到整洁效果。
```
AJ-Captcha for php
│
├─resources 资源
│	│
│	├─defaultImages 图片资源
│	│
│	└─fonts 字体
│
├─src 源码
│	│
│	├─Domian 领域层
│	│
│	├─Exception 异常
│	│
│	├─Service 服务层
│	│
│	├─ Utils工具类
│	│
│	└─ config.php 配置参考
└─test 测试实例
	│
	├─thinkphp thinkphp框架测试示例
	│
	├─laravel laravel框架测试示例
	│
	└─*.php 原生测试文件 配置文件
```

#### [更新日志](./changelog.md)

若此软件对您有所帮助，您可以点右上角 💘Star💘支持
