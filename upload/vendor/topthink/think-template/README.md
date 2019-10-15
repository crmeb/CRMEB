# think-template

基于XML和标签库的编译型模板引擎

## 主要特性

- 支持XML标签库和普通标签的混合定义；
- 支持直接使用PHP代码书写；
- 支持文件包含；
- 支持多级标签嵌套；
- 支持布局模板功能；
- 一次编译多次运行，编译和运行效率非常高；
- 模板文件和布局模板更新，自动更新模板缓存；
- 系统变量无需赋值直接输出；
- 支持多维数组的快速输出；
- 支持模板变量的默认值；
- 支持页面代码去除Html空白；
- 支持变量组合调节器和格式化功能；
- 允许定义模板禁用函数和禁用PHP语法；
- 通过标签库方式扩展；

## 安装

~~~php
composer require topthink/think-template
~~~

## 用法示例

在根目录下创建index.php入口文件测试：
~~~php
<?php
namespace think;

require __DIR__.'/vendor/autoload.php';

// 设置模板引擎参数
$config = [
	'view_path'	=>	'./template/',
	'cache_path'	=>	'./runtime/',
	'view_suffix'   =>	'html',
];

$template = new Template($config);
// 模板变量赋值
$template->assign('name','think');
// 读取模板文件渲染输出
$template->fetch('index');
// 完整模板文件渲染
$template->fetch('./template/test.php');
// 渲染内容输出
$template->display($content);
~~~

详细用法参考[这里](https://www.kancloud.cn/manual/thinkphp5_1/354069)