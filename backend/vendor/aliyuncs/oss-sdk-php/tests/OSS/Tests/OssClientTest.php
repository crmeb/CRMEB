<?php

namespace OSS\Tests;

use OSS\Core\OssException;
use OSS\Credentials\EnvironmentVariableCredentialsProvider;
use OSS\OssClient;
use OSS\Credentials\Credentials;
use OSS\Credentials\CredentialsProvider;
use OSS\Credentials\StaticCredentialsProvider;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestOssClientBase.php';

class TestEmptyIdCredentials extends Credentials
{
    public function __construct()
    {
    }

    public function getAccessKeyId()
    {
        return '';
    }

    public function getAccessKeySecret()
    {
        return 'secret';
    }

    public function getSecurityToken()
    {
        return null;
    }
}

class TestEmptySecretCredentials extends Credentials
{
    public function __construct()
    {
    }

    public function getAccessKeyId()
    {
        return 'id';
    }

    public function getAccessKeySecret()
    {
        return '';
    }

    public function getSecurityToken()
    {
        return null;
    }
}


class TestCredentialsProvider implements CredentialsProvider
{
    private $credentials;

    public function __construct($flag)
    {
        if ($flag == 2) {
            $this->credentials = new TestEmptyIdCredentials();
        } else if ($flag == 1) {
            $this->credentials = new TestEmptySecretCredentials();
        } else {
            $this->credentials = null;
        }
    }

