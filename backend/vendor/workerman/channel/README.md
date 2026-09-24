# Channel
基于订阅的多进程通讯组件，用于workerman进程间通讯或者服务器集群通讯，类似redis订阅发布机制。基于workerman开发。

Channel 提供两种通讯形式，分别是发布订阅的事件机制和消息队列机制。

它们的主要区别是：
- 事件机制是消息发出后，所有订阅该事件的客户端都能收到消息。
- 消息队列机制是消息发出后，所有订阅该消息的客户端只有一个会收到消息，如果客户端忙消息会进行排队直到有客户端闲置后重新取到消息。
- 需要注意的是 Channel 只是提供一种通讯方式，本身并不提供消息确认、重试、延迟、持久化等功能，请根据实际情况合理使用。

# 手册地址
[Channel手册](http://doc.workerman.net/components/channel.html)

# 服务端
```php
use Workerman\Worker;

//Tcp 通讯方式
$channel_server = new Channel\Server('0.0.0.0', 2206);

//Unix Domain Socket 通讯方式
//$channel_server = new Channel\Server('unix:///tmp/workerman-channel.sock');

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
```

# 客户端
```php
use Workerman\Worker;

$worker = new Worker();
$worker->onWorkerStart = function()
{
    // Channel客户端连接到Channel服务端
    Channel\Client::connect('<Channel服务端ip>', 2206);

    // 使用 Unix Domain Socket 通讯
    //Channel\Client::connect('unix:///tmp/workerman-channel.sock');

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

## 消息队列示例
```php
use Workerman\Worker;
use Workerman\Timer;

$worker = new Worker();
$worker->name = 'Producer';
$worker->onWorkerStart = function()
{
    Client::connect();

    $count = 0;
    Timer::add(1, function() {
        Client::enqueue('queue', 'Hello World '.time());
    });
};

$mq = new Worker();
$mq->name = 'Consumer';
$mq->count = 4;
$mq->onWorkerStart = function($worker) {
    Client::connect();

    //订阅消息 queue
    Client::watch('queue', function($data) use ($worker) {
        echo "Worker {$worker->id} get queue: $data\n";
    });

    //10 秒后取消订阅该消息
    Timer::add(10, function() {
        Client::unwatch('queue');
    }, [], false);
};

Worker::runAll();
```