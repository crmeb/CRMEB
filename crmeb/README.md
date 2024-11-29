CRMEB v5 后端程序目录
===============

> 运行环境要求PHP7.1-7.4。

## 安装
## 宝塔Docker一键安装
1.安装宝塔面板，前往 [宝塔面板](https://www.bt.cn/new/download.html?r=dk_crmeb) 官网，选择正式版的脚本下载安装

2.安装后登录宝塔面板，在菜单栏中点击 Docker ，首次进入会提示安装 Docker 服务，点击立即安装，按提示完成安装

3.安装完成后在应用商店中找到crmeb  ，点击安装，配置域名等基本信息即可完成安装

## 一键安装
上传你的代码，站点入口目录设置/public
在浏览器中输入你的域名或IP（例如：www.yourdomain.com）,
安装程序会自动执行安装。期间系统会提醒你输入数据库信息以完成安装，安装完成后建议删除install目录。

后台访问地址：
1.域名/admin
公众号和H5首页访问地址：
1.域名/

安装过程中请牢记您的账号密码！

## 重新安装
1. 清除数据库
2. 删除/public/install.lock 文件

## 手动安装
1.创建数据库，倒入数据库文件
数据库文件目录/public/install/crmeb.sql
2.修改数据库连接文件
配置文件路径/.env
~~~
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1 #数据库连接地址
HOSTPORT = 3306 #数据库端口
DATABASE = test #数据库名称
USERNAME = username #数据库登录账号
PASSWORD = password #数据库登录密码
PREFIX = eb_
CHARSET = utf8mb4
DEBUG = true

[LANG]
default_lang = zh-cn

[CACHE]
DRIVER = file #缓存类型，redis/file
CACHE_PREFIX = cache_xxxx: #缓存前缀
CACHE_TAG_PREFIX = cache_tag_xxxx: #缓存类型前缀

[REDIS]
REDIS_HOSTNAME = 127.0.0.1 #redis链接地址
PORT = 6379 #端口号
REDIS_PASSWORD = 123456 #密码
SELECT = 0 #数据库

[QUEUE]
QUEUE_NAME = xxxx #队列前缀
~~~
3.修改目录权限（linux系统）777
/crmeb
/template

4.后台登录：
http://域名/admin
默认账号：admin 密码：crmeb.com


## 消息队列
```sh
php think queue:listen --queue
```

## 定时任务
在自动收货,库存预警等功能使用到
```sh
php think timer [ status ] [ --d ]
```
参数
- status: 状态
  - start: 启动
  - stop: 关闭
  - restart: 重启
- --d : 后台执行

## 长连接服务
在h5聊天,后台管理员消息通知等功能使用到
```sh
php think workerman [ status ]  [ --d ]
```
windows环境下需要分三步执行
```sh
# 内部通讯服务
php think workerman start --d
```
参数
- status: 状态
  - start: 启动
  - stop: 关闭
  - restart: 重启
- --d : 后台执行

## 开发规范
#### 命名规范
ThinkPHP6.0遵循PSR-2命名规范和PSR-4自动加载规范，并且注意如下规范:

1. 目录和文件
2. 目录使用小写+下划线；
3. 类库、函数文件统一以.php为后缀；
4. 类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
5. 类（包含接口和Trait）文件采用驼峰法命名（首字母大写），其它文件采用小写+下划线命名；
6. 类名（包括接口和Trait）和文件名保持一致，统一采用驼峰法命名（首字母大写）；

#### 函数和类、属性命名

1. 类的命名采用驼峰法（首字母大写），例如 User、UserType；
2. common函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip；
3. 控制器里面的方法使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip
4. 方法的命名使用驼峰法（首字母小写），例如 getUserName；
5. 属性的命名使用驼峰法（首字母小写），例如 tableName、instance；
6. 特例：以双下划线__打头的函数或方法作为魔术方法，例如 __call 和 __autoload；

#### 常量和配置
1. 常量以大写字母和下划线命名，例如 APP_PATH；
2. 配置参数以小写字母和下划线命名，例如 url_route_on 和url_convert；
3. 环境变量定义使用大写字母和下划线命名，例如APP_DEBUG；

#### 数据表和字段
1. 数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 think_user 表和 user_name字段，不建议使用驼峰和中文作为数据表及字段命名

注意：请理解并尽量遵循以上命名规范，可以减少在开发过程中出现不必要的错误

#### 语法规范
1. 尽量使用php7新语法
2. 每个 namespace 命名空间声明语句和 use 声明语句块后面，必须 插入一个空白行
3. 类的开始花括号（{） 必须 写在类声明后自成一行，结束花括号（}）也 必须 写在类主体后自成一行
4. 方法的开始花括号（{） 必须 写在函数声明后自成一行，结束花括号（}）也 必须 写在函数主体后自成一行。
5. 类的属性和方法 必须 添加访问修饰符（private、protected 以及 public），abstract 以及 final 必须 声明在访问修饰符之前，而 static 必须 声明在访问修饰符之后
6. 控制结构的关键字后 必须 要有一个空格符，而调用方法或函数时则 一定不可 有
7. 控制结构的开始花括号（{） 必须 写在声明的同一行，而结束花括号（}） 必须 写在主体后自成一行
8. 纯 PHP 代码文件 必须 省略最后的 ?> 结束标签
9. 所有方法，类，控制器类，都 必须 添加访问修饰符
    ~~~

    /**
     * 中文注释
     * @param string $str 声明类型
     * @param array $arr
     * @return bool
     */
    public function action(string $str, array $arr)
    {
         return true;
    }
    ~~~
10. 参数列表中，每个逗号后面 必须 要有一个空格，而逗号前面 一定不可 有空格
    ~~~
     function foo($arg1, &$arg2, $arg3 = [])
     {
            // method body
     }
    ~~~
11. 参数 可以 分列成多行，此时包括第一个参数在内的每个参数都 尽量 单独成行。
    ~~~
    <?php
    $foo->bar(
        $longArgument,
        $longerArgument,
        $muchLongerArgument
    );
    ~~~
12. 标准的 if 结构如下代码所示，请留意「括号」、「空格」以及「花括号」的位置，
    注意 else 和 elseif 都与前面的结束花括号在同一行
    ~~~
    <?php
    if ($expr1) {
        // if body
    } elseif ($expr2) {
        // elseif body
    } else {
        // else body;
    }
    ~~~
13. 赋值等号前后必须加空格符
    ~~~
    <?php
    $arr = [];
    ~~~


#### PHP 7.1+ 常用新语法

1. 三元运算符
   ~~~
   <?php

   $arr = ['crmeb'=>true];
   之前
   echo isset($arr['crmeb']) ? $arr['crmeb'] : '';
   之后
   echo $arr['crmeb'] ?? '';
   ~~~
2.  define() 定义常量数组
   ~~~
   <?php
    define('ARR',['a','b']);
   ~~~
3.  命名空间优化
   ~~~
    <?php
    //PHP7之前语法
    use FooLibrary\Bar\Baz\ClassA;
    use FooLibrary\Bar\Baz\ClassB;
    // PHP7新语法写法
    use FooLibrary\Bar\Baz\{ ClassA, ClassB};

   ~~~
#### CRMEB PRO规范
 1. 所有数据验证放在模块下的 validates 目录下
 2. JSON返回使用父级 AuthController类中的success 和 fail
 3. 错误判断抛出异常，由一个错误类统一控制输出
    ~~~
    <?php

        throw new AuthException('错误信息',400);
    ~~~
 4. 错误码和错误提示语应该统一管理，方便切换多语言
 5. 数据库操作使用模型类，不能使用Db::table()
 6. 获取表单数据使用 app\Request
    ~~~
    <?php
    use app\Request;


    public function index(Request $request) {

        //获取提交的数据，并以二维数组形式返回
        $arr = $request->getMore([
            'name',
            'nickname'
        ]);
        //获取提交的数据，并以二维数组形式返回并附加默认值
        $arr = $request->getMore([
           ['name','123'],
           ['nickname','0']
        ]);
        //获取提交的数据,并以一维数组形式返回并附加默认值
        [$name, $nickname] = $request->getMore([
           ['name','123'],
           ['nickname','0']
        ],true);

    }
    ~~~
 7. 所有控制器类命令和表名对应，按照大驼峰命名规范
 8. 所有文件夹命名按照小写字母加下划线定义
 9. 所有属性名，变量名尽量遵守小驼峰命名规范
 10. 复杂逻辑，多状态应适当添加行内注释
 11. 模型里只能写关于搜索条件语句,查出数据得组合书写在services层进行处理,services创建命令:php make:services api@user/User


## 文档

[使用手册](https://doc.crmeb.com)
[TP6开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)


## 参与开发

请参阅 [CRMEB](https://github.com/crmeb/CRMEB)。

## 版权信息


本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2023 by CRMEB (http://www.crmeb.com)

All rights reserved。

CRMEB® 商标和著作权所有者为西安众邦网络科技有限公司。
