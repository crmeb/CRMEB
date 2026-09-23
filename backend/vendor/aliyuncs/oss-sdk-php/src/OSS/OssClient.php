<?php

namespace OSS;

use OSS\Core\MimeTypes;
use OSS\Core\OssException;
use OSS\Credentials\Credentials;
use OSS\Credentials\CredentialsProvider;
use OSS\Credentials\StaticCredentialsProvider;
use OSS\Http\RequestCore;
use OSS\Http\RequestCore_Exception;
use OSS\Http\ResponseCore;
use OSS\Model\BucketInfo;
use OSS\Model\CorsConfig;
use OSS\Model\CnameConfig;
use OSS\Model\GetLiveChannelHistory;
use OSS\Model\GetLiveChannelInfo;
use OSS\Model\GetLiveChannelStatus;
use OSS\Model\LoggingConfig;
use OSS\Model\LiveChannelConfig;
use OSS\Model\LiveChannelInfo;
use OSS\Model\LiveChannelListInfo;
use OSS\Model\ObjectListInfoV2;
use OSS\Model\StorageCapacityConfig;
use OSS\Result\AclResult;
use OSS\Result\BodyResult;
use OSS\Result\GetCorsResult;
use OSS\Result\GetLifecycleResult;
use OSS\Result\GetLocationResult;
use OSS\Result\GetLoggingResult;
use OSS\Result\GetRefererResult;
use OSS\Result\GetStorageCapacityResult;
use OSS\Result\GetWebsiteResult;
use OSS\Result\GetCnameResult;
use OSS\Result\HeaderResult;
use OSS\Result\InitiateMultipartUploadResult;
use OSS\Result\ListBucketsResult;
use OSS\Result\ListMultipartUploadResult;
use OSS\Model\ListMultipartUploadInfo;
use OSS\Result\ListObjectsResult;
use OSS\Result\ListObjectsV2Result;
use OSS\Result\ListPartsResult;
use OSS\Result\PutSetDeleteResult;
use OSS\Result\DeleteObjectsResult;
use OSS\Result\CopyObjectResult;
use OSS\Result\CallbackResult;
use OSS\Result\ExistResult;
use OSS\Result\PutLiveChannelResult;
use OSS\Result\GetLiveChannelHistoryResult;
use OSS\Result\GetLiveChannelInfoResult;
use OSS\Result\GetLiveChannelStatusResult;
use OSS\Result\ListLiveChannelResult;
use OSS\Result\AppendResult;
use OSS\Model\ObjectListInfo;
use OSS\Result\SymlinkResult;
use OSS\Result\UploadPartResult;
use OSS\Model\BucketListInfo;
use OSS\Model\LifecycleConfig;
use OSS\Model\RefererConfig;
use OSS\Model\WebsiteConfig;
use OSS\Core\OssUtil;
use OSS\Model\ListPartsInfo;
use OSS\Result\GetBucketInfoResult;
use OSS\Model\BucketStat;
use OSS\Result\GetBucketStatResult;
use OSS\Model\ServerSideEncryptionConfig;
use OSS\Result\GetBucketEncryptionResult;
use OSS\Model\RequestPaymentConfig;
use OSS\Result\GetBucketRequestPaymentResult;
use OSS\Model\Tag;
use OSS\Model\TaggingConfig;
use OSS\Result\GetBucketTagsResult;
use OSS\Model\VersioningConfig;
use OSS\Result\GetBucketVersioningResult;
use OSS\Model\InitiateWormConfig;
use OSS\Result\InitiateBucketWormResult;
use OSS\Model\ExtendWormConfig;
use OSS\Result\GetBucketWormResult;
use OSS\Model\RestoreConfig;
use OSS\Model\ObjectVersionListInfo;
use OSS\Result\ListObjectVersionsResult;
use OSS\Model\DeleteObjectInfo;
use OSS\Model\DeletedObjectInfo;
use OSS\Result\DeleteObjectVersionsResult;
use OSS\Model\TransferAccelerationConfig;
use OSS\Result\GetBucketTransferAccelerationResult;
use OSS\Model\CnameTokenInfo;
use OSS\Result\CreateBucketCnameTokenResult;
use OSS\Result\GetBucketCnameTokenResult;
use OSS\Signer\SignerInterface;
use OSS\Signer\SignerV1;
use OSS\Signer\SignerV4;


/**
 * Class OssClient
 *
 * Object Storage Service(OSS)'s client class, which wraps all OSS APIs user could call to talk to OSS.
 * Users could do operations on bucket, object, including MultipartUpload or setting ACL via an OSSClient instance.
 * For more details, please check out the OSS API document:https://www.alibabacloud.com/help/doc-detail/31947.htm
 */
class OssClient
{

    /**
     * OssClient constructor.
     */
    public function __construct()
    {
        $argNum = func_num_args();
        $args = func_get_args();
        if ($argNum == 1 && is_array($args[0])) {
            call_user_func_array(array($this, '__initNewClient'), $args);
        } else {
            call_user_func_array(array($this, '__initClient'), $args);
        }
    }

    /**
     * There're a few different ways to create an OssClient object:
     * 1. Most common one from access Id, access Key and the endpoint: $ossClient = new OssClient($id, $key, $endpoint)
     * 2. If the endpoint is the CName (such as www.testoss.com, make sure it's CName binded in the OSS console),
     *    uses $ossClient = new OssClient($id, $key, $endpoint, true)
     * 3. If using Alicloud's security token service (STS), then the AccessKeyId, AccessKeySecret and STS token are all got from STS.
     * Use this: $ossClient = new OssClient($id, $key, $endpoint, false, $token)
     * 4. If the endpoint is in IP format, you could use this: $ossClient = new OssClient($id, $key, “1.2.3.4:8900”)
     *
     * @param string $accessKeyId The AccessKeyId from OSS or STS
     * @param string $accessKeySecret The AccessKeySecret from OSS or STS
     * @param string $endpoint The domain name of the datacenter,For example: oss-cn-hangzhou.aliyuncs.com
     * @param boolean $isCName If this is the CName and binded in the bucket.
     * @param string $securityToken from STS.
     * @param string $requestProxy
     * @throws OssException
     */
    private function __initClient($accessKeyId, $accessKeySecret, $endpoint, $isCName = false, $securityToken = NULL, $requestProxy = NULL)
    {
        $accessKeyId = trim($accessKeyId);
        $accessKeySecret = trim($accessKeySecret);
        $endpoint = trim(trim($endpoint), "/");

        if (empty($accessKeyId)) {
            throw new OssException("access key id is empty");
        }
        if (empty($accessKeySecret)) {
            throw new OssException("access key secret is empty");
        }
        $provider = new StaticCredentialsProvider($accessKeyId, $accessKeySecret, $securityToken);
        $config = array(
            'endpoint' => $endpoint,
            'cname' => $isCName,
            'request_proxy' => $requestProxy,
            'provider' => $provider
        );
        $this->__initNewClient($config);
    }

    /**
     * @param array $config
     * @throws OssException
     */
    private function __initNewClient($config = array())
    {
        $isCName = isset($config['cname']) ? $config['cname'] : false;
        $endpoint = isset($config['endpoint']) ? $config['endpoint'] : '';
        $requestProxy = isset($config['request_proxy']) ? $config['request_proxy'] : null;
        $provider = isset($config['provider']) ? $config['provider'] : '';
        if (empty($endpoint)) {
            throw new OssException("endpoint is empty");
        }
        $this->hostname = $this->checkEndpoint($endpoint, $isCName);
        if (isset($config['forcePathStyle'])) {
            if ($config['forcePathStyle'] === true) {
                $this->hostType = self::OSS_HOST_TYPE_PATH_STYLE;
            }
        }
        $this->requestProxy = $requestProxy;
        if (!$provider instanceof CredentialsProvider) {
            throw new OssException("provider must be an instance of CredentialsProvider");
        }
        $this->provider = $provider;

        $this->region = isset($config['region']) ? $config['region'] : '';
        $this->cloudBoxId = isset($config['cloudBoxId']) ? $config['cloudBoxId'] : '';

        // $enableStrictObjName
        $this->enableStrictObjName = true;
        if (isset($config['strictObjectName'])) {
            if ($config['strictObjectName'] === false) {
                $this->enableStrictObjName = false;
            }
        }

        // sign version
        $signatureVersion = self::OSS_SIGNATURE_VERSION_V1;
        if (isset($config['signatureVersion']) && $config['signatureVersion'] === self::OSS_SIGNATURE_VERSION_V4) {
            $signatureVersion = self::OSS_SIGNATURE_VERSION_V4;
        }
        if ($signatureVersion === self::OSS_SIGNATURE_VERSION_V4) {
            $this->enableStrictObjName = false;
            $this->signer = new SignerV4();
        } else {
            $this->signer = new SignerV1();
        }

        //checkObjectEncoding
        $this->checkObjectEncoding = false;
        if (isset($config['checkObjectEncoding'])) {
            if ($config['checkObjectEncoding'] === true) {
                $this->checkObjectEncoding = true;
            }
        }

        //filePathCompatible
        $this->filePathCompatible = false;
        if (version_compare(phpversion(), '7.0.0', '<')) {
            if (OssUtil::isWin()) {
                $this->filePathCompatible = true;
            }
        }
        if (isset($config['filePathCompatible'])) {
            if ($config['filePathCompatible'] === true) {
                $this->filePathCompatible = true;
            } else if ($config['filePathCompatible'] === false) {
                $this->filePathCompatible = false;
            }
        }

        self::checkEnv();
    }

