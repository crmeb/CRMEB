<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\Credentials\EnvironmentVariableCredentialsProvider;
$bucket = Common::getBucketName();

// Access Key Provider demo
$id = '<access_key_id>';
$secret = '<accessKey_secret>';
$provider = new StaticCredentialsProvider($id,$secret);
$config = array(
    'provider' => $provider,
    'endpoint'=>'<endpoint>'
);
try {
    $ossClient = new OssClient($config);
    $ossClient->putObject($bucket,'c.file','hi oss,this is credentials test of access key provider');
    $result = $ossClient->getObject($bucket,'c.file');
    var_dump($result);
} catch (OssException $e) {
    printf($e->getMessage() . "\n");
    return;
}

// Sts provider demo
$id = '<access_key_id>';
$secret = '<accessKey_secret>';
$token = '<security_token>';
$provider = new StaticCredentialsProvider($id,$secret,$token);
$config = array(
    'provider' => $provider,
    'endpoint'=> "<endpoint>"
);

try {
    $ossClient = new OssClient($config);
    $ossClient->putObject($bucket,'c.file','hi oss,this is credentials test of sts provider');
    $result = $ossClient->getObject($bucket,'c.file');
    var_dump($result);
} catch (OssException $e) {
    printf($e->getMessage() . "\n");
    return;
}

// read from env
$envProvider = new EnvironmentVariableCredentialsProvider();
$config = array(
    'provider' => $envProvider,
    'endpoint'=> "<endpoint>"
);

try {
    $ossClient = new OssClient($config);
    $ossClient->putObject($bucket,'c.file','hi oss,this is credentials test of sts provider');
    $result = $ossClient->getObject($bucket,'c.file');
    var_dump($result);
} catch (OssException $e) {
    printf($e->getMessage() . "\n");
    return;
}
