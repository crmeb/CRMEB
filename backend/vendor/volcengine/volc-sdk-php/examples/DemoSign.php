<?php
require('../vendor/autoload.php');

$sign = new \Volc\Base\SignatureV4();
$credentials = [];
$credentials['region'] = 'cn-north-1';
$credentials['service'] = 'iam';
$credentials['ak'] = $ak;
$credentials['sk'] = $sk;

$req = new \Volc\Base\Model\SignParam();
$req->setDate(new DateTime("20211110T201554Z"));
$req->setHeaderList(["Accept"=>["application/json"], "Host"=>["open.volcengineapi.com"]]);
$req->setHost("open.volcengineapi.com");
$req->setPath("/");
$req->setIsSignUrl(false);
$req->setMethod("GET");
$req->setQueryList(["Action"=>["ListUsers"], "Version"=>["2018-01-01"], "Limit"=>["10"], "Offset"=>["0"]]);
$s = Utils::streamFor("");
$req->setPyloadHash(Utils::hash($s,"sha256"));

$resp = $sign->signOnly($req, $credentials);
echo $resp;