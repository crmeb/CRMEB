PHP Container & Facade Manager( Support PSR-11)
===============

[![Build Status](https://travis-ci.org/top-think/think-container.svg?branch=2.0)](https://travis-ci.org/top-think/think-container)
[![Latest Stable Version](https://poser.pugx.org/topthink/think-container/v/stable)](https://packagist.org/packages/topthink/think-container)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.1-8892BF.svg)](http://www.php.net/)

## 安装
~~~
composer require topthink/think-container
~~~

## 特性

* 支持PSR-11规范
* 支持依赖注入
* 支持Facade门面
* 支持容器对象绑定
* 支持闭包绑定
* 支持接口绑定

## Container
~~~
// 获取容器实例
$container = \think\Container::getInstance();
// 绑定一个类、闭包、实例、接口实现到容器
$container->bind('cache', '\app\common\Cache');
// 判断是否存在对象实例
$container->has('cache');
// 从容器中获取对象的唯一实例
$container->get('cache');
// 从容器中获取对象，没有则自动实例化
$container->make('cache');
// 删除容器中的对象实例
$container->delete('cache');
// 执行某个方法或者闭包 支持依赖注入
$container->invoke($callable, $vars);
// 执行某个类的实例化 支持依赖注入
$container->invokeClass($class, $vars);
// 静态方法获取容器对象实例 不存在则自动实例化
\think\Container::pull('cache');
~~~

对象化操作
~~~
// 获取容器实例
$container = \think\Container::getInstance();
// 绑定一个类、闭包、实例、接口实现到容器
$container->cache = '\app\common\Cache';
// 判断是否存在对象实例
isset($container->cache);
// 从容器中获取对象的唯一实例
$container->cache;
// 删除容器中的对象实例
unset($container->cache);
~~~

## Facade


定义一个`app\facade\App`类之后，即可以静态方式调用`\think\App`类的动态方法
~~~
<?php
namespace think;
class App 
{
	public function name(){
		return 'app';
	}
}
~~~

~~~
<?php
namespace app\facade;

use think\Facade;

class App extends Facade
{
    /**
     * 获取当前Facade对应类名
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
	return '\think\App';
    }
}
~~~

然后就可以静态方式调用动态方法了
~~~
use app\facade\App;

echo App::name(); // app
~~~
