<?php

//=============================================================================

//How to use credentials-php to access oss

// step 1:Install credentials-php  composer require alibabacloud/credentials
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Credentials\CredentialsProvider;
use AlibabaCloud\Credentials\Credential;
use OSS\Credentials\StaticCredentialsProvider;

// public provider conversion class
class AlibabaCloudCredentialsWrapper implements CredentialsProvider{
    /**
     * @var Credential
     */
    private $warpper;
    public function __construct($credential){
        $this->warpper = $credential;
    }
    public function getCredentials(){
        $ak = $this->warpper->getAccessKeyId();
        $sk = $this->warpper->getAccessKeySecret();
        $token = $this->warpper->getSecurityToken();
        return new StaticCredentialsProvider($ak, $sk, $token);
    }
}

$bucket = Common::getBucketName();

//AccessKey Credentials demo
$credential = new Credential(array(
    'type'              => 'access_key',
    'access_key_id'     => '<access_key_id>',
    'access_key_secret' => '<accessKey_secret>',
));
$providerWarpper = new AlibabaCloudCredentialsWrapper($credential);
$config = array(
    'provider' => $providerWarpper,
    'endpoint'=> '<endpoint>'
);
try {
    $ossClient = new OssClient($config);
    $ossClient->putObject($bucket,'c.file','hi oss,this is credentials test of access key');
    $result = $ossClient->getObject($bucket,'c.file');
    var_dump($result);
} catch (OssException $e) {
    printf($e->getMessage() . "\n");
    return;
}


// EcsRamRole Credentials demo
$ecsRamRole = new Credential(array(
    'type'      => 'ecs_ram_role',
    'role_name' => 'EcsRamRoleOssTest',
));
$providerWarpper = new AlibabaCloudCredentialsWrapper($ecsRamRole);
$bucket = 'oss-bucket-cd-yp-test';
$config = array(
    'provider' => $providerWarpper,
    'endpoint'=> '<endpoint>'
);
try {
    $ossClient = new OssClient($config);
    $ossClient->putObject($bucket,'c.file','hi oss,this is credentials test of EcsRamRole');
    $result = $ossClient->getObject($bucket,'c.file');
    var_dump($result);
} catch (OssException $e) {
    printf($e->getMessage() . "\n");
    return;
}
