<?php

use Channel\Client;
use Channel\Server;
use Workerman\Worker;
use Workerman\Timer;

// composer autoload
include __DIR__ . '/../vendor/autoload.php';

$channel_server = new Server();

$worker = new Worker();
$worker->onWorkerStart = function()
{
    Client::connect();

    Client::on('test event', function($event_data){
        echo 'test event triggered event_data :';
        var_dump($event_data);
    });

    Timer::add(2, function(){
        Client::publish('test event', 'some data');
    });
};

Worker::runAll();
