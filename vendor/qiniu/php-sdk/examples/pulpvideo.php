<?php

require_once __DIR__ . '/../autoload.php';

use Qiniu\Auth;
use Qiniu\Http\Client;

$accessKey = getenv('QINIU_ACCESS_KEY');
$secretKey = getenv('QINIU_SECRET_KEY');
$auth = new Auth($accessKey, $secretKey);
$config = new \Qiniu\Config();
$argusManager = new \Qiniu\Storage\ArgusManager($auth, $config);

$reqBody = array();
$reqBody['uri'] = "xxxx";

$ops = array();
$ops = array(
    array(
        'op' => 'pulp',
        'params' => array(
            'labels' => array(
                array(
                    'label' => "1",
                    'select' => 1,
                    'score' => 2,
                ),
            )
        )
    ),
);

$params = array();
$params = array(
    'async' => false,
    'vframe' => array(
        'mode' => 1,
        'interval' => 8,
    )
);

$req = array();
$req['data'] = $reqBody;
$req['ops'] = $ops;
$req['params'] = $params;
$body = json_encode($req);

$vid = "xxxx";
list($ret, $err) = $argusManager->pulpVideo($body, $vid);

if ($err !== null) {
    var_dump($err);
} else {
    var_dump($ret);
}
