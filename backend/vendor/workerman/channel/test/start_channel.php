<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2022/2/20
 * Time: 12:00
 */

include_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;

$processName = "ChannelServerTest";
Worker::$pidFile = "var/{$processName}.pid";
Worker::$logFile = "var/{$processName}_logFile.log";
Worker::$stdoutFile = "var/{$processName}_stdout.log";

$channel_server = new Channel\Server('0.0.0.0', 2206);

if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
