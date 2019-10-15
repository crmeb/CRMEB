# Channel
基于订阅的多进程通讯组件，用于workerman进程间通讯或者服务器集群通讯，类似redis订阅发布机制。基于workerman开发。

# 手册地址
[Channel手册](http://doc3.workerman.net/component/channel.html)

# 服务端
```php
use Workerman\Worker;

$channel_server = new Channel\Server('0.0.0.0', 2206);

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
```

# 客户端
```php
use Workerman\Worker;

$worker = new Worker(....);
$worker->onWorkerStart = function()
{
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('<Channel服务端ip>', 2206);
    // 要订阅的事件名称（名称可以为任意的数字和字符串组合）
    $event_name = 'event_xxxx';
    // 订阅某个自定义事件并注册回调，收到事件后会自动触发此回调
    Channel\Client::on($event_name, function($event_data){
        var_dump($event_data);
    });
};
$worker->onMessage = function($connection, $data)
{
    // 要发布的事件名称
    $event_name = 'event_xxxx';
    // 事件数据（数据格式可以为数字、字符串、数组），会传递给客户端回调函数作为参数
    $event_data = array('some data.', 'some data..');
    // 发布某个自定义事件，订阅这个事件的客户端会收到事件数据，并触发客户端对应的事件回调
    Channel\Client::publish($event_name, $event_data);
};

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
````
