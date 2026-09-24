<?php

include_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Connection\TcpConnection;
use Workerman\Connection\AsyncUdpConnection;
use Workerman\Connection\AsyncTcpConnection;

//监听端口
$worker = new Worker("");

//开启进程数量
$worker->count = 8;
$processName = "client";
$worker->name = $processName;
$worker->reusePort = true;   //开启均衡负载模式

Worker::$pidFile = "var/{$processName}.pid";
Worker::$logFile = "var/{$processName}_logFile.log";
Worker::$stdoutFile = "var/{$processName}_stdout.log";

$worker->onWorkerStart = function() use($worker){
    usleep(10);
    Channel\Client::connect('127.0.0.1' , 2206);
    $event_name = "test_channel";
    Channel\Client::on($event_name, function($event_data)use($worker ,$event_name ){
        $log_str = "{$worker->id} on {$event_name}:".json_encode($event_data,320)."\n";
        echo $log_str;
    });
};

Worker::runAll();