    /**
     * Lists the Bucket [GetService]. Not applicable if the endpoint is CName (because CName must be binded to a specific bucket).
     *
     * @param array $options
     * @return BucketListInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listBuckets($options = NULL)
    {
        if ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
            throw new OssException("operation is not permitted with CName host");
        }
        $this->precheckOptions($options);
        $options[self::OSS_BUCKET] = '';
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $response = $this->auth($options);
        $result = new ListBucketsResult($response);
        return $result->getData();
    }

    /**
     * Creates bucket,The ACL of the bucket created by default is OssClient::OSS_ACL_TYPE_PRIVATE
     *
     * @param string $bucket
     * @param string $acl
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function createBucket($bucket, $acl = self::OSS_ACL_TYPE_PRIVATE, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_HEADERS][self::OSS_ACL] = $acl;
        if (isset($options[self::OSS_STORAGE])) {
            $this->precheckStorage($options[self::OSS_STORAGE]);
            $options[self::OSS_CONTENT] = OssUtil::createBucketXmlBody($options[self::OSS_STORAGE]);
            unset($options[self::OSS_STORAGE]);
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Deletes bucket
     * The deletion will not succeed if the bucket is not empty (either has objects or parts)
     * To delete a bucket, all its objects and parts must be deleted first.
     *
     * @param string $bucket
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucket($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Checks if a bucket exists
     *
     * @param string $bucket
     * @return bool|null
     * @throws OssException|RequestCore_Exception
     */
    public function doesBucketExist($bucket)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new ExistResult($response);
        return $result->getData();
    }

    /**
     * Get the data center location information for the bucket
     *
     * @param string $bucket
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketLocation($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'location';
        $response = $this->auth($options);
        $result = new GetLocationResult($response);
        return $result->getData();
    }

    /**
     * Get the Meta information for the Bucket
     *
     * @param string $bucket
     * @param array $options Refer to the SDK documentation
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketMeta($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Gets the bucket ACL
     *
     * @param string $bucket
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketAcl($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new AclResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket ACL
     *
     * @param string $bucket bucket name
     * @param string $acl access permissions, valid values are ['private', 'public-read', 'public-read-write']
     * @param array $options by default is empty
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketAcl($bucket, $acl, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_HEADERS][self::OSS_ACL] = $acl;
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets object ACL
     *
     * @param string $bucket
     * @param string $object
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getObjectAcl($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new AclResult($response);
        return $result->getData();
    }

    /**
     * Sets the object ACL
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $acl access permissions, valid values are ['default', 'private', 'public-read', 'public-read-write']
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putObjectAcl($bucket, $object, $acl, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_HEADERS][self::OSS_OBJECT_ACL] = $acl;
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the bucket logging config
     *
     * @param string $bucket bucket name
     * @param array $options by default is empty
     * @return LoggingConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketLogging($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new GetLoggingResult($response);
        return $result->getData();
    }

    /**
     * Sets the bycket logging config. Only owner can call this API.
     *
     * @param string $bucket bucket name
     * @param string $targetBucket The logging file's bucket
     * @param string $targetPrefix The logging file's prefix
     * @param array $options By default is empty.
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketLogging($bucket, $targetBucket, $targetPrefix, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $this->precheckBucket($targetBucket, 'targetbucket is not allowed empty');
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';

        $loggingConfig = new LoggingConfig($targetBucket, $targetPrefix);
        $options[self::OSS_CONTENT] = $loggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket logging config
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketLogging($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the website config in bucket---that is could make the bucket as a static website once the CName is binded.
     *
     * @param string $bucket bucket name
     * @param WebsiteConfig $websiteConfig
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketWebsite($bucket, $websiteConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $websiteConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the website config in the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return WebsiteConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketWebsite($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new GetWebsiteResult($response);
        return $result->getData();
    }

    /**
     * Deletes the website config in the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketWebsite($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the cross-origin-resource-sharing (CORS) rule. It would overwrite the originl one.
     *
     * @param string $bucket bucket name
     * @param CorsConfig $corsConfig CORS config. Check out the details from OSS API document
     * @param array $options array
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketCors($bucket, $corsConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $corsConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the bucket CORS config
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return CorsConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketCors($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new GetCorsResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's CORS config and disable the CORS on the bucket.
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketCors($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Bind a CName for the bucket
     *
     * @param string $bucket bucket name
     * @param string $cname
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function addBucketCname($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[self::OSS_COMP] = 'add';
        $options[self::OSS_CNAME] = '';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the binded CName list of the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return CnameConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketCname($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_CNAME] = '';
        $response = $this->auth($options);
        $result = new GetCnameResult($response);
        return $result->getData();
    }

    /**
     * Remove a CName binding from the bucket
     *
     * @param string $bucket bucket name
     * @param CnameConfig $cname
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketCname($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[self::OSS_COMP] = 'delete';
        $options[self::OSS_CNAME] = '';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * create a cname token for a bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return CnameTokenInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function createBucketCnameToken($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[self::OSS_COMP] = 'token';
        $options[self::OSS_CNAME] = '';
        $response = $this->auth($options);
        $result = new CreateBucketCnameTokenResult($response);
        return $result->getData();
    }

    /**
     * get a cname token for a bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return CnameTokenInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketCnameToken($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_COMP] = 'token';
        $options[self::OSS_CNAME] = $cname;
        $response = $this->auth($options);
        $result = new GetBucketCnameTokenResult($response);
        return $result->getData();
    }

    /**
     * Creates a Live Channel under a bucket
     *
     * @param string $bucket bucket name
     * @param string channelName  $channelName
     * @param LiveChannelConfig $channelConfig
     * @param array $options
     * @return LiveChannelInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketLiveChannel($bucket, $channelName, $channelConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $channelConfig->serializeToXml();

        $response = $this->auth($options);
        $result = new PutLiveChannelResult($response);
        $info = $result->getData();
        $info->setName($channelName);
        $info->setDescription($channelConfig->getDescription());

        return $info;
    }

    /**
     * Sets the LiveChannel status
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param string channelStatus $channelStatus enabled or disabled
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putLiveChannelStatus($bucket, $channelName, $channelStatus, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';
        $options[self::OSS_LIVE_CHANNEL_STATUS] = $channelStatus;

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the LiveChannel information by the channel name
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param array $options
     * @return GetLiveChannelInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function getLiveChannelInfo($bucket, $channelName, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';

        $response = $this->auth($options);
        $result = new GetLiveChannelInfoResult($response);
        return $result->getData();
    }

    /**
     * Gets the status of LiveChannel
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param array $options
     * @return GetLiveChannelStatus|null
     * @throws OssException|RequestCore_Exception
     */
    public function getLiveChannelStatus($bucket, $channelName, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';
        $options[self::OSS_COMP] = 'stat';

        $response = $this->auth($options);
        $result = new GetLiveChannelStatusResult($response);
        return $result->getData();
    }

    /**
     * Gets the LiveChannel pushing streaming record
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param array $options
     * @return GetLiveChannelHistory|null
     * @throws OssException|RequestCore_Exception
     */
    public function getLiveChannelHistory($bucket, $channelName, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';
        $options[self::OSS_COMP] = 'history';

        $response = $this->auth($options);
        $result = new GetLiveChannelHistoryResult($response);
        return $result->getData();
    }

    /**
     *Gets the live channel list under a bucket.
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return LiveChannelListInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listBucketLiveChannels($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'live';
        $options[self::OSS_QUERY_STRING] = array(
            'prefix' => isset($options['prefix']) ? $options['prefix'] : '',
            'marker' => isset($options['marker']) ? $options['marker'] : '',
            'max-keys' => isset($options['max-keys']) ? $options['max-keys'] : '',
        );
        $response = $this->auth($options);
        $result = new ListLiveChannelResult($response);
        $list = $result->getData();
        $list->setBucketName($bucket);

        return $list;
    }

    /**
     * Creates a play list file for the LiveChannel
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param string $playlistName The playlist name, must end with ".m3u8".
     * @param array $setTime startTime and EndTime in unix time. No more than 1 day.
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function postVodPlaylist($bucket, $channelName, $playlistName, $setTime)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $channelName . '/' . $playlistName;
        $options[self::OSS_SUB_RESOURCE] = 'vod';
        $options[self::OSS_LIVE_CHANNEL_END_TIME] = $setTime['EndTime'];
        $options[self::OSS_LIVE_CHANNEL_START_TIME] = $setTime['StartTime'];

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Deletes the Bucket LiveChannel
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketLiveChannel($bucket, $channelName, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = $channelName;
        $options[self::OSS_SUB_RESOURCE] = 'live';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Generates the signed pushing streaming url
     *
     * @param string $bucket bucket name
     * @param string channelName $channelName
     * @param int timeout timeout value in seconds
     * @param array $options
     * @return string The signed pushing streaming url
     * @throws OssException
     */
    public function signRtmpUrl($bucket, $channelName, $timeout = 60, $options = NULL)
    {
        $this->precheckCommon($bucket, $channelName, $options, false);
        $expires = time() + $timeout;
        $proto = 'rtmp://';
        $hostname = $this->generateHostname($bucket);
        $cano_params = '';
        $query_items = array();
        $params = isset($options['params']) ? $options['params'] : array();
        uksort($params, 'strnatcasecmp');
        foreach ($params as $key => $value) {
            $cano_params = $cano_params . $key . ':' . $value . "\n";
            $query_items[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $resource = '/' . $bucket . '/' . $channelName;

        $string_to_sign = $expires . "\n" . $cano_params . $resource;
        $cred = $this->provider->getCredentials();
        $this->checkCredentials($cred);

        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $cred->getAccessKeySecret(), true));

        $query_items[] = 'OSSAccessKeyId=' . rawurlencode($cred->getAccessKeyId());
        $query_items[] = 'Expires=' . rawurlencode($expires);
        $query_items[] = 'Signature=' . rawurlencode($signature);

        return $proto . $hostname . '/live/' . $channelName . '?' . implode('&', $query_items);
    }

    /**
     * Generates the signed pushing streaming url
     *
     * @param string $bucket bucket name
     * @param string $channelName channel name
     * @param int $expiration expiration time of the Url, unix epoch, since 1970.1.1 00.00.00 UTC
     * @param array $options
     * @return string The signed pushing streaming url
     * @throws OssException
     */
    public function generatePresignedRtmpUrl($bucket, $channelName, $expiration, $options = NULL)
    {
        $this->precheckCommon($bucket, $channelName, $options, false);
        $proto = 'rtmp://';
        $hostname = $this->generateHostname($bucket);
        $cano_params = '';
        $query_items = array();
        $params = isset($options['params']) ? $options['params'] : array();
        uksort($params, 'strnatcasecmp');
        foreach ($params as $key => $value) {
            $cano_params = $cano_params . $key . ':' . $value . "\n";
            $query_items[] = rawurlencode($key) . '=' . rawurlencode($value);
        }
        $resource = '/' . $bucket . '/' . $channelName;

        $string_to_sign = $expiration . "\n" . $cano_params . $resource;
        $cred = $this->provider->getCredentials();
        $this->checkCredentials($cred);

        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $cred->getAccessKeySecret(), true));

        $query_items[] = 'OSSAccessKeyId=' . rawurlencode($cred->getAccessKeyId());
        $query_items[] = 'Expires=' . rawurlencode($expiration);
        $query_items[] = 'Signature=' . rawurlencode($signature);

        return $proto . $hostname . '/live/' . $channelName . '?' . implode('&', $query_items);
    }

    /**
     * Precheck the CORS request. Before sending a CORS request, a preflight request (OPTIONS) is sent with the specific origin.
     * HTTP METHOD and headers information are sent to OSS as well for evaluating if the CORS request is allowed.
     *
     * Note: OSS could enable the CORS on the bucket by calling putBucketCors. Once CORS is enabled, the OSS could evaluate accordingto the preflight request.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $origin the origin of the request
     * @param string $request_method The actual HTTP method which will be used in CORS request
     * @param string $request_headers The actual HTTP headers which will be used in CORS request
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function optionsObject($bucket, $object, $origin, $request_method, $request_headers, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_OPTIONS;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_HEADERS][self::OSS_OPTIONS_ORIGIN] = $origin;
        $options[self::OSS_HEADERS][self::OSS_OPTIONS_REQUEST_HEADERS] = $request_headers;
        $options[self::OSS_HEADERS][self::OSS_OPTIONS_REQUEST_METHOD] = $request_method;
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's lifecycle config
     *
     * @param string $bucket bucket name
     * @param LifecycleConfig $lifecycleConfig LifecycleConfig instance
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketLifecycle($bucket, $lifecycleConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $lifecycleConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's lifecycle config
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return LifecycleConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketLifecycle($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new GetLifecycleResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's lifecycle config
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketLifecycle($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets a bucket's referer, which has a whitelist of referrer and specifies if empty referer is allowed.
     * Checks out API document for more details about "Bucket Referer"
     *
     * @param string $bucket bucket name
     * @param RefererConfig $refererConfig
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketReferer($bucket, $refererConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'referer';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $refererConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the bucket's Referer
     * Checks out API document for more details about "Bucket Referer"
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return RefererConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketReferer($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'referer';
        $response = $this->auth($options);
        $result = new GetRefererResult($response);
        return $result->getData();
    }

    /**
     * Set the size of the bucket,the unit is GB
     * When the capacity of the bucket is bigger than the set, it's forbidden to continue writing
     *
     * @param string $bucket bucket name
     * @param int $storageCapacity
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketStorageCapacity($bucket, $storageCapacity, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'qos';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $storageCapacityConfig = new StorageCapacityConfig($storageCapacity);
        $options[self::OSS_CONTENT] = $storageCapacityConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get the capacity of the bucket, the unit is GB
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return int|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketStorageCapacity($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'qos';
        $response = $this->auth($options);
        $result = new GetStorageCapacityResult($response);
        return $result->getData();
    }

    /**
     * Get the information of the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return BucketInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketInfo($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'bucketInfo';
        $response = $this->auth($options);
        $result = new GetBucketInfoResult($response);
        return $result->getData();
    }

    /**
     * Get the stat of the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return BucketStat|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketStat($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'stat';
        $response = $this->auth($options);
        $result = new GetBucketStatResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's policy
     *
     * @param string $bucket bucket name
     * @param string $policy policy json format content
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketPolicy($bucket, $policy, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'policy';
        $options[self::OSS_CONTENT_TYPE] = 'application/json';
        $options[self::OSS_CONTENT] = $policy;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's policy
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return string|null policy json content
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketPolicy($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'policy';
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's policy
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketPolicy($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'policy';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's encryption
     *
     * @param string $bucket bucket name
     * @param ServerSideEncryptionConfig $sseConfig
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketEncryption($bucket, $sseConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'encryption';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $sseConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's encryption
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return ServerSideEncryptionConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketEncryption($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'encryption';
        $response = $this->auth($options);
        $result = new GetBucketEncryptionResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's encryption
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketEncryption($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'encryption';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Set the request playment of the bucket, Can be BucketOwner and Requester
     *
     * @param string $bucket bucket name
     * @param string $payer
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketRequestPayment($bucket, $payer, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'requestPayment';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new RequestPaymentConfig($payer);
        $options[self::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get the request playment of the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketRequestPayment($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'requestPayment';
        $response = $this->auth($options);
        $result = new GetBucketRequestPaymentResult($response);
        return $result->getData();
    }

    /**
     * Sets the bucket's tags
     *
     * @param string $bucket bucket name
     * @param TaggingConfig $taggingConfig
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketTags($bucket, $taggingConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $taggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets bucket's tags
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return TaggingConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketTags($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new GetBucketTagsResult($response);
        return $result->getData();
    }

    /**
     * Deletes the bucket's tags
     * If want to delete specified tags for a bucket, please set the $tags
     *
     * @param string $bucket bucket name
     * @param tag[] $tags (optional)
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteBucketTags($bucket, $tags = NULL, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        if (empty($tags)) {
            $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        } else {
            $value = '';
            foreach ($tags as $tag) {
                $value .= $tag->getKey() . ',';
            }
            $value = rtrim($value, ',');
            $options[self::OSS_TAGGING] = $value;
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Set the versioning of the bucket, Can be BucketOwner and Requester
     *
     * @param string $bucket bucket name
     * @param string $status
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putBucketVersioning($bucket, $status, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'versioning';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new VersioningConfig($status);
        $options[self::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get the versioning of the bucket
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketVersioning($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'versioning';
        $response = $this->auth($options);
        $result = new GetBucketVersioningResult($response);
        return $result->getData();
    }

    /**
     * Initialize a bucket's worm
     *
     * @param string $bucket bucket name
     * @param int $day
     * @param array $options
     * @return string|null returns upload id
     * @throws OssException|RequestCore_Exception
     */
    public function initiateBucketWorm($bucket, $day, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_SUB_RESOURCE] = 'worm';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new InitiateWormConfig($day);
        $options[self::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new InitiateBucketWormResult($response);
        return $result->getData();
    }

    /**
     * Aborts the bucket's worm
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function abortBucketWorm($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_SUB_RESOURCE] = 'worm';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Complete a bucket's worm
     *
     * @param string $bucket bucket name
     * @param string $wormId
     * @param array $options
     * @return string|null returns upload id
     * @throws OssException|RequestCore_Exception
     */
    public function completeBucketWorm($bucket, $wormId, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_WORM_ID] = $wormId;
        $options[self::OSS_CONTENT] = '';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Extend a bucket's worm
     *
     * @param string $bucket bucket name
     * @param string $wormId
     * @param int $day
     * @param array $options
     * @return string|null returns upload id
     * @throws OssException|RequestCore_Exception
     */
    public function extendBucketWorm($bucket, $wormId, $day, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_WORM_ID] = $wormId;
        $options[self::OSS_SUB_RESOURCE] = 'wormExtend';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new ExtendWormConfig($day);
        $options[self::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Get a bucket's worm
     *
     * @param string $bucket bucket name
     * @param array $options
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketWorm($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'worm';
        $response = $this->auth($options);
        $result = new GetBucketWormResult($response);
        return $result->getData();
    }


    /**
     * Put Bucket TransferAcceleration
     * @param $bucket
     * @param $enabled boolean
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */

    public function putBucketTransferAcceleration($bucket, $enabled, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_SUB_RESOURCE] = 'transferAcceleration';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $config = new TransferAccelerationConfig();
        $config->setEnabled($enabled);
        $options[self::OSS_CONTENT] = $config->serializeToXml();
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Put Bucket TransferAcceleration
     * @param $bucket
     * @param array $options
     * @return boolean|null
     * @throws OssException|RequestCore_Exception
     */
    public function getBucketTransferAcceleration($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'transferAcceleration';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $response = $this->auth($options);
        $result = new GetBucketTransferAccelerationResult($response);
        return $result->getData();
    }

    /**
     * Lists the bucket's object list (in ObjectListInfo)
     *
     * @param string $bucket
     * @param array $options are defined below:
     * $options = array(
     *      'max-keys'  => specifies max object count to return. By default is 100 and max value could be 1000.
     *      'prefix'    => specifies the key prefix the returned objects must have. Note that the returned keys still contain the prefix.
     *      'delimiter' => The delimiter of object name for grouping object. When it's specified, listObjects will differeniate the object and folder. And it will return subfolder's objects.
     *      'marker'    => The key of returned object must be greater than the 'marker'.
     *)
     * Prefix and marker are for filtering and paging. Their length must be less than 256 bytes
     * @return ObjectListInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listObjects($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $query = isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array();
        $options[self::OSS_QUERY_STRING] = array_merge(
            $query,
            array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL,
                self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER]) ? $options[self::OSS_DELIMITER] : '/',
                self::OSS_PREFIX => isset($options[self::OSS_PREFIX]) ? $options[self::OSS_PREFIX] : '',
                self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS]) ? $options[self::OSS_MAX_KEYS] : self::OSS_MAX_KEYS_VALUE,
                self::OSS_MARKER => isset($options[self::OSS_MARKER]) ? $options[self::OSS_MARKER] : '')
        );

        $response = $this->auth($options);
        $result = new ListObjectsResult($response);
        return $result->getData();
    }


    /**
     * Lists the bucket's object list v2 (in ObjectListInfoV2)
     *
     * @param string $bucket
     * @param array $options are defined below:
     * $options = array(
     *      'max-keys'    => specifies max object count to return. By default is 100 and max value could be 1000.
     *      'prefix'      => specifies the key prefix the returned objects must have. Note that the returned keys still contain the prefix.
     *      'delimiter'   => The delimiter of object name for grouping object. When it's specified, listObjects will differeniate the object and folder. And it will return subfolder's objects.
     *      'start-after' => The key of returned object must be greater than the 'start-after'.
     *      'continuation-token' => The token from which the list operation must start.
     *)
     * Prefix, start-after and continuation-token are for filtering and paging. Their length must be less than 256 bytes
     * @return ObjectListInfoV2|null
     * @throws OssException|RequestCore_Exception
     */
    public function listObjectsV2($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $query = isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array();
        $temp = array(
            self::OSS_LIST_TYPE => 2,
            self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL,
            self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER]) ? $options[self::OSS_DELIMITER] : '/',
            self::OSS_PREFIX => isset($options[self::OSS_PREFIX]) ? $options[self::OSS_PREFIX] : '',
            self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS]) ? $options[self::OSS_MAX_KEYS] : self::OSS_MAX_KEYS_VALUE,
            self::OSS_START_AFTER => isset($options[self::OSS_START_AFTER]) ? $options[self::OSS_START_AFTER] : '',
        );
        if (isset($options[self::OSS_CONTINUATION_TOKEN])) {
            $temp[self::OSS_CONTINUATION_TOKEN] = $options[self::OSS_CONTINUATION_TOKEN];
        }
        $options[self::OSS_QUERY_STRING] = array_merge(
            $query, $temp
        );
        $response = $this->auth($options);
        $result = new ListObjectsV2Result($response);
        return $result->getData();
    }

    /**
     * Lists the bucket's object with version information (in ObjectListInfo)
     *
     * @param string $bucket
     * @param array $options are defined below:
     * $options = array(
     *      'max-keys'   => specifies max object count to return. By default is 100 and max value could be 1000.
     *      'prefix'     => specifies the key prefix the returned objects must have. Note that the returned keys still contain the prefix.
     *      'delimiter'  => The delimiter of object name for grouping object. When it's specified, listObjectVersions will differeniate the object and folder. And it will return subfolder's objects.
     *      'key-marker' => The key of returned object must be greater than the 'key-marker'.
     *      'version-id-marker' => The version id of returned object must be greater than the 'version-id-marker'.
     *)
     * Prefix and marker are for filtering and paging. Their length must be less than 256 bytes
     * @return ObjectVersionListInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listObjectVersions($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_SUB_RESOURCE] = 'versions';
        $query = isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array();
        $options[self::OSS_QUERY_STRING] = array_merge(
            $query,
            array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL,
                self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER]) ? $options[self::OSS_DELIMITER] : '/',
                self::OSS_PREFIX => isset($options[self::OSS_PREFIX]) ? $options[self::OSS_PREFIX] : '',
                self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS]) ? $options[self::OSS_MAX_KEYS] : self::OSS_MAX_KEYS_VALUE,
                self::OSS_KEY_MARKER => isset($options[self::OSS_KEY_MARKER]) ? $options[self::OSS_KEY_MARKER] : '',
                self::OSS_VERSION_ID_MARKER => isset($options[self::OSS_VERSION_ID_MARKER]) ? $options[self::OSS_VERSION_ID_MARKER] : '')
        );

        $response = $this->auth($options);
        $result = new ListObjectVersionsResult($response);
        return $result->getData();
    }

    /**
     * Creates a virtual 'folder' in OSS. The name should not end with '/' because the method will append the name with a '/' anyway.
     *
     * Internal use only.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function createObjectDir($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object . '/';
        $options[self::OSS_CONTENT_LENGTH] = array(self::OSS_CONTENT_LENGTH => 0);
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Uploads the $content object to OSS.
     *
     * @param string $bucket bucket name
     * @param string $object objcet name
     * @param string $content The content object
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putObject($bucket, $object, $content, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);

        $options[self::OSS_CONTENT] = $content;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object;

        if (!isset($options[self::OSS_LENGTH])) {
            $options[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
        } else {
            $options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
        }

        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5($content, true));
            $options[self::OSS_CONTENT_MD5] = $content_md5;
        }

        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $response = $this->auth($options);

        if (isset($options[self::OSS_CALLBACK]) && !empty($options[self::OSS_CALLBACK])) {
            $result = new CallbackResult($response);
        } else {
            $result = new PutSetDeleteResult($response);
        }

        return $result->getData();
    }


    /**
     * creates symlink
     * @param string $bucket bucket name
     * @param string $symlink symlink name
     * @param string $targetObject targetObject name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putSymlink($bucket, $symlink, $targetObject, $options = NULL)
    {
        $this->precheckCommon($bucket, $symlink, $options);

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $symlink;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_SYMLINK;
        $options[self::OSS_HEADERS][self::OSS_SYMLINK_TARGET] = rawurlencode($targetObject);

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets symlink
     * @param string $bucket bucket name
     * @param string $symlink symlink name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function getSymlink($bucket, $symlink, $options = NULL)
    {
        $this->precheckCommon($bucket, $symlink, $options);

        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $symlink;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_SYMLINK;

        $response = $this->auth($options);
        $result = new SymlinkResult($response);
        return $result->getData();
    }

    /**
     * Uploads a local file
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $file local file path
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function uploadFile($bucket, $object, $file, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = $this->encodeFilePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[self::OSS_FILE_UPLOAD] = $file;
        $file_size = sprintf('%u', filesize($options[self::OSS_FILE_UPLOAD]));
        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5_file($options[self::OSS_FILE_UPLOAD], true));
            $options[self::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $file);
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_CONTENT_LENGTH] = $file_size;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Uploads object from file handle
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param resource $handle file handle
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function uploadStream($bucket, $object, $handle, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        if (!is_resource($handle)) {
            throw new OssException("The handle must be an opened stream");
        }
        $options[self::OSS_FILE_UPLOAD] = $handle;
        if ($this->isCheckMD5($options)) {
            rewind($handle);
            $ctx = hash_init('md5');
            hash_update_stream($ctx, $handle);
            $content_md5 = base64_encode(hash_final($ctx, true));
            rewind($handle);
            $options[self::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        if (!isset($options[self::OSS_CONTENT_LENGTH])) {
            $stat = fstat($handle);
            $options[self::OSS_CONTENT_LENGTH] = $stat[self::OSS_SIZE];
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Append the object with the content at the specified position.
     * The specified position is typically the lengh of the current file.
     * @param string $bucket bucket name
     * @param string $object objcet name
     * @param string $content content to append
     * @param array $options
     * @return int|null next append position
     * @throws OssException|RequestCore_Exception
     */
    public function appendObject($bucket, $object, $content, $position, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);

        $options[self::OSS_CONTENT] = $content;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'append';
        $options[self::OSS_POSITION] = strval($position);
        if (!isset($options[self::OSS_LENGTH])) {
            $options[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
        } else {
            $options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
        }

        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5($content, true));
            $options[self::OSS_CONTENT_MD5] = $content_md5;
        }

        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        $response = $this->auth($options);
        $result = new AppendResult($response);
        return $result->getData();
    }

    /**
     * Append the object with a local file
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $file The local file path to append with
     * @param array $options
     * @return int|null next append position
     * @throws OssException|RequestCore_Exception
     */
    public function appendFile($bucket, $object, $file, $position, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);

        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = $this->encodeFilePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[self::OSS_FILE_UPLOAD] = $file;
        $file_size = sprintf('%u', filesize($options[self::OSS_FILE_UPLOAD]));
        $is_check_md5 = $this->isCheckMD5($options);
        if ($is_check_md5) {
            $content_md5 = base64_encode(md5_file($options[self::OSS_FILE_UPLOAD], true));
            $options[self::OSS_CONTENT_MD5] = $content_md5;
        }
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $file);
        }

        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_CONTENT_LENGTH] = $file_size;
        $options[self::OSS_SUB_RESOURCE] = 'append';
        $options[self::OSS_POSITION] = strval($position);

        $response = $this->auth($options);
        $result = new AppendResult($response);
        return $result->getData();
    }

    /**
     * Copy from an existing OSS object to another OSS object. If the target object exists already, it will be overwritten.
     *
     * @param string $fromBucket Source bucket name
     * @param string $fromObject Source object name
     * @param string $toBucket Target bucket name
     * @param string $toObject Target object name
     * @param array $options
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options = NULL)
    {
        $this->precheckCommon($fromBucket, $fromObject, $options);
        $this->precheckCommon($toBucket, $toObject, $options);
        $options[self::OSS_BUCKET] = $toBucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $toObject;
        $param = '/' . $fromBucket . '/' . rawurlencode($fromObject);
        if (isset($options[self::OSS_VERSION_ID])) {
            $param = $param . '?versionId=' . $options[self::OSS_VERSION_ID];
            unset($options[self::OSS_VERSION_ID]);
        }
        $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = $param;
        $response = $this->auth($options);
        $result = new CopyObjectResult($response);
        return $result->getData();
    }

    /**
     * Gets Object metadata
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $options Checks out the SDK document for the detail
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function getObjectMeta($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
        $options[self::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Gets the simplified metadata of a object.
     * Simplified metadata includes ETag, Size, LastModified.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $options Checks out the SDK document for the detail
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function getSimplifiedObjectMeta($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'objectMeta';
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * Deletes a object
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteObject($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Deletes multiple objects in a bucket
     *
     * @param string $bucket bucket name
     * @param array $objects object list
     * @param array $options
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteObjects($bucket, $objects, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        if (!is_array($objects) || !$objects) {
            throw new OssException('objects must be array');
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_SUB_RESOURCE] = 'delete';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $quiet = 'false';
        if (isset($options['quiet'])) {
            if (is_bool($options['quiet'])) { //Boolean
                $quiet = $options['quiet'] ? 'true' : 'false';
            } elseif (is_string($options['quiet'])) { // string
                $quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
            }
        }
        $xmlBody = OssUtil::createDeleteObjectsXmlBody($objects, $quiet);
        $options[self::OSS_CONTENT] = $xmlBody;
        $response = $this->auth($options);
        $result = new DeleteObjectsResult($response);
        return $result->getData();
    }

    /**
     * Deletes multiple objects with version id in a bucket
     *
     * @param string $bucket bucket name
     * @param array $objects DeleteObjectInfo list
     * @param array $options
     * @return DeletedObjectInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteObjectVersions($bucket, $objects, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        if (!is_array($objects) || !$objects) {
            throw new OssException('objects must be array');
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_SUB_RESOURCE] = 'delete';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $quiet = 'false';
        if (isset($options['quiet'])) {
            if (is_bool($options['quiet'])) { //Boolean
                $quiet = $options['quiet'] ? 'true' : 'false';
            } elseif (is_string($options['quiet'])) { // string
                $quiet = ($options['quiet'] === 'true') ? 'true' : 'false';
            }
        }
        $xmlBody = OssUtil::createDeleteObjectVersionsXmlBody($objects, $quiet);
        $options[self::OSS_CONTENT] = $xmlBody;
        $response = $this->auth($options);
        $result = new DeleteObjectVersionsResult($response);
        return $result->getData();
    }

    /**
     * Gets Object content
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param array $options It must contain ALIOSS::OSS_FILE_DOWNLOAD. And ALIOSS::OSS_RANGE is optional and empty means to download the whole file.
     * @return string|null
     * @throws OssException|RequestCore_Exception
     */
    public function getObject($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = $object;
        if (isset($options[self::OSS_LAST_MODIFIED])) {
            $options[self::OSS_HEADERS][self::OSS_IF_MODIFIED_SINCE] = $options[self::OSS_LAST_MODIFIED];
            unset($options[self::OSS_LAST_MODIFIED]);
        }
        if (isset($options[self::OSS_ETAG])) {
            $options[self::OSS_HEADERS][self::OSS_IF_NONE_MATCH] = $options[self::OSS_ETAG];
            unset($options[self::OSS_ETAG]);
        }
        if (isset($options[self::OSS_RANGE])) {
            $range = $options[self::OSS_RANGE];
            $options[self::OSS_HEADERS][self::OSS_RANGE] = "bytes=$range";
            unset($options[self::OSS_RANGE]);
        }
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }

    /**
     * Checks if the object exists
     * It's implemented by getObjectMeta().
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param array $options
     * @return bool|null True:object exists; False:object does not exist
     * @throws OssException|RequestCore_Exception|
     */
    public function doesObjectExist($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
        $options[self::OSS_OBJECT] = $object;
        $response = $this->auth($options);
        $result = new ExistResult($response);
        return $result->getData();
    }

    /**
     * Object reading for Archive type
     * Use Restore to enable the server to perform the thawing task
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function restoreObject($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_RESTORE;
        if (isset($options[self::OSS_RESTORE_CONFIG])) {
            $config = $options[self::OSS_RESTORE_CONFIG];
            $options[self::OSS_CONTENT_TYPE] = 'application/xml';
            $options[self::OSS_CONTENT] = $config->serializeToXml();
        }
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Sets the object tagging
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param TaggingConfig $taggingConfig
     * @return array|null
     * @throws OssException|RequestCore_Exception
     */
    public function putObjectTagging($bucket, $object, $taggingConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $taggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Gets the object tagging
     *
     * @param string $bucket
     * @param string $object
     * @return TaggingConfig|null
     * @throws OssException|RequestCore_Exception
     */
    public function getObjectTagging($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new GetBucketTagsResult($response);
        return $result->getData();
    }

    /**
     * Deletes the object tagging
     *
     * @param string $bucket
     * @param string $object
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function deleteObjectTagging($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_TAGGING;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Processes the object
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $process process script
     * @return string|null process result, json format
     * @throws OssException|RequestCore_Exception|
     */
    public function processObject($bucket, $object, $process, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'x-oss-process';
        $options[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
        $options[self::OSS_CONTENT] = 'x-oss-process=' . $process;
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }


    /**
     * Async Process the object
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $asyncProcess async process script
     * @param null $options
     * @return string|null process result, json format
     * @throws OssException
     * @throws RequestCore_Exception
     */
    public function asyncProcessObject($bucket, $object, $asyncProcess, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'x-oss-async-process';
        $options[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
        $options[self::OSS_CONTENT] = 'x-oss-async-process=' . $asyncProcess;
        $response = $this->auth($options);
        $result = new BodyResult($response);
        return $result->getData();
    }

    /**
     * Gets the part size according to the preferred part size.
     * If the specified part size is too small or too big, it will return a min part or max part size instead.
     * Otherwise returns the specified part size.
     * @param int $partSize
     * @return int
     */
    private function computePartSize($partSize)
    {
        $partSize = (integer)$partSize;
        if ($partSize <= self::OSS_MIN_PART_SIZE) {
            $partSize = self::OSS_MIN_PART_SIZE;
        } elseif ($partSize > self::OSS_MAX_PART_SIZE) {
            $partSize = self::OSS_MAX_PART_SIZE;
        }
        return $partSize;
    }

    /**
     * Computes the parts count, size and start position according to the file size and the part size.
     * It must be only called by upload_Part().
     *
     * @param integer $file_size File size
     * @param integer $partSize part size. Default is 5MB
     * @return array An array contains key-value pairs--the key is `seekTo`and value is `length`.
     */
    public function generateMultiuploadParts($file_size, $partSize = 5242880)
    {
        $i = 0;
        $size_count = $file_size;
        $values = array();
        $partSize = $this->computePartSize($partSize);
        while ($size_count > 0) {
            $size_count -= $partSize;
            $values[] = array(
                self::OSS_SEEK_TO => ($partSize * $i),
                self::OSS_LENGTH => (($size_count > 0) ? $partSize : ($size_count + $partSize)),
            );
            $i++;
        }
        return $values;
    }

    /**
     * Initialize a multi-part upload
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param array $options Key-Value array
     * @return string|null returns upload id
     * @throws OssException|RequestCore_Exception
     */
    public function initiateMultipartUpload($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = 'uploads';
        $options[self::OSS_CONTENT] = '';

        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object);
        }
        if (!isset($options[self::OSS_HEADERS])) {
            $options[self::OSS_HEADERS] = array();
        }
        $response = $this->auth($options);
        $result = new InitiateMultipartUploadResult($response);
        return $result->getData();
    }

    /**
     * Upload a part in a multiparts upload.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $uploadId
     * @param array $options Key-Value array
     * @return string|null eTag
     * @throws OssException|RequestCore_Exception
     */
    public function uploadPart($bucket, $object, $uploadId, $options = null)
    {
        $this->precheckCommon($bucket, $object, $options);
        $this->precheckParam($options, self::OSS_FILE_UPLOAD, __FUNCTION__);
        $this->precheckParam($options, self::OSS_PART_NUM, __FUNCTION__);

        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_UPLOAD_ID] = $uploadId;

        if (isset($options[self::OSS_LENGTH])) {
            $options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
        }
        $response = $this->auth($options);
        $result = new UploadPartResult($response);
        return $result->getData();
    }

    /**
     * Gets the uploaded parts.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $uploadId uploadId
     * @param array $options Key-Value array
     * @return ListPartsInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listParts($bucket, $object, $uploadId, $options = null)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_UPLOAD_ID] = $uploadId;
        $options[self::OSS_QUERY_STRING] = array();
        foreach (array('max-parts', 'part-number-marker') as $param) {
            if (isset($options[$param])) {
                $options[self::OSS_QUERY_STRING][$param] = $options[$param];
                unset($options[$param]);
            }
        }
        $response = $this->auth($options);
        $result = new ListPartsResult($response);
        return $result->getData();
    }

    /**
     * Abort a multiparts upload
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $uploadId uploadId
     * @param array $options Key-Value name
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function abortMultipartUpload($bucket, $object, $uploadId, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_UPLOAD_ID] = $uploadId;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * Completes a multiparts upload, after all parts are uploaded.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $uploadId uploadId
     * @param array $listParts array( array("PartNumber"=> int, "ETag"=>string))
     * @param array $options Key-Value array
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function completeMultipartUpload($bucket, $object, $uploadId, $listParts, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_UPLOAD_ID] = $uploadId;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        if (is_array($listParts)) {
            $options[self::OSS_CONTENT] = OssUtil::createCompleteMultipartUploadXmlBody($listParts);
        } else {
            $options[self::OSS_CONTENT] = "";
        }

        $response = $this->auth($options);
        if (isset($options[self::OSS_CALLBACK]) && !empty($options[self::OSS_CALLBACK])) {
            $result = new CallbackResult($response);
        } else {
            $result = new PutSetDeleteResult($response);
        }
        return $result->getData();
    }

    /**
     * Lists all ongoing multipart upload events, which means all initialized but not completed or aborted multipart uploads.
     *
     * @param string $bucket bucket
     * @param array $options key-value array--expected keys are 'delimiter', 'key-marker', 'max-uploads', 'prefix', 'upload-id-marker'
     * @return ListMultipartUploadInfo|null
     * @throws OssException|RequestCore_Exception
     */
    public function listMultipartUploads($bucket, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_SUB_RESOURCE] = 'uploads';

        foreach (array('delimiter', 'key-marker', 'max-uploads', 'prefix', 'upload-id-marker') as $param) {
            if (isset($options[$param])) {
                $options[self::OSS_QUERY_STRING][$param] = $options[$param];
                unset($options[$param]);
            }
        }
        $query = isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array();
        $options[self::OSS_QUERY_STRING] = array_merge(
            $query,
            array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL)
        );

        $response = $this->auth($options);
        $result = new ListMultipartUploadResult($response);
        return $result->getData();
    }

    /**
     * Copy an existing file as a part
     *
     * @param string $fromBucket source bucket name
     * @param string $fromObject source object name
     * @param string $toBucket target bucket name
     * @param string $toObject target object name
     * @param int $partNumber Part number
     * @param string $uploadId Upload Id
     * @param array $options Key-Value array---it should have 'start' or 'end' key to specify the range of the source object to copy. If it's not specifed, the whole object is copied.
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function uploadPartCopy($fromBucket, $fromObject, $toBucket, $toObject, $partNumber, $uploadId, $options = NULL)
    {
        $this->precheckCommon($fromBucket, $fromObject, $options);
        $this->precheckCommon($toBucket, $toObject, $options);

        //If $options['isFullCopy'] is not set, copy from the beginning
        $start_range = "0";
        if (isset($options['start'])) {
            $start_range = $options['start'];
        }
        $end_range = "";
        if (isset($options['end'])) {
            $end_range = $options['end'];
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_BUCKET] = $toBucket;
        $options[self::OSS_OBJECT] = $toObject;
        $options[self::OSS_PART_NUM] = $partNumber;
        $options[self::OSS_UPLOAD_ID] = $uploadId;

        if (!isset($options[self::OSS_HEADERS])) {
            $options[self::OSS_HEADERS] = array();
        }

        $param = '/' . $fromBucket . '/' . rawurlencode($fromObject);
        if (isset($options[self::OSS_VERSION_ID])) {
            $param = $param . '?versionId=' . $options[self::OSS_VERSION_ID];
            unset($options[self::OSS_VERSION_ID]);
        }

        $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = $param;
        $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE_RANGE] = "bytes=" . $start_range . "-" . $end_range;
        $response = $this->auth($options);
        $result = new UploadPartResult($response);
        return $result->getData();
    }

    /**
     * A higher level API for uploading a file with multipart upload. It consists of initialization, parts upload and completion.
     *
     * @param string $bucket bucket name
     * @param string $object object name
     * @param string $file The local file to upload
     * @param array $options Key-Value array
     * @return null
     * @throws OssException|RequestCore_Exception
     */
    public function multiuploadFile($bucket, $object, $file, $options = null)
    {
        $this->precheckCommon($bucket, $object, $options);
        if (isset($options[self::OSS_LENGTH])) {
            $options[self::OSS_CONTENT_LENGTH] = $options[self::OSS_LENGTH];
            unset($options[self::OSS_LENGTH]);
        }
        if (empty($file)) {
            throw new OssException("parameter invalid, file is empty");
        }
        $uploadFile = $this->encodeFilePath($file);
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $uploadFile);
        }

        $upload_position = isset($options[self::OSS_SEEK_TO]) ? (integer)$options[self::OSS_SEEK_TO] : 0;

        if (isset($options[self::OSS_CONTENT_LENGTH])) {
            $upload_file_size = (integer)$options[self::OSS_CONTENT_LENGTH];
        } else {
            $upload_file_size = sprintf('%u', filesize($uploadFile));

            if ($upload_file_size !== false) {
                $upload_file_size -= $upload_position;
            }
        }

        if ($upload_position === false || !isset($upload_file_size) || $upload_file_size === false || $upload_file_size < 0) {
            throw new OssException('The size of `fileUpload` cannot be determined in ' . __FUNCTION__ . '().');
        }
        // Computes the part size and assign it to options.
        if (isset($options[self::OSS_PART_SIZE])) {
            $options[self::OSS_PART_SIZE] = $this->computePartSize($options[self::OSS_PART_SIZE]);
        } else {
            $options[self::OSS_PART_SIZE] = self::OSS_MID_PART_SIZE;
        }

        $is_check_md5 = $this->isCheckMD5($options);
        // if the file size is less than part size, use simple file upload.
        if ($upload_file_size < $options[self::OSS_PART_SIZE] && !isset($options[self::OSS_UPLOAD_ID])) {
            return $this->uploadFile($bucket, $object, $uploadFile, $options);
        }

        // Using multipart upload, initialize if no OSS_UPLOAD_ID is specified in options.
        if (isset($options[self::OSS_UPLOAD_ID])) {
            $uploadId = $options[self::OSS_UPLOAD_ID];
        } else {
            // initialize
            $uploadId = $this->initiateMultipartUpload($bucket, $object, $options);
        }

        // generates the parts information and upload them one by one
        $pieces = $this->generateMultiuploadParts($upload_file_size, (integer)$options[self::OSS_PART_SIZE]);
        $response_upload_part = array();
        foreach ($pieces as $i => $piece) {
            $from_pos = $upload_position + (integer)$piece[self::OSS_SEEK_TO];
            $to_pos = (integer)$piece[self::OSS_LENGTH] + $from_pos - 1;
            $up_options = array(
                self::OSS_FILE_UPLOAD => $uploadFile,
                self::OSS_PART_NUM => ($i + 1),
                self::OSS_SEEK_TO => $from_pos,
                self::OSS_LENGTH => $to_pos - $from_pos + 1,
                self::OSS_CHECK_MD5 => $is_check_md5,
            );
            if ($is_check_md5) {
                $content_md5 = OssUtil::getMd5SumForFile($uploadFile, $from_pos, $to_pos);
                $up_options[self::OSS_CONTENT_MD5] = $content_md5;
            }
            $response_upload_part[] = $this->uploadPart($bucket, $object, $uploadId, $up_options);
        }

        $uploadParts = array();
        foreach ($response_upload_part as $i => $etag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $etag,
            );
        }

        //build complete options
        $cmp_options = null;
        if (isset($options[self::OSS_HEADERS]) && isset($options[self::OSS_HEADERS][self::OSS_REQUEST_PAYER])) {
            $cmp_options = array(
                OssClient::OSS_HEADERS => array(
                    OssClient::OSS_REQUEST_PAYER => $options[self::OSS_HEADERS][self::OSS_REQUEST_PAYER],
                ));
        }
        return $this->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts, $cmp_options);
    }

    /**
     * Uploads the local directory to the specified bucket into specified folder (prefix)
     *
     * @param string $bucket bucket name
     * @param string $prefix The object key prefix. Typically it's folder name. The name should not end with '/' as the API appends it automatically.
     * @param string $localDirectory The local directory to upload
     * @param string $exclude To excluded directories
     * @param bool $recursive Recursive flag. True: Recursively upload all datas under the local directory; False: only upload first layer's files.
     * @param bool $checkMd5
     * @return array Returns two list: array("succeededList" => array("object"), "failedList" => array("object"=>"errorMessage"))
     * @throws OssException
     */
    public function uploadDir($bucket, $prefix, $localDirectory, $exclude = '.|..|.svn|.git', $recursive = false, $checkMd5 = true)
    {
        $retArray = array("succeededList" => array(), "failedList" => array());
        if (empty($bucket)) throw new OssException("parameter error, bucket is empty");
        if (!is_string($prefix)) throw new OssException("parameter error, prefix is not string");
        if (empty($localDirectory)) throw new OssException("parameter error, localDirectory is empty");
        $directory = $localDirectory;
        $directory = $this->encodeFilePath($directory);
        //If it's not the local directory, throw OSSException.
        if (!is_dir($directory)) {
            throw new OssException('parameter error: ' . $directory . ' is not a directory, please check it');
        }
        //read directory
        $file_list_array = OssUtil::readDir($directory, $exclude, $recursive);
        if (!$file_list_array) {
            throw new OssException($directory . ' is empty...');
        }
        foreach ($file_list_array as $k => $item) {
            if (is_dir($item['path'])) {
                continue;
            }
            $options = array(
                self::OSS_PART_SIZE => self::OSS_MIN_PART_SIZE,
                self::OSS_CHECK_MD5 => $checkMd5,
            );
            //mbstring to utf-8
            $fileName = $this->decodeFilePath($item['file']);
            $realObject = (!empty($prefix) ? $prefix . '/' : '') . $fileName;

            try {
                $this->multiuploadFile($bucket, $realObject, $item['path'], $options);
                $retArray["succeededList"][] = $realObject;
            } catch (OssException $e) {
                $retArray["failedList"][$realObject] = $e->getMessage();
            }
        }
        return $retArray;
    }

    /**
     * Sign URL with specified expiration time in seconds (timeout) and HTTP method.
     * The signed URL could be used to access the object directly.
     *
     * @param string $bucket
     * @param string $object
     * @param int $timeout expiration time in seconds.
     * @param string $method
     * @param array $options Key-Value array
     * @return string
     * @throws OssException
     */
    public function signUrl($bucket, $object, $timeout = 60, $method = self::OSS_HTTP_GET, $options = NULL)
    {
        $expiration = time() + $timeout;
        return $this->generatePresignedUrl($bucket, $object, $expiration, $method, $options);
    }

    /**
     * Sign URL with specified expiration time in seconds and HTTP method.
     * The signed URL could be used to access the object directly.
     *
     * @param string $bucket
     * @param string $object
     * @param int $expiration expiration time of the Url, unix epoch, since 1970.1.1 00.00.00 UTC
     * @param string $method
     * @param array $options Key-Value array
     * @return string
     * @throws OssException
     */
    public function generatePresignedUrl($bucket, $object, $expiration, $method = self::OSS_HTTP_GET, $options = NULL)
    {
        $this->precheckObjectExt($object, $this->enableStrictObjName);
        $this->precheckCommon($bucket, $object, $options);
        $cred = $this->provider->getCredentials();
        //method
        if (self::OSS_HTTP_GET !== $method && self::OSS_HTTP_PUT !== $method) {
            throw new OssException("method is invalid");
        }
        // Should https or http be used?
        $scheme = $this->useSSL ? 'https://' : 'http://';
        // gets the host name. If the host name is public domain or private domain, form a third level domain by prefixing the bucket name on the domain name.
        $hostname = $this->generateHostname($bucket);
        $path = $this->generatePath($bucket, $object);
        $headers = $this->generateHeaders($options, '');
        $query_string = $this->generateQueryString($options);
        $query_string = empty($query_string) ? '' : '?' . $query_string;
        $requestUrl = $scheme . $hostname . $path . $query_string;
        //Creates the request
        $request = new RequestCore($requestUrl);
        $request->set_method($method);
        if (isset($options[self::OSS_CALLBACK])) {
            $headers[self::OSS_CALLBACK] = base64_encode($options[self::OSS_CALLBACK]);
        }
        if (isset($options[self::OSS_CALLBACK_VAR])) {
            $headers[self::OSS_CALLBACK_VAR] = base64_encode($options[self::OSS_CALLBACK_VAR]);
        }
        foreach ($headers as $header_key => $header_value) {
            $header_value = trim($header_value);
            if (empty($header_value)) {
                continue;
            }
            $request->add_header($header_key, $header_value);
        }
        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => $this->getRegion(),
            'product' => $this->getProduct(),
            'expiration' => $expiration,
        );
        $this->signer->presign($request, $cred, $signingOpt);
        return $request->request_url;
    }

    /**
     * validates options. Create a empty array if it's NULL.
     *
     * @param array $options
     * @throws OssException
     */
    private function precheckOptions(&$options)
    {
        OssUtil::validateOptions($options);
        if (!$options) {
            $options = array();
        }
    }

    /**
     * Validates bucket parameter
     *
     * @param string $bucket
     * @param string $errMsg
     * @throws OssException
     */
    private function precheckBucket($bucket, $errMsg = 'bucket is not allowed empty')
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($bucket, $errMsg);
        if (!OssUtil::validateBucket($bucket)) {
            throw new OssException('"' . $bucket . '"' . 'bucket name is invalid');
        }
    }

    /**
     * validates object parameter
     *
     * @param string $object
     * @throws OssException
     */
    private function precheckObject($object)
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($object, "object name is empty");
        if (!OssUtil::validateObject($object)) {
            throw new OssException('"' . $object . '"' . ' object name is invalid');
        }
    }

    /**
     * validates object name start with ? or not
     * @param $object string
     * @param $strict boolean
     * @throws OssException
     */
    private function precheckObjectExt($object, $strict)
    {
        $this->precheckObject($object);
        if ($strict) {
            if (is_string($object) && $object[0] === "?") {
                throw new OssException('"' . $object . '"' . ' object name cannot start with `?`');
            }
        }
    }

    /**
     * Check option restore
     *
     * @param $storage string
     * @throws OssException
     */
    private function precheckStorage($storage)
    {
        if (is_string($storage)) {
            switch ($storage) {
                case self::OSS_STORAGE_ARCHIVE:
                    return;
                case self::OSS_STORAGE_IA:
                    return;
                case self::OSS_STORAGE_STANDARD:
                    return;
                case self::OSS_STORAGE_COLDARCHIVE:
                    return;
                default:
                    break;
            }
        }
        throw new OssException('storage name is invalid');
    }

    /**
     * Validates bucket,options parameters and optionally validate object parameter.
     *
     * @param string $bucket
     * @param string $object
     * @param array $options
     * @param bool $isCheckObject
     */
    private function precheckCommon($bucket, $object, &$options, $isCheckObject = true)
    {
        if ($isCheckObject) {
            $this->precheckObject($object);
        }
        $this->precheckOptions($options);
        $this->precheckBucket($bucket);
    }

    /**
     * checks parameters
     *
     * @param array $options
     * @param string $param
     * @param string $funcName
     * @throws OssException
     */
    private function precheckParam($options, $param, $funcName)
    {
        if (!isset($options[$param])) {
            throw new OssException('The `' . $param . '` options is required in ' . $funcName . '().');
        }
    }

    /**
     * Checks md5
     *
     * @param array $options
     * @return bool|null
     */
    private function isCheckMD5($options)
    {
        return $this->getValue($options, self::OSS_CHECK_MD5, false, true, true);
    }

    /**
     * Gets value of the specified key from the options
     *
     * @param array $options
     * @param string $key
     * @param string $default
     * @param bool $isCheckEmpty
     * @param bool $isCheckBool
     * @return bool|null
     */
    private function getValue($options, $key, $default = NULL, $isCheckEmpty = false, $isCheckBool = false)
    {
        $value = $default;
        if (isset($options[$key])) {
            if ($isCheckEmpty) {
                if (!empty($options[$key])) {
                    $value = $options[$key];
                }
            } else {
                $value = $options[$key];
            }
            unset($options[$key]);
        }
        if ($isCheckBool) {
            if ($value !== true && $value !== false) {
                $value = false;
            }
        }
        return $value;
    }

    /**
     * Gets mimetype
     *
     * @param string $object
     * @return string
     */
    private function getMimeType($object, $file = null)
    {
        if (!is_null($file)) {
            $type = MimeTypes::getMimetype($file);
            if (!is_null($type)) {
                return $type;
            }
        }

        $type = MimeTypes::getMimetype($object);
        if (!is_null($type)) {
            return $type;
        }

        return self::DEFAULT_CONTENT_TYPE;
    }

    /**
     * Validates and executes the request according to OSS API protocol.
     *
     * @param array $options
     * @return ResponseCore|string
     * @throws OssException
     * @throws RequestCore_Exception
     */
    private function auth($options)
    {
        OssUtil::validateOptions($options);
        //Object Encoding
        $this->authPrecheckObjectEncoding($options);
        //Validates ACL
        $this->authPrecheckAcl($options);
        $cred = $this->provider->getCredentials();
        $this->checkCredentials($cred);

        $bucket = isset($options[self::OSS_BUCKET]) ? $options[self::OSS_BUCKET] : '';
        $object = isset($options[self::OSS_OBJECT]) ? $options[self::OSS_OBJECT] : '';

        // Should https or http be used?
        $scheme = $this->useSSL ? 'https://' : 'http://';
        // gets the host name. If the host name is public domain or private domain, form a third level domain by prefixing the bucket name on the domain name.
        $hostname = $this->generateHostname($bucket);
        $path = $this->generatePath($bucket, $object);
        $headers = $this->generateHeaders($options, $hostname);
        $query_string = $this->generateQueryString($options);
        $query_string = empty($query_string) ? '' : '?' . $query_string;
        $requestUrl = $scheme . $hostname . $path . $query_string;

        //Creates the request
        $request = new RequestCore($requestUrl, $this->requestProxy);
        $request->set_useragent($this->generateUserAgent());
        // Streaming uploads
        if (isset($options[self::OSS_FILE_UPLOAD])) {
            if (is_resource($options[self::OSS_FILE_UPLOAD])) {
                $length = null;

                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO])) {
                    $stats = fstat($options[self::OSS_FILE_UPLOAD]);
                    if ($stats && $stats[self::OSS_SIZE] >= 0) {
                        $length = $stats[self::OSS_SIZE] - (integer)$options[self::OSS_SEEK_TO];
                    }
                }
                $request->set_read_stream($options[self::OSS_FILE_UPLOAD], $length);
            } else {
                $request->set_read_file($options[self::OSS_FILE_UPLOAD]);
                $length = $request->read_stream_size;
                if (isset($options[self::OSS_CONTENT_LENGTH])) {
                    $length = $options[self::OSS_CONTENT_LENGTH];
                } elseif (isset($options[self::OSS_SEEK_TO]) && isset($length)) {
                    $length -= (integer)$options[self::OSS_SEEK_TO];
                }
                $request->set_read_stream_size($length);
            }
        }
        if (isset($options[self::OSS_SEEK_TO])) {
            $request->set_seek_position((integer)$options[self::OSS_SEEK_TO]);
        }
        if (isset($options[self::OSS_FILE_DOWNLOAD])) {
            if (is_resource($options[self::OSS_FILE_DOWNLOAD])) {
                $request->set_write_stream($options[self::OSS_FILE_DOWNLOAD]);
            } else {
                $request->set_write_file($options[self::OSS_FILE_DOWNLOAD]);
            }
        }
        if (isset($options[self::OSS_METHOD])) {
            $request->set_method($options[self::OSS_METHOD]);
        }
        if (isset($options[self::OSS_CONTENT])) {
            $request->set_body($options[self::OSS_CONTENT]);
            if (isset($headers[self::OSS_CONTENT_TYPE]) && $headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
                $headers[self::OSS_CONTENT_TYPE] = 'application/octet-stream';
            }

            $headers[self::OSS_CONTENT_LENGTH] = strlen($options[self::OSS_CONTENT]);
            $headers[self::OSS_CONTENT_MD5] = base64_encode(md5($options[self::OSS_CONTENT], true));
        }

        if (isset($options[self::OSS_CALLBACK])) {
            $headers[self::OSS_CALLBACK] = base64_encode($options[self::OSS_CALLBACK]);
        }
        if (isset($options[self::OSS_CALLBACK_VAR])) {
            $headers[self::OSS_CALLBACK_VAR] = base64_encode($options[self::OSS_CALLBACK_VAR]);
        }

        if (!isset($headers[self::OSS_ACCEPT_ENCODING])) {
            $headers[self::OSS_ACCEPT_ENCODING] = '';
        }

        if (!isset($headers[self::OSS_CONTENT_TYPE])) {
            $headers[self::OSS_CONTENT_TYPE] = self::DEFAULT_CONTENT_TYPE;
        }

        foreach ($headers as $header_key => $header_value) {
            $header_value = trim($header_value);
            if (empty($header_value)) {
                continue;
            }
            $request->add_header($header_key, $header_value);
        }

        // sign request
        $signingOpt = array(
            'bucket' => $bucket,
            'key' => $object,
            'region' => $this->getRegion(),
            'product' => $this->getProduct(),
        );
        if (isset($options[self::OSS_ADDITIONAL_HEADERS])) {
            $signingOpt['additionalHeaders'] = $options[self::OSS_ADDITIONAL_HEADERS];
        }

        $this->signer->sign($request, $cred, $signingOpt);
        $string_to_sign = isset($signingOpt['string_to_sign']) ? $signingOpt['string_to_sign'] : '';

        if ($this->timeout !== 0) {
            $request->timeout = $this->timeout;
        }
        if ($this->connectTimeout !== 0) {
            $request->connect_timeout = $this->connectTimeout;
        }

        try {
            $request->send_request();
        } catch (RequestCore_Exception $e) {
            throw(new OssException('RequestCoreException: ' . $e->getMessage()));
        }
        $response_header = $request->get_response_header();
        $response_header['oss-request-url'] = $requestUrl;
        $response_header['oss-redirects'] = $this->redirects;
        $response_header['oss-stringtosign'] = $string_to_sign;
        $response_header['oss-requestheaders'] = $request->request_headers;

        $data = new ResponseCore($response_header, $request->get_response_body(), $request->get_response_code());
        //retry if OSS Internal Error
        if ((integer)$request->get_response_code() === 500) {
            if ($this->redirects <= $this->maxRetries) {
                //Sets the sleep time betwen each retry.
                $delay = (integer)(pow(4, $this->redirects) * 100000);
                usleep($delay);
                $this->redirects++;
                $data = $this->auth($options);
            }
        }

        $this->redirects = 0;
        return $data;
    }

    /**
     * Sets the max retry count
     *
     * @param int $maxRetries
     * @return void
     */
    public function setMaxTries($maxRetries = 3)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * Gets the max retry count
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /**
     * Enaable/disable STS in the URL. This is to determine the $sts value passed from constructor take effect or not.
     *
     * @param boolean $enable
     */
    public function setSignStsInUrl($enable)
    {
    }

    /**
     * @return boolean
     */
    public function isUseSSL()
    {
        return $this->useSSL;
    }

    /**
     * @param boolean $useSSL
     */
    public function setUseSSL($useSSL)
    {
        $this->useSSL = $useSSL;
    }

    /**
     * Checks the object's encoding. Convert it to UTF8 if it's in GBK or GB2312
     *
     * @param mixed $options parameter
     */
    private function authPrecheckObjectEncoding(&$options)
    {
        if ($this->checkObjectEncoding !== true) {
            return;
        }

        if (!isset($options[self::OSS_OBJECT])) {
            return;
        }

        try {
            $tmp_object = $options[self::OSS_OBJECT];
            $encoding = array('UTF-8', 'GB2312', 'GBK');
            $encode = mb_detect_encoding($tmp_object, $encoding);
            if ($encode === 'UTF-8' || $encode === false) {
                return;
            }
            $tmp_object = iconv($encode, "UTF-8", $tmp_object);
            if ($tmp_object === false) {
                return;
            }
            $options[self::OSS_OBJECT] = $tmp_object;
        } catch (\Exception $e) {
            //IGNORE
        }
    }

    /**
     * Checks if the ACL is one of the 3 predefined one. Throw OSSException if not.
     *
     * @param $options
     * @throws OssException
     */
    private function authPrecheckAcl($options)
    {
        if (isset($options[self::OSS_HEADERS][self::OSS_ACL]) && !empty($options[self::OSS_HEADERS][self::OSS_ACL])) {
            if (!in_array(strtolower($options[self::OSS_HEADERS][self::OSS_ACL]), self::$OSS_ACL_TYPES)) {
                throw new OssException($options[self::OSS_HEADERS][self::OSS_ACL] . ':' . 'acl is invalid(private,public-read,public-read-write)');
            }
        }
    }

    /**
     * Gets the host name for the current request.
     * It could be either a third level domain (prefixed by bucket name) or second level domain if it's CName or IP
     *
     * @param $bucket
     * @return string The host name without the protocol scheem (e.g. https://)
     */
    private function generateHostname($bucket)
    {
        if ($this->hostType === self::OSS_HOST_TYPE_IP || $this->hostType === self::OSS_HOST_TYPE_PATH_STYLE) {
            $hostname = $this->hostname;
        } elseif ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
            $hostname = $this->hostname;
        } else {
            // Private domain or public domain
            $hostname = ($bucket == '') ? $this->hostname : ($bucket . '.') . $this->hostname;
        }
        return $hostname;
    }

    /**
     * Gets the Uri path in the current request
     *
     * @param $bucket
     * @param $object
     * @return string return the resource uri.
     */
    private function generatePath($bucket, $object)
    {
        $paths = array();
        // +bucket
        if ('' !== $bucket) {
            if ($this->hostType === self::OSS_HOST_TYPE_IP || $this->hostType === self::OSS_HOST_TYPE_PATH_STYLE) {
                $paths[] = $bucket;
                if ('' === $object) {
                    $paths[] = '';
                }
            }
        }
        // + object
        if ('' !== $object && '/' !== $object) {
            $paths[] = str_replace(array('%2F'), array('/'), rawurlencode($object));
        }
        return '/' . implode('/', $paths);
    }

    /**
     * generates query string
     *
     * @param mixed $options
     * @return string
     */
    private function generateQueryString($options)
    {
        //query parameters
        $query = array();
        $queryList = array(
            self::OSS_PART_NUM,
            'response-content-type',
            'response-content-language',
            'response-cache-control',
            'response-content-encoding',
            'response-expires',
            'response-content-disposition',
            self::OSS_UPLOAD_ID,
            self::OSS_COMP,
            self::OSS_LIVE_CHANNEL_STATUS,
            self::OSS_LIVE_CHANNEL_START_TIME,
            self::OSS_LIVE_CHANNEL_END_TIME,
            self::OSS_PROCESS,
            self::OSS_POSITION,
            self::OSS_SYMLINK,
            self::OSS_RESTORE,
            self::OSS_TAGGING,
            self::OSS_WORM_ID,
            self::OSS_TRAFFIC_LIMIT,
            self::OSS_VERSION_ID,
            self::OSS_CONTINUATION_TOKEN,
            self::OSS_CNAME,
        );
        foreach ($queryList as $item) {
            if (isset($options[$item])) {
                $query[$item] = $options[$item];
            }
        }
        if (isset($options[self::OSS_QUERY_STRING])) {
            $query = array_merge($query, $options[self::OSS_QUERY_STRING]);
        }
        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $query[$options[self::OSS_SUB_RESOURCE]] = '';
        }

        return OssUtil::toQueryString($query);
    }

    /**
     * Initialize headers
     *
     * @param mixed $options
     * @param string $hostname hostname
     * @return array
     */
    private function generateHeaders($options, $hostname)
    {
        $headers = array();

        if (!empty($hostname)) {
            $headers[self::OSS_HOST] = $hostname;
        }

        if (isset($options[self::OSS_CONTENT_TYPE])) {
            $headers[self::OSS_CONTENT_TYPE] = $options[self::OSS_CONTENT_TYPE];
        }

        if (isset($options[self::OSS_DATE])) {
            $headers[self::OSS_DATE] = $options[self::OSS_DATE];
        }

        if (isset($options[self::OSS_CONTENT_MD5])) {
            $headers[self::OSS_CONTENT_MD5] = $options[self::OSS_CONTENT_MD5];
        }

        //Merge HTTP headers
        if (isset($options[self::OSS_HEADERS])) {
            $headers = array_merge($headers, $options[self::OSS_HEADERS]);
        }
        return $headers;
    }

    /**
     * Generates UserAgent
     *
     * @return string
     */
    private function generateUserAgent()
    {
        return self::OSS_NAME . "/" . self::OSS_VERSION . " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
    }

    /**
     * Checks endpoint type and returns the endpoint without the protocol schema.
     * Figures out the domain's type (ip, cname or private/public domain).
     *
     * @param string $endpoint
     * @param boolean $isCName
     * @return string The domain name without the protocol schema.
     * @throws OssException
     */
    private function checkEndpoint($endpoint, $isCName)
    {
        $ret_endpoint = null;
        if (strpos($endpoint, 'http://') === 0) {
            $ret_endpoint = substr($endpoint, strlen('http://'));
        } elseif (strpos($endpoint, 'https://') === 0) {
            $ret_endpoint = substr($endpoint, strlen('https://'));
            $this->useSSL = true;
        } else {
            $ret_endpoint = $endpoint;
        }

        $ret_endpoint = OssUtil::getHostPortFromEndpoint($ret_endpoint);

        if ($isCName) {
            $this->hostType = self::OSS_HOST_TYPE_CNAME;
        } elseif (OssUtil::isIPFormat($ret_endpoint)) {
            $this->hostType = self::OSS_HOST_TYPE_IP;
        } else {
            $this->hostType = self::OSS_HOST_TYPE_NORMAL;
        }
        return $ret_endpoint;
    }

    /**
     * @param Credentials $credential
     * @throws OssException
     */
    private function checkCredentials($credential)
    {
        if (empty($credential)) {
            throw new OssException("credentials is empty.");
        }
        if (strlen($credential->getAccessKeyId()) == 0) {
            throw new OssException("access key id is empty");
        }
        if (strlen($credential->getAccessKeySecret()) == 0) {
            throw new OssException("access key secret is empty");
        }
    }

    /**
     * For get Sign Product
     * @return string
     */
    private function getProduct()
    {
        if (!empty($this->cloudBoxId)) {
            return self::OSS_CLOUDBOX_PRODUCT;
        }
        return self::OSS_DEFAULT_PRODUCT;
    }

    /**
     * For get Sign Region
     * @return mixed
     */
    private function getRegion()
    {
        if (!empty($this->cloudBoxId)) {
            return $this->cloudBoxId;
        }
        return $this->region;
    }

    /**
     * Encodes the file path from UTF-8 to GBK.
     *
     * @param $filepath
     * @return string
     */
    private function encodeFilePath($filepath)
    {
        if ($this->filePathCompatible !== true) {
            return $filepath;
        }

        if (empty($filepath)) {
            return $filepath;
        }

        try {
            $encoding = array('UTF-8', 'GB2312', 'GBK');
            $encode = mb_detect_encoding($filepath, $encoding);
            if ($encode !== 'UTF-8') {
                return $filepath;
            }
            $tmp = iconv($encode, 'GBK', $filepath);
            if ($tmp !== false) {
                $filepath = $tmp;
            }
        } catch (\Exception $e) {
            //IGNORE
        }
        return $filepath;
    }

    /**
     * Decodes the file path from GBK  to UTF-8.
     *
     * @param $filepath
     * @return string
     */
    private function decodeFilePath($filepath)
    {
        if ($this->filePathCompatible !== true) {
            return $filepath;
        }
        if (empty($filepath)) {
            return $filepath;
        }

        try {
            $encoding = array('UTF-8', 'GB2312', 'GBK');
            $encode = mb_detect_encoding($filepath, $encoding);
            if ($encode === 'UTF-8' || $encode === false) {
                return $filepath;
            }
            $tmp = iconv($encode, 'UTF-8', $filepath);
            if ($tmp !== false) {
                $filepath = $tmp;
            }
        } catch (\Exception $e) {
            //IGNORE
        }
        return $filepath;
    }

    /**
     * Check if all dependent extensions are installed correctly.
     * For now only "curl" is needed.
     * @throws OssException
     */
    public static function checkEnv()
    {
        if (function_exists('get_loaded_extensions')) {
            //Test curl extension
            $enabled_extension = array("curl");
            $extensions = get_loaded_extensions();
            if ($extensions) {
                foreach ($enabled_extension as $item) {
                    if (!in_array($item, $extensions)) {
                        throw new OssException("Extension {" . $item . "} is not installed or not enabled, please check your php env.");
                    }
                }
            } else {
                throw new OssException("function get_loaded_extensions not found.");
            }
        } else {
            throw new OssException('Function get_loaded_extensions has been disabled, please check php config.');
        }
    }

    /**
     * Sets the http's timeout (in seconds)
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Sets the http's connection timeout (in seconds)
     *
     * @param int $connectTimeout
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
    }

    // Constants for Life cycle
    const OSS_LIFECYCLE_EXPIRATION = "Expiration";
    const OSS_LIFECYCLE_TIMING_DAYS = "Days";
    const OSS_LIFECYCLE_TIMING_DATE = "Date";
    //OSS Internal constants
    const OSS_BUCKET = 'bucket';
    const OSS_OBJECT = 'object';
    const OSS_HEADERS = OssUtil::OSS_HEADERS;
    const OSS_ADDITIONAL_HEADERS = 'additionalHeaders';
    const OSS_METHOD = 'method';
    const OSS_QUERY = 'query';
    const OSS_BASENAME = 'basename';
    const OSS_MAX_KEYS = 'max-keys';
    const OSS_UPLOAD_ID = 'uploadId';
    const OSS_PART_NUM = 'partNumber';
    const OSS_COMP = 'comp';
    const OSS_LIVE_CHANNEL_STATUS = 'status';
    const OSS_LIVE_CHANNEL_START_TIME = 'startTime';
    const OSS_LIVE_CHANNEL_END_TIME = 'endTime';
    const OSS_POSITION = 'position';
    const OSS_MAX_KEYS_VALUE = 100;
    const OSS_MAX_OBJECT_GROUP_VALUE = OssUtil::OSS_MAX_OBJECT_GROUP_VALUE;
    const OSS_MAX_PART_SIZE = OssUtil::OSS_MAX_PART_SIZE;
    const OSS_MID_PART_SIZE = OssUtil::OSS_MID_PART_SIZE;
    const OSS_MIN_PART_SIZE = OssUtil::OSS_MIN_PART_SIZE;
    const OSS_FILE_SLICE_SIZE = 8192;
    const OSS_PREFIX = 'prefix';
    const OSS_DELIMITER = 'delimiter';
    const OSS_MARKER = 'marker';
    const OSS_FETCH_OWNER = 'fetch-owner';
    const OSS_START_AFTER = 'start-after';
    const OSS_CONTINUATION_TOKEN = 'continuation-token';
    const OSS_ACCEPT_ENCODING = 'Accept-Encoding';
    const OSS_CONTENT_MD5 = 'Content-Md5';
    const OSS_SELF_CONTENT_MD5 = 'x-oss-meta-md5';
    const OSS_CONTENT_TYPE = 'Content-Type';
    const OSS_CONTENT_LENGTH = 'Content-Length';
    const OSS_IF_MODIFIED_SINCE = 'If-Modified-Since';
    const OSS_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    const OSS_IF_MATCH = 'If-Match';
    const OSS_IF_NONE_MATCH = 'If-None-Match';
    const OSS_CACHE_CONTROL = 'Cache-Control';
    const OSS_EXPIRES = 'Expires';

    const OSS_CONTENT_COING = 'Content-Coding';
    const OSS_CONTENT_DISPOSTION = 'Content-Disposition';
    const OSS_RANGE = 'range';
    const OSS_ETAG = 'etag';
    const OSS_LAST_MODIFIED = 'lastmodified';
    const OS_CONTENT_RANGE = 'Content-Range';
    const OSS_CONTENT = OssUtil::OSS_CONTENT;
    const OSS_BODY = 'body';
    const OSS_LENGTH = OssUtil::OSS_LENGTH;
    const OSS_HOST = 'Host';
    const OSS_DATE = 'Date';
    const OSS_AUTHORIZATION = 'Authorization';
    const OSS_FILE_DOWNLOAD = 'fileDownload';
    const OSS_FILE_UPLOAD = 'fileUpload';
    const OSS_PART_SIZE = 'partSize';
    const OSS_SEEK_TO = 'seekTo';
    const OSS_SIZE = 'size';
    const OSS_QUERY_STRING = 'query_string';
    const OSS_SUB_RESOURCE = 'sub_resource';
    const OSS_DEFAULT_PREFIX = 'x-oss-';
    const OSS_CHECK_MD5 = 'checkmd5';
    const OSS_CHECK_OBJECT = 'checkobject';
    const DEFAULT_CONTENT_TYPE = 'application/octet-stream';
    const OSS_SYMLINK_TARGET = 'x-oss-symlink-target';
    const OSS_SYMLINK = 'symlink';
    const OSS_HTTP_CODE = 'http_code';
    const OSS_REQUEST_ID = 'x-oss-request-id';
    const OSS_INFO = 'info';
    const OSS_STORAGE = 'storage';
    const OSS_RESTORE = 'restore';
    const OSS_STORAGE_STANDARD = 'Standard';
    const OSS_STORAGE_IA = 'IA';
    const OSS_STORAGE_ARCHIVE = 'Archive';
    const OSS_STORAGE_COLDARCHIVE = 'ColdArchive';
    const OSS_TAGGING = 'tagging';
    const OSS_WORM_ID = 'wormId';
    const OSS_RESTORE_CONFIG = 'restore-config';
    const OSS_KEY_MARKER = 'key-marker';
    const OSS_VERSION_ID_MARKER = 'version-id-marker';
    const OSS_VERSION_ID = 'versionId';
    const OSS_HEADER_VERSION_ID = 'x-oss-version-id';
    const OSS_CNAME = 'cname';

    //private URLs
    const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
    const OSS_URL_EXPIRES = 'Expires';
    const OSS_URL_SIGNATURE = 'Signature';
    //HTTP METHOD
    const OSS_HTTP_GET = 'GET';
    const OSS_HTTP_PUT = 'PUT';
    const OSS_HTTP_HEAD = 'HEAD';
    const OSS_HTTP_POST = 'POST';
    const OSS_HTTP_DELETE = 'DELETE';
    const OSS_HTTP_OPTIONS = 'OPTIONS';
    //Others
    const OSS_ACL = 'x-oss-acl';
    const OSS_OBJECT_ACL = 'x-oss-object-acl';
    const OSS_OBJECT_GROUP = 'x-oss-file-group';
    const OSS_MULTI_PART = 'uploads';
    const OSS_MULTI_DELETE = 'delete';
    const OSS_OBJECT_COPY_SOURCE = 'x-oss-copy-source';
    const OSS_OBJECT_COPY_SOURCE_RANGE = "x-oss-copy-source-range";
    const OSS_PROCESS = "x-oss-process";
    const OSS_CALLBACK = "x-oss-callback";
    const OSS_CALLBACK_VAR = "x-oss-callback-var";
    const OSS_REQUEST_PAYER = "x-oss-request-payer";
    const OSS_TRAFFIC_LIMIT = "x-oss-traffic-limit";
    //Constants for STS SecurityToken
    const OSS_SECURITY_TOKEN = "x-oss-security-token";
    const OSS_ACL_TYPE_PRIVATE = 'private';
    const OSS_ACL_TYPE_PUBLIC_READ = 'public-read';
    const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';
    const OSS_ENCODING_TYPE = "encoding-type";
    const OSS_ENCODING_TYPE_URL = "url";

    const OSS_LIST_TYPE = "list-type";

    // Domain Types
    const OSS_HOST_TYPE_NORMAL = "normal";//http://bucket.oss-cn-hangzhou.aliyuncs.com/object
    const OSS_HOST_TYPE_IP = "ip";  //http://1.1.1.1/bucket/object
    const OSS_HOST_TYPE_SPECIAL = 'special'; //http://bucket.guizhou.gov/object
    const OSS_HOST_TYPE_CNAME = "cname";  //http://mydomain.com/object
    const OSS_HOST_TYPE_PATH_STYLE = "path-style";  //http://oss-cn-hangzhou.aliyuncs.com/bucket/object
    //OSS ACL array
    static $OSS_ACL_TYPES = array(
        self::OSS_ACL_TYPE_PRIVATE,
        self::OSS_ACL_TYPE_PUBLIC_READ,
        self::OSS_ACL_TYPE_PUBLIC_READ_WRITE
    );
    // OssClient version information
    const OSS_NAME = "aliyun-sdk-php";
    const OSS_VERSION = "2.7.2";
    const OSS_BUILD = "20241028";
    const OSS_AUTHOR = "";
    const OSS_OPTIONS_ORIGIN = 'Origin';
    const OSS_OPTIONS_REQUEST_METHOD = 'Access-Control-Request-Method';
    const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

    // signatrue version information
    const OSS_SIGNATURE_VERSION_V1 = "v1";
    const OSS_SIGNATURE_VERSION_V4 = "v4";
    const OSS_DEFAULT_PRODUCT = "oss";
    const OSS_CLOUDBOX_PRODUCT = "oss-cloudbox";

    //use ssl flag
    private $useSSL = false;
    private $maxRetries = 3;
    private $redirects = 0;

    // user's domain type. It could be one of the four: OSS_HOST_TYPE_NORMAL, OSS_HOST_TYPE_IP, OSS_HOST_TYPE_SPECIAL, OSS_HOST_TYPE_CNAME
    private $hostType = self::OSS_HOST_TYPE_NORMAL;
    private $requestProxy = null;
    /**
     * @var CredentialsProvider
     */
    private $provider;
    private $hostname;
    private $enableStrictObjName;
    private $timeout = 0;
    private $connectTimeout = 0;
    private $cloudBoxId = null;
    private $region = null;
    /**
     * @var SignerV1|SignerV4
     */
    private $signer;

    private $checkObjectEncoding = false;

    private $filePathCompatible;
}
