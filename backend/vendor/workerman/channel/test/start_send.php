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
$worker->count = 1;
$processName = "send";
$worker->name = $processName;
$worker->reusePort = true;   //开启均衡负载模式

Worker::$pidFile = "var/{$processName}.pid";
Worker::$logFile = "var/{$processName}_logFile.log";
Worker::$stdoutFile = "var/{$processName}_stdout.log";

$worker->onWorkerStart = function() use($worker){
    Channel\Client::connect('127.0.0.1' , 2206);
    Timer::add( 1 , function ()use($worker){
        $data_arr = [
            'time' => microtime(true),
            'date' => date("Y-m-d H:i:s"),
        ];
        $event_name = "test_channel";
        Channel\Client::publish($event_name, $data_arr , true);
    });
};
Worker::runAll();