<?php
use Workerman\Worker;
require __DIR__ . '/../vendor/autoload.php';

use Workerman\Crontab\Crontab;

$worker = new Worker();

$worker->onWorkerStart = function () {
    // Execute the function in the first second of every minute.
    new Crontab('1 * * * * *', function(){
        echo date('Y-m-d H:i:s')."\n";
    });
};

Worker::runAll();
