<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->add('Songshenzong\\Support\\Test\\', __DIR__);
