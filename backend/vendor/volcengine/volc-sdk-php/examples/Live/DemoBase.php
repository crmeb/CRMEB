<?php

use Volc\Service\Live;

require('../../vendor/autoload.php');

$client = Live::getInstance();

$ak = '';
$sk = '';
$client->setAccessKey($ak);
$client->setSecretKey($sk);