<?php

namespace OSS\Tests;

require_once __DIR__ . '/../../../autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'StsClient.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Credentials\StaticCredentialsProvider;

/**
 * Class Common
 *
 * Sample program [Samples / *. Php] Common class, used to obtain OssClient instance and other public methods
 */
class Common
{
    /**
     * According to the Config configuration, get an OssClient instance
     *
     * @return OssClient  An OssClient instance
     */
    public static function getOssClient($conf = NULL)
    {
        try {
            $provider = new StaticCredentialsProvider(
                getenv('OSS_ACCESS_KEY_ID'), 
                getenv('OSS_ACCESS_KEY_SECRET')
            );
            $config = array(
                'region' => self::getRegion(),
                'endpoint' => self::getEndpoint(),
                'provider' => $provider,
                'signatureVersion' => self::getSignVersion()
            );

            if ($conf != null) {
                foreach ($conf as  $key => $value) {
                    $config[$key] = $value;
                }
            }
            
            $ossClient = new OssClient($config);
  
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
        }
        return $ossClient;
    }

    public static function getStsOssClient($conf = NULL)
    {
        $stsClient = new StsClient();
        $assumeRole = new AssumeRole();
        $stsClient->AccessSecret = getenv('OSS_ACCESS_KEY_SECRET');
        $assumeRole->AccessKeyId = getenv('OSS_ACCESS_KEY_ID');
        $assumeRole->RoleArn =  getenv('OSS_TEST_RAM_ROLE_ARN');
        $params = $assumeRole->getAttributes();
        $response = $stsClient->doAction($params);

        try {
            $provider = new StaticCredentialsProvider(
                $response->Credentials->AccessKeyId, 
                $response->Credentials->AccessKeySecret,
                $response->Credentials->SecurityToken
            );
            $config = array(
                'region' => self::getRegion(),
                'endpoint' => self::getEndpoint(),
                'provider' => $provider,
                'signatureVersion' => self::getSignVersion()
            );

            if ($conf != null) {
                foreach ($conf as  $key => $value) {
                    $config[$key] = $value;
                }
            }

            $ossStsClient = new OssClient($config);
  
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossStsClient;
    }

    public static function getBucketName()
    {
        $name = getenv('OSS_BUCKET');
        if (empty($name)) {
            return "skyranch-php-test";
        }
        return $name;
    }

    public static function getRegion()
    {
		return getenv('OSS_TEST_REGION'); 
    }

    public static function getEndpoint()
    {
		return getenv('OSS_TEST_ENDPOINT'); 
    }

	public static function getCallbackUrl()
    {
        return getenv('OSS_TEST_CALLBACK_URL');
    }

    public static function getPayerUid()
    {
        return getenv('OSS_TEST_PAYER_UID');
    }

    public static function getPayerAccessKeyId()
    {
        return getenv('OSS_TEST_PAYER_ACCESS_KEY_ID');
    }

    public static function getPayerAccessKeySecret()
    {
        return getenv('OSS_TEST_PAYER_ACCESS_KEY_SECRET');
    }

    public static function getSignVersion()
    {
        return OssClient::OSS_SIGNATURE_VERSION_V1;
    }

    public static function getPathStyleBucket()
    {
        return getenv('OSS_TEST_PATHSTYLE_BUCKET');
    }

    /**
     * Tool method, create a bucket
     */
    public static function createBucket()
    {
        $ossClient = self::getOssClient();
        if (is_null($ossClient)) exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    /**
     * Wait for bucket meta sync
     */
    public static function waitMetaSync()
    {
        if (getenv('TRAVIS')) {
            sleep(10);
        }
    }
}
