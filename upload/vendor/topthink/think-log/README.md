# think-log

写入LOG日志，要求PHP7.1+。

## 主要特性

* 多通道日志写入
* 日志实时/延时写入
* 日志信息格式化
* 日志信息处理机制
* JSON格式支持
* 日志自动清理

## 安装

~~~
composer require topthink/think-log
~~~

## 用法

~~~php
use think\facade\Log;

Log::init([
	'default'	=>	'file',
	'channels'	=>	[
		'file'	=>	[
			'type'	=>	'file',
			'path'	=>	'./logs/',
		],
	],
]);

Log::error('error info');
Log::info('log info');
Log::save();
~~~
