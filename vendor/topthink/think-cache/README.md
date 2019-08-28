# think-cache

用于PHP缓存管理（PHP 7.1+），支持`PSR-6`及`PSR-16`缓存规范。

## 安装
~~~
composer require topthink/think-cache
~~~

## 用法：
~~~php
use think\facade\Cache;

// 缓存配置
Cache::config([
	'default'	=>	'file',
	'stores'	=>	[
		'file'	=>	[
			'type'   => 'File',
			// 缓存保存目录
			'path'   => './cache/',
			// 缓存前缀
			'prefix' => '',
			// 缓存有效期 0表示永久缓存
			'expire' => 0,
		],
		'redis'	=>	[
			'type'   => 'redis',
			'host'   => '127.0.0.1',
			'port'   => 6379,
			'prefix' => '',
			'expire' => 0,
		],
	],
]);
// 设置缓存
Cache::set('val','value',600);
// 判断缓存是否设置
Cache::has('val');
// 获取缓存
Cache::get('val');
// 删除缓存
Cache::delete('val');
// 清除缓存
Cache::clear();
// 读取并删除缓存
Cache::pull('val');
// 不存在则写入
Cache::remember('val',10);

// 对于数值类型的缓存数据可以使用
// 缓存增+1
Cache::inc('val');
// 缓存增+5
Cache::inc('val',5);
// 缓存减1
Cache::dec('val');
// 缓存减5
Cache::dec('val',5);

// 使用缓存标签
Cache::tag('tag_name')->set('val','value',600);
// 删除某个标签下的缓存数据
Cache::tag('tag_name')->clear();
// 支持指定多个标签
Cache::tag(['tag1','tag2'])->set('val2','value',600);
// 删除多个标签下的缓存数据
Cache::tag(['tag1','tag2'])->clear();

// 使用多种缓存类型
$redis = Cache::store('redis');

$redis->set('var','value',600);
$redis->get('var');
~~~
