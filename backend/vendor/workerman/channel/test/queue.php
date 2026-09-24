<?php

use Channel\Client;
use Channel\Server;
use Workerman\Worker;
use Workerman\Timer;

// composer autoload
include __DIR__ . '/../vendor/autoload.php';

$channel_server = new Server();

$worker = new Worker();
$worker->name = 'Event';
$worker->onWorkerStart = function()
{
	Client::connect();

	$count = 0;
	$timerId = Timer::add(0.01, function() use (&$timerId, &$count) {
		Client::publish('test event', 'some data');
		$count++;
		Client::enqueue('task-queue', time());
		if ($count == 1000) {
			Timer::del($timerId);
		}
	});

	Timer::add(10, function() {
		Client::enqueue('task-queue', 'hello every 10 seconds');
	});
};

$mq = new Worker();
$mq->name = 'Queue';
$mq->count = 4;
$mq->onWorkerStart = function($worker) {
	Client::connect();
	$countDown = 20;
	$id = 1;
	Client::watch('task-queue', function($data) use ($worker, &$countDown, &$id) {
		echo "[$id] Worker {$worker->id} get queue: $data\n";
		sleep(0.2);
		$countDown--;
		$id++;
		if ($worker->id > 1 && $countDown == 0) {
			Client::unwatch('task-queue');
		}
		Timer::add(1, [Client::class, 'reserve'], [], false);
	});
};

Worker::runAll();
