<?php

use Volc\Service\Cdn;

require('../../vendor/autoload.php');
require_once "init.php";

$client = Cdn::getInstance();

$config = new Config();
$client->setAccessKey($config->ak);
$client->setSecretKey($config->sk);

$body = [
    'Certificate' => [
        'Certificate' => '-----BEGIN CERTIFICATE-----\\r\\nHmU2w=\\r\\n-----END CERTIFICATE-----\\r\\n-----BEGIN CERTIFICATE-----\\r\\nMIIElg==\\r\\n-----END CERTIFICATE-----\\r\\n-----BEGIN CERTIFICATE-----\\r\\nMIIDV5Adg\\r\\n06O/nVsJ8dWd4=\\r\\n-----END CERTIFICATE-----',
        'PrivateKey' => '-----BEGIN RSA PRIVATE KEY-----\nMIIE6\nBNtw==\n-----END RSA PRIVATE KEY-----'        
    ],
    'CertInfo' => [
        'Desc' => 'MyCert'
    ],
    'Source' => 'volc_cert_center'
];

$response = $client->addCdnCertificate($body);
var_dump($response);