    /**
     * @return Credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
}

class OssClientTest extends TestOssClientBase
{
    public function testConstrunct()
    {
        try {
            $ossClient = new OssClient('id', 'key', 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse($ossClient->isUseSSL());
            $ossClient->setUseSSL(true);
            $this->assertTrue($ossClient->isUseSSL());
            $this->assertTrue(true);
            $this->assertEquals(3, $ossClient->getMaxRetries());
            $ossClient->setMaxTries(4);
            $this->assertEquals(4, $ossClient->getMaxRetries());
            $ossClient->setTimeout(10);
            $ossClient->setConnectTimeout(20);
        } catch (OssException $e) {
            assertFalse(true);
        }
    }

    public function testConstrunct2()
    {
        try {
            $ossClient = new OssClient('id', "", 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("access key secret is empty", $e->getMessage());
        }
    }

    public function testConstrunct3()
    {
        try {
            $ossClient = new OssClient("", 'key', 'http://oss-cn-hangzhou.aliyuncs.com');
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals("access key id is empty", $e->getMessage());
        }
    }

    public function testConstrunct4()
    {
        try {
            $ossClient = new OssClient('id', 'key', "");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('endpoint is empty', $e->getMessage());
        }
    }

    public function testConstrunct5()
    {
        try {
            $ossClient = new OssClient('id', 'key', "123.123.123.1");
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct6()
    {
        try {
            $ossClient = new OssClient('id', 'key', "https://123.123.123.1");
            $this->assertTrue($ossClient->isUseSSL());
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $ossClient = new OssClient('id', 'key', "https://123.123.123.1:3128");
            $this->assertTrue($ossClient->isUseSSL());
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct7()
    {
        try {
            $ossClient = new OssClient('id', 'key', "http://123.123.123.1");
            $this->assertFalse($ossClient->isUseSSL());
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }

        try {
            $ossClient = new OssClient('id', 'key', "http://123.123.123.1:3128");
            $this->assertFalse($ossClient->isUseSSL());
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct8()
    {
        try {
            $ossClient = new OssClient('id', 'key', "http://123.123.123.1", true);
            $ossClient->listBuckets();
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertFalse(false);
        }
    }

    public function testConstrunct9()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint() . '/ ';
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->listBuckets();
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testConstrunct10()
    {
        try {
            $ossClient = new OssClient('id', 'key', "http://ABC-COM.TEST.123.cn", true);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertTrue(false);
        }
    }

    public function testConstrunct11()
    {
        try {
            $ossClient = new OssClient('id', 'key', "oss-test.com\\aliyuncs.com");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('endpoint is invalid:' . "oss-test.com\\aliyuncs.com", $e->getMessage());
        }
    }

    public function testConstrunct12()
    {
        try {
            $ossClient = new OssClient('id', 'key', "192.168.1.0:abc123");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('endpoint is invalid:' . "192.168.1.0:abc123", $e->getMessage());
        }
    }

    public function testSupportPutEmptyObject()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint() . '/ ';
            $bucket = $this->bucket;
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->putObject($bucket, 'test_emptybody', '');
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        //use invalid sts-token, should fail.
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint() . '/ ';
            $bucket = $this->bucket;
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, "invalid-sts-token");
            $ossClient->putObject($bucket, 'test_emptybody', '');
            $this->assertTrue(false);
        } catch (OssException $e) {
            $this->assertEquals('InvalidAccessKeyId', $e->getErrorCode());
        }
    }

    public function testCreateObjectDir()
    {
        $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
        $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
        $endpoint = ' ' . Common::getEndpoint() . '/ ';
        $bucket = $this->bucket;
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);

        try {
            $object = 'test-dir';
            $ossClient->createObjectDir($bucket, $object);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }

        try {
            $object = '0';
            $ossClient->createObjectDir($bucket, $object);
            $ossClient->putObject($bucket, $object, '');
            $this->assertTrue(true);
        } catch (OssException $e) {
            var_dump($e);
            $this->assertFalse(true);
        }
    }

    public function testGetBucketCors()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint(). '/ ';
            $bucket = Common::getBucketName();
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->getBucketCors($bucket);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testGetBucketCname()
    {
        try {
            $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint() . '/ ';
            $bucket = $this->bucket;
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $ossClient->getBucketCname($bucket);
            $this->assertTrue(true);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testProxySupport()
    {
        $accessKeyId = ' ' . getenv('OSS_ACCESS_KEY_ID') . ' ';
        $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
        $endpoint = ' ' . Common::getEndpoint() . '/ ';
        $bucket = Common::getBucketName() . '-proxy';
        $requestProxy = getenv('OSS_PROXY');
        $key = 'test-proxy-srv-object';
        $content = 'test-content';
        $proxys = parse_url($requestProxy);

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, null, $requestProxy);

        $result = $ossClient->createBucket($bucket);
        $this->checkProxy($result, $proxys);

        $result = $ossClient->putObject($bucket, $key, $content);
        $this->checkProxy($result, $proxys);
        $result = $ossClient->getObject($bucket, $key);
        $this->assertEquals($content, $result);

        // list object
        $objectListInfo = $ossClient->listObjects($bucket);
        $objectList = $objectListInfo->getObjectList();
        $this->assertNotNull($objectList);
        $this->assertTrue(is_array($objectList));
        $objects = array();
        foreach ($objectList as $value) {
            $objects[] = $value->getKey();
        }
        $this->assertEquals(1, count($objects));
        $this->assertTrue(in_array($key, $objects));

        $result = $ossClient->deleteObject($bucket, $key);
        $this->checkProxy($result, $proxys);

        $result = $ossClient->deleteBucket($bucket);
        $this->checkProxy($result, $proxys);
    }

    private function checkProxy($result, $proxys)
    {
        $this->assertEquals($result['info']['primary_ip'], $proxys['host']);
        $this->assertEquals($result['info']['primary_port'], $proxys['port']);
        $this->assertTrue(array_key_exists('via', $result));
    }

    public function testIpEndpoint()
    {
        try {
            $accessKeyId = 'sk' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = '192.168.1.1';
            $bucket = Common::getBucketName();
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
            $object = "a.file";
            $timeout = 3600;
            $options = array('Content-Type' => 'txt');
            $signedUrl = $ossClient->signUrl($bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(strpos($signedUrl, '192.168.1.1/' . $bucket . '/a.file?') != false);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testCnameEndpoint()
    {
        try {
            $accessKeyId = 'sk' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = 'cname.endpoint';
            $bucket = Common::getBucketName();
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, true);
            $object = "a.file";
            $timeout = 3600;
            $options = array('Content-Type' => 'txt');
            $signedUrl = $ossClient->signUrl($bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(strpos($signedUrl, 'cname.endpoint/a.file?') != false);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testStsToken()
    {
        try {
            $accessKeyId = 'sk' . getenv('OSS_ACCESS_KEY_ID') . ' ';
            $accessKeySecret = ' ' . getenv('OSS_ACCESS_KEY_SECRET') . ' ';
            $endpoint = ' ' . Common::getEndpoint() . '/ ';
            $bucket = Common::getBucketName();
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, "test-token");
            $object = "a.file";
            $timeout = 3600;
            $options = array('Content-Type' => 'txt');
            $signedUrl = $ossClient->signUrl($bucket, $object, $timeout, "PUT", $options);
            $this->assertTrue(strpos($signedUrl, 'security-token=test-token') != false);
        } catch (OssException $e) {
            $this->assertFalse(true);
        }
    }

    public function testEmptyCredentials()
    {
        // empty case, should throw exception
        try {
            $id = '';
            $secret = 'accessKey_secret';
            $provider = new StaticCredentialsProvider($id, $secret);
            $config = array(
                'provider' => $provider,
                'endpoint' => 'http://oss-cn-hangzhou.aliyuncs.com'
            );
            $ossClient = new OssClient($config);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('access key id is empty', $e->getMessage());
        }

        // empty case, should throw exception
        try {
            $id = 'id';
            $secret = '';
            $provider = new StaticCredentialsProvider($id, $secret);
            $config = array(
                'provider' => $provider,
                'endpoint' => 'http://oss-cn-hangzhou.aliyuncs.com'
            );
            $ossClient = new OssClient($config);
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('access key secret is empty', $e->getMessage());
        }

        // empty case, should throw exception
        try {
            $provider = new TestCredentialsProvider(0);
            $config = array(
                'provider' => $provider,
                'endpoint' => 'http://oss-cn-hangzhou.aliyuncs.com'
            );
            $ossClient = new OssClient($config);
            $ossClient->getBucketAcl("bucket");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('credentials is empty.', $e->getMessage());
        }

        // empty case, should throw exception
        try {
            $provider = new TestCredentialsProvider(1);
            $config = array(
                'provider' => $provider,
                'endpoint' => 'http://oss-cn-hangzhou.aliyuncs.com'
            );
            $ossClient = new OssClient($config);
            $ossClient->getBucketAcl("bucket");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('access key secret is empty', $e->getMessage());
        }

        // empty case, should throw exception
        try {
            $provider = new TestCredentialsProvider(2);
            $config = array(
                'provider' => $provider,
                'endpoint' => 'http://oss-cn-hangzhou.aliyuncs.com'
            );
            $ossClient = new OssClient($config);
            $ossClient->getBucketAcl("bucket");
            $this->assertFalse(true);
        } catch (OssException $e) {
            $this->assertEquals('access key id is empty', $e->getMessage());
        }
    }

    public function testEnvironmentVariableCredentialsProvider()
    {
        try {
            $provider = new EnvironmentVariableCredentialsProvider();
            $config = array(
                'provider' => $provider,
                'endpoint' => Common::getEndpoint(),
            );
            $ossClient = new OssClient($config);
            $ossClient->putObject($this->bucket, 'test_emptybody', '');
            $this->assertTrue(true);
        } catch (OssException $e) {
            printf($e->getMessage());
            $this->assertFalse(true);
        }


        try {
            $ossClient->getObject($this->bucket, 'test_emptybody');
            $this->assertTrue(true);
        } catch (OssException $e) {
            printf($e->getMessage());
            $this->assertFalse(true);
        }
    }
}
