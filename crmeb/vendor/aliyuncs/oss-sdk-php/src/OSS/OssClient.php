<?php
namespace OSS;

use OSS\Core\MimeTypes;
use OSS\Core\OssException;
use OSS\Http\RequestCore;
use OSS\Http\RequestCore_Exception;
use OSS\Http\ResponseCore;
use OSS\Model\CorsConfig;
use OSS\Model\CnameConfig;
use OSS\Model\LoggingConfig;
use OSS\Model\LiveChannelConfig;
use OSS\Model\LiveChannelInfo;
use OSS\Model\LiveChannelListInfo;
use OSS\Model\StorageCapacityConfig;
use OSS\Result\AclResult;
use OSS\Result\BodyResult;
use OSS\Result\GetCorsResult;
use OSS\Result\GetLifecycleResult;
use OSS\Result\GetLoggingResult;
use OSS\Result\GetRefererResult;
use OSS\Result\GetWebsiteResult;
use OSS\Result\GetCnameResult;
use OSS\Result\GetLocationResult;
use OSS\Result\HeaderResult;
use OSS\Result\InitiateMultipartUploadResult;
use OSS\Result\ListBucketsResult;
use OSS\Result\ListMultipartUploadResult;
use OSS\Model\ListMultipartUploadInfo;
use OSS\Result\ListObjectsResult;
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
use OSS\Result\GetStorageCapacityResult;
use OSS\Result\AppendResult;
use OSS\Model\ObjectListInfo;
use OSS\Result\UploadPartResult;
use OSS\Model\BucketListInfo;
use OSS\Model\LifecycleConfig;
use OSS\Model\RefererConfig;
use OSS\Model\WebsiteConfig;
use OSS\Core\OssUtil;
use OSS\Model\ListPartsInfo;
use OSS\Result\SymlinkResult;

/**
 * Class OssClient
 *
 * Object Storage Service(OSS) 的客户端类，封装了用户通过OSS API对OSS服务的各种操作，
 * 用户通过OssClient实例可以进行Bucket，Object，MultipartUpload, ACL等操作，具体
 * 的接口规则可以参考官方OSS API文档
 */
class OssClient
{
    /**
     * 构造函数
     *
     * 构造函数有几种情况：
     * 1. 一般的时候初始化使用 $ossClient = new OssClient($id, $key, $endpoint)
     * 2. 如果使用CNAME的，比如使用的是www.testoss.com，在控制台上做了CNAME的绑定，
     * 初始化使用 $ossClient = new OssClient($id, $key, $endpoint, true)
     * 3. 如果使用了阿里云SecurityTokenService(STS)，获得了AccessKeyID, AccessKeySecret, Token
     * 初始化使用  $ossClient = new OssClient($id, $key, $endpoint, false, $token)
     * 4. 如果用户使用的endpoint是ip
     * 初始化使用 $ossClient = new OssClient($id, $key, “1.2.3.4:8900”)
     *
     * @param string $accessKeyId 从OSS获得的AccessKeyId
     * @param string $accessKeySecret 从OSS获得的AccessKeySecret
     * @param string $endpoint 您选定的OSS数据中心访问域名，例如oss-cn-hangzhou.aliyuncs.com
     * @param boolean $isCName 是否对Bucket做了域名绑定，并且Endpoint参数填写的是自己的域名
     * @param string $securityToken
     * @param string $requestProxy 添加代理支持
     * @throws OssException
     */
    public function __construct($accessKeyId, $accessKeySecret, $endpoint, $isCName = false, $securityToken = NULL, $requestProxy = NULL)
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
        if (empty($endpoint)) {
            throw new OssException("endpoint is empty");
        }
        $this->hostname = $this->checkEndpoint($endpoint, $isCName);
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->securityToken = $securityToken;
        $this->requestProxy = $requestProxy;

        self::checkEnv();
    }

    /**
     * 列举用户所有的Bucket[GetService], Endpoint类型为cname不能进行此操作
     *
     * @param array $options
     * @throws OssException
     * @return BucketListInfo
     */
    public function listBuckets($options = NULL)
    {
        if ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
            throw new OssException("operation is not permitted with CName host");
        }
        $this->precheckOptions($options);
        $options[self::OSS_BUCKET] = '';
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $response = $this->auth($options);
        $result = new ListBucketsResult($response);
        return $result->getData();
    }

    /**
     * 创建bucket，默认创建的bucket的ACL是OssClient::OSS_ACL_TYPE_PRIVATE
     *
     * @param string $bucket
     * @param string $acl
     * @param array $options
     * @param string $storageType
     * @return null
     */
    public function createBucket($bucket, $acl = self::OSS_ACL_TYPE_PRIVATE, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
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
     * 删除bucket
     * 如果Bucket不为空（Bucket中有Object，或者有分块上传的碎片），则Bucket无法删除，
     * 必须删除Bucket中的所有Object以及碎片后，Bucket才能成功删除。
     *
     * @param string $bucket
     * @param array $options
     * @return null
     */
    public function deleteBucket($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = '/';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 判断bucket是否存在
     *
     * @param string $bucket
     * @return bool
     * @throws OssException
     */
    public function doesBucketExist($bucket)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new ExistResult($response);
        return $result->getData();
    }
    
    /**
     * 获取bucket所属的数据中心位置信息
     *
     * @param string $bucket
     * @param array $options
     * @throws OssException
     * @return string
     */
    public function getBucketLocation($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'location';
        $response = $this->auth($options);
        $result = new GetLocationResult($response);
        return $result->getData();
    }
    
    /**
     * 获取Bucket的Meta信息
     *
     * @param string $bucket
     * @param array $options 具体参考SDK文档
     * @return array
     */
    public function getBucketMeta($bucket, $options = NULL)
    {
    	$this->precheckCommon($bucket, NULL, $options, false);
    	$options[self::OSS_BUCKET] = $bucket;
    	$options[self::OSS_METHOD] = self::OSS_HTTP_HEAD;
    	$options[self::OSS_OBJECT] = '/';
    	$response = $this->auth($options);
    	$result = new HeaderResult($response);
    	return $result->getData();
    }

    /**
     * 获取bucket的ACL配置情况
     *
     * @param string $bucket
     * @param array $options
     * @throws OssException
     * @return string
     */
    public function getBucketAcl($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new AclResult($response);
        return $result->getData();
    }

    /**
     * 设置bucket的ACL配置情况
     *
     * @param string $bucket bucket名称
     * @param string $acl 读写权限，可选值 ['private', 'public-read', 'public-read-write']
     * @param array $options 可以为空
     * @throws OssException
     * @return null
     */
    public function putBucketAcl($bucket, $acl, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_HEADERS] = array(self::OSS_ACL => $acl);
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取object的ACL属性
     *
     * @param string $bucket
     * @param string $object
     * @throws OssException
     * @return string
     */
    public function getObjectAcl($bucket, $object)
    {
        $options = array();
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
     * 设置object的ACL属性
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $acl 读写权限，可选值 ['default', 'private', 'public-read', 'public-read-write']
     * @throws OssException
     * @return null
     */
    public function putObjectAcl($bucket, $object, $acl)
    {
        $this->precheckCommon($bucket, $object, $options, true);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_HEADERS] = array(self::OSS_OBJECT_ACL => $acl);
        $options[self::OSS_SUB_RESOURCE] = 'acl';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取Bucket的访问日志配置情况
     *
     * @param string $bucket bucket名称
     * @param array $options 可以为空
     * @throws OssException
     * @return LoggingConfig
     */
    public function getBucketLogging($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new GetLoggingResult($response);
        return $result->getData();
    }

    /**
     * 开启Bucket访问日志记录功能，只有Bucket的所有者才能更改
     *
     * @param string $bucket bucket名称
     * @param string $targetBucket 日志文件存放的bucket
     * @param string $targetPrefix 日志的文件前缀
     * @param array $options 可以为空
     * @throws OssException
     * @return null
     */
    public function putBucketLogging($bucket, $targetBucket, $targetPrefix, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $this->precheckBucket($targetBucket, 'targetbucket is not allowed empty');
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';

        $loggingConfig = new LoggingConfig($targetBucket, $targetPrefix);
        $options[self::OSS_CONTENT] = $loggingConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 关闭bucket访问日志记录功能
     *
     * @param string $bucket bucket名称
     * @param array $options 可以为空
     * @throws OssException
     * @return null
     */
    public function deleteBucketLogging($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'logging';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 将bucket设置成静态网站托管模式
     *
     * @param string $bucket bucket名称
     * @param WebsiteConfig $websiteConfig
     * @param array $options 可以为空
     * @throws OssException
     * @return null
     */
    public function putBucketWebsite($bucket, $websiteConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $websiteConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取bucket的静态网站托管状态
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return WebsiteConfig
     */
    public function getBucketWebsite($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new GetWebsiteResult($response);
        return $result->getData();
    }

    /**
     * 关闭bucket的静态网站托管模式
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function deleteBucketWebsite($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'website';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 在指定的bucket上设定一个跨域资源共享(CORS)的规则，如果原规则存在则覆盖原规则
     *
     * @param string $bucket bucket名称
     * @param CorsConfig $corsConfig 跨域资源共享配置，具体规则参见SDK文档
     * @param array $options array
     * @throws OssException
     * @return null
     */
    public function putBucketCors($bucket, $corsConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $corsConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取Bucket的CORS配置情况
     *
     * @param string $bucket bucket名称
     * @param array $options 可以为空
     * @throws OssException
     * @return CorsConfig
     */
    public function getBucketCors($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new GetCorsResult($response, __FUNCTION__);
        return $result->getData();
    }

    /**
     * 关闭指定Bucket对应的CORS功能并清空所有规则
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function deleteBucketCors($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cors';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 为指定Bucket增加CNAME绑定
     *
     * @param string $bucket bucket名称
     * @param string $cname
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function addBucketCname($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cname';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[self::OSS_COMP] = 'add';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取指定Bucket已绑定的CNAME列表
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return CnameConfig
     */
    public function getBucketCname($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cname';
        $response = $this->auth($options);
        $result = new GetCnameResult($response);
        return $result->getData();
    }

    /**
     * 解除指定Bucket的CNAME绑定
     *
     * @param string $bucket bucket名称
     * @param CnameConfig $cnameConfig
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function deleteBucketCname($bucket, $cname, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'cname';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $cnameConfig = new CnameConfig();
        $cnameConfig->addCname($cname);
        $options[self::OSS_CONTENT] = $cnameConfig->serializeToXml();
        $options[self::OSS_COMP] = 'delete';

        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 为指定Bucket创建LiveChannel
     *
     * @param string $bucket bucket名称
     * @param string channelName  $channelName
     * @param LiveChannelConfig $channelConfig
     * @param array $options
     * @throws OssException
     * @return LiveChannelInfo
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
     * 设置LiveChannel的status
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param string channelStatus $channelStatus 为enabled或disabled
     * @param array $options
     * @throws OssException
     * @return null 
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
     * 获取LiveChannel信息
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param array $options
     * @throws OssException
     * @return GetLiveChannelInfo
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
     * 获取LiveChannel状态信息
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param array $options
     * @throws OssException
     * @return GetLiveChannelStatus
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
     *获取LiveChannel推流记录
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param array $options
     * @throws OssException
     * @return GetLiveChannelHistory
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
     *获取指定Bucket下的live channel列表
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return LiveChannelListInfo
     */
    public function listBucketLiveChannels($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
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
     * 为指定LiveChannel生成播放列表
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName 
     * @param string $playlistName 指定生成的点播播放列表的名称，必须以“.m3u8”结尾
     * @param array $setTime  startTime和EndTime以unix时间戳格式给定,跨度不能超过一天
     * @throws OssException
     * @return null
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
     * 删除指定Bucket的LiveChannel
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param array $options
     * @throws OssException
     * @return null
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
     * 生成带签名的推流地址
     *
     * @param string $bucket bucket名称
     * @param string channelName $channelName
     * @param int timeout 设置超时时间，单位为秒
     * @param array $options
     * @throws OssException
     * @return 推流地址
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
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));

        $query_items[] = 'OSSAccessKeyId=' . rawurlencode($this->accessKeyId);
        $query_items[] = 'Expires=' . rawurlencode($expires);
        $query_items[] = 'Signature=' . rawurlencode($signature);

        return $proto . $hostname . '/live/' . $channelName . '?' . implode('&', $query_items);
    }

    /**
     * 检验跨域资源请求, 发送跨域请求之前会发送一个preflight请求（OPTIONS）并带上特定的来源域，
     * HTTP方法和header信息等给OSS以决定是否发送真正的请求。 OSS可以通过putBucketCors接口
     * 来开启Bucket的CORS支持，开启CORS功能之后，OSS在收到浏览器preflight请求时会根据设定的
     * 规则评估是否允许本次请求
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $origin 请求来源域
     * @param string $request_method 表明实际请求中会使用的HTTP方法
     * @param string $request_headers 表明实际请求中会使用的除了简单头部之外的headers
     * @param array $options
     * @return array
     * @throws OssException
     * @link http://help.aliyun.com/document_detail/oss/api-reference/cors/OptionObject.html
     */
    public function optionsObject($bucket, $object, $origin, $request_method, $request_headers, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_OPTIONS;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_HEADERS] = array(
            self::OSS_OPTIONS_ORIGIN => $origin,
            self::OSS_OPTIONS_REQUEST_HEADERS => $request_headers,
            self::OSS_OPTIONS_REQUEST_METHOD => $request_method
        );
        $response = $this->auth($options);
        $result = new HeaderResult($response);
        return $result->getData();
    }

    /**
     * 设置Bucket的Lifecycle配置
     *
     * @param string $bucket bucket名称
     * @param LifecycleConfig $lifecycleConfig Lifecycle配置类
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function putBucketLifecycle($bucket, $lifecycleConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $lifecycleConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取Bucket的Lifecycle配置情况
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return LifecycleConfig
     */
    public function getBucketLifecycle($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new GetLifecycleResult($response);
        return $result->getData();
    }

    /**
     * 删除指定Bucket的生命周期配置
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return null
     */
    public function deleteBucketLifecycle($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_DELETE;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'lifecycle';
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 设置一个bucket的referer访问白名单和是否允许referer字段为空的请求访问
     * Bucket Referer防盗链具体见OSS防盗链
     *
     * @param string $bucket bucket名称
     * @param RefererConfig $refererConfig
     * @param array $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketReferer($bucket, $refererConfig, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'referer';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $options[self::OSS_CONTENT] = $refererConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取Bucket的Referer配置情况
     * Bucket Referer防盗链具体见OSS防盗链
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return RefererConfig
     */
    public function getBucketReferer($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'referer';
        $response = $this->auth($options);
        $result = new GetRefererResult($response);
        return $result->getData();
    }
    
    /**
     * 设置bucket的容量大小，单位GB
     * 当bucket的容量大于设置的容量时，禁止继续写入
     *
     * @param string $bucket bucket名称
     * @param int $storageCapacity
     * @param array $options
     * @return ResponseCore
     * @throws null
     */
    public function putBucketStorageCapacity($bucket, $storageCapacity, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'qos';
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        $storageCapacityConfig = new StorageCapacityConfig($storageCapacity);
        $options[self::OSS_CONTENT] = $storageCapacityConfig->serializeToXml();
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }
    
    /**
     * 获取bucket的容量大小，单位GB
     *
     * @param string $bucket bucket名称
     * @param array $options
     * @throws OssException
     * @return int
     */
    public function getBucketStorageCapacity($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_SUB_RESOURCE] = 'qos';
        $response = $this->auth($options);
        $result = new GetStorageCapacityResult($response);
        return $result->getData();
    }

    /**
     * 获取bucket下的object列表
     *
     * @param string $bucket
     * @param array $options
     * 其中options中的参数如下
     * $options = array(
     *      'max-keys'  => max-keys用于限定此次返回object的最大数，如果不设定，默认为100，max-keys取值不能大于1000。
     *      'prefix'    => 限定返回的object key必须以prefix作为前缀。注意使用prefix查询时，返回的key中仍会包含prefix。
     *      'delimiter' => 是一个用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现delimiter字符之间的object作为一组元素
     *      'marker'    => 用户设定结果从marker之后按字母排序的第一个开始返回。
     *)
     * 其中 prefix，marker用来实现分页显示效果，参数的长度必须小于256字节。
     * @throws OssException
     * @return ObjectListInfo
     */
    public function listObjects($bucket, $options = NULL)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_OBJECT] = '/';
        $options[self::OSS_HEADERS] = array(
            self::OSS_DELIMITER => isset($options[self::OSS_DELIMITER]) ? $options[self::OSS_DELIMITER] : '/',
            self::OSS_PREFIX => isset($options[self::OSS_PREFIX]) ? $options[self::OSS_PREFIX] : '',
            self::OSS_MAX_KEYS => isset($options[self::OSS_MAX_KEYS]) ? $options[self::OSS_MAX_KEYS] : self::OSS_MAX_KEYS_VALUE,
            self::OSS_MARKER => isset($options[self::OSS_MARKER]) ? $options[self::OSS_MARKER] : '',
        );
        $query = isset($options[self::OSS_QUERY_STRING]) ? $options[self::OSS_QUERY_STRING] : array();
        $options[self::OSS_QUERY_STRING] = array_merge(
            $query,
            array(self::OSS_ENCODING_TYPE => self::OSS_ENCODING_TYPE_URL)
        );

        $response = $this->auth($options);
        $result = new ListObjectsResult($response);
        return $result->getData();
    }

    /**
     * 创建虚拟目录 (本函数会在object名称后增加'/', 所以创建目录的object名称不需要'/'结尾，否则，目录名称会变成'//')
     *
     * 暂不开放此接口
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param array $options
     * @return null
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
     * 上传内存中的内容
     *
     * @param string $bucket bucket名称
     * @param string $object objcet名称
     * @param string $content 上传的内容
     * @param array $options
     * @return null
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
     * 创建symlink
     * @param string $bucket bucket名称
     * @param string $symlink symlink名称
     * @param string $targetObject 目标object名称
     * @param array $options
     * @return null
     */
    public function putSymlink($bucket, $symlink ,$targetObject, $options = NULL)
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
     * 获取symlink
     *@param string $bucket bucket名称
     * @param string $symlink symlink名称
     * @return null
     */
    public function getSymlink($bucket, $symlink)
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
     * 上传本地文件
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $file 本地文件路径
     * @param array $options
     * @return null
     * @throws OssException
     */
    public function uploadFile($bucket, $object, $file, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = OssUtil::encodePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[self::OSS_FILE_UPLOAD] = $file;
        $file_size = filesize($options[self::OSS_FILE_UPLOAD]);
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
     * 追加上传内存中的内容
     *
     * @param string $bucket bucket名称
     * @param string $object objcet名称
     * @param string $content 本次追加上传的内容
     * @param array $options
     * @return int next append position
     * @throws OssException
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
     * 追加上传本地文件
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $file 追加上传的本地文件路径
     * @param array $options
     * @return int next append position
     * @throws OssException
     */
    public function appendFile($bucket, $object, $file, $position, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);

        OssUtil::throwOssExceptionWithMessageIfEmpty($file, "file path is invalid");
        $file = OssUtil::encodePath($file);
        if (!file_exists($file)) {
            throw new OssException($file . " file does not exist");
        }
        $options[self::OSS_FILE_UPLOAD] = $file;
        $file_size = filesize($options[self::OSS_FILE_UPLOAD]);
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
     * 拷贝一个在OSS上已经存在的object成另外一个object
     *
     * @param string $fromBucket 源bucket名称
     * @param string $fromObject 源object名称
     * @param string $toBucket 目标bucket名称
     * @param string $toObject 目标object名称
     * @param array $options
     * @return null
     * @throws OssException
     */
    public function copyObject($fromBucket, $fromObject, $toBucket, $toObject, $options = NULL)
    {
        $this->precheckCommon($fromBucket, $fromObject, $options);
        $this->precheckCommon($toBucket, $toObject, $options);
        $options[self::OSS_BUCKET] = $toBucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_PUT;
        $options[self::OSS_OBJECT] = $toObject;
        if (isset($options[self::OSS_HEADERS])) {
            $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
        } else {
            $options[self::OSS_HEADERS] = array(self::OSS_OBJECT_COPY_SOURCE => '/' . $fromBucket . '/' . $fromObject);
        }
        $response = $this->auth($options);
        $result = new CopyObjectResult($response);
        return $result->getData();
    }

    /**
     * 获取Object的Meta信息
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
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
     * 删除某个Object
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param array $options
     * @return null
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
     * 删除同一个Bucket中的多个Object
     *
     * @param string $bucket bucket名称
     * @param array $objects object列表
     * @param array $options
     * @return ResponseCore
     * @throws null
     */
    public function deleteObjects($bucket, $objects, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        if (!is_array($objects) || !$objects) {
            throw new OssException('objects must be array');
        }
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = '/';
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
     * 获得Object内容
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param array $options 该参数中必须设置ALIOSS::OSS_FILE_DOWNLOAD，ALIOSS::OSS_RANGE可选，可以根据实际情况设置；如果不设置，默认会下载全部内容
     * @return string
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
     * 检测Object是否存在
     * 通过获取Object的Meta信息来判断Object是否存在， 用户需要自行解析ResponseCore判断object是否存在
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param array $options
     * @return bool
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
     * 针对Archive类型的Object读取
     * 需要使用Restore操作让服务端执行解冻任务
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @return null
     * @throws OssException
     */
    public function restoreObject($bucket, $object, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_SUB_RESOURCE] = self::OSS_RESTORE;
        $response = $this->auth($options);
        $result = new PutSetDeleteResult($response);
        return $result->getData();
    }

    /**
     * 获取分片大小，根据用户提供的part_size，重新计算一个更合理的partsize
     *
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
     * 计算文件可以分成多少个part，以及每个part的长度以及起始位置
     * 方法必须在 <upload_part()>中调用
     *
     * @param integer $file_size 文件大小
     * @param integer $partSize part大小,默认5M
     * @return array An array 包含 key-value 键值对. Key 为 `seekTo` 和 `length`.
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
     * 初始化multi-part upload
     *
     * @param string $bucket Bucket名称
     * @param string $object Object名称
     * @param array $options Key-Value数组
     * @throws OssException
     * @return string 返回uploadid
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
     * 分片上传的块上传接口
     *
     * @param string $bucket Bucket名称
     * @param string $object Object名称
     * @param string $uploadId
     * @param array $options Key-Value数组
     * @return string eTag
     * @throws OssException
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
     * 获取已成功上传的part
     *
     * @param string $bucket Bucket名称
     * @param string $object Object名称
     * @param string $uploadId uploadId
     * @param array $options Key-Value数组
     * @return ListPartsInfo
     * @throws OssException
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
     * 中止进行一半的分片上传操作
     *
     * @param string $bucket Bucket名称
     * @param string $object Object名称
     * @param string $uploadId uploadId
     * @param array $options Key-Value数组
     * @return null
     * @throws OssException
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
     * 在将所有数据Part都上传完成后，调用此接口完成本次分块上传
     *
     * @param string $bucket Bucket名称
     * @param string $object Object名称
     * @param string $uploadId uploadId
     * @param array $listParts array( array("PartNumber"=> int, "ETag"=>string))
     * @param array $options Key-Value数组
     * @throws OssException
     * @return null
     */
    public function completeMultipartUpload($bucket, $object, $uploadId, $listParts, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        $options[self::OSS_METHOD] = self::OSS_HTTP_POST;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_UPLOAD_ID] = $uploadId;
        $options[self::OSS_CONTENT_TYPE] = 'application/xml';
        if (!is_array($listParts)) {
            throw new OssException("listParts must be array type");
        }
        $options[self::OSS_CONTENT] = OssUtil::createCompleteMultipartUploadXmlBody($listParts);
        $response = $this->auth($options);
        if (isset($options[self::OSS_CALLBACK]) && !empty($options[self::OSS_CALLBACK])) {
            $result = new CallbackResult($response);
        } else {
            $result = new PutSetDeleteResult($response);
        }
        return $result->getData();
    }

    /**
     * 罗列出所有执行中的Multipart Upload事件，即已经被初始化的Multipart Upload但是未被
     * Complete或者Abort的Multipart Upload事件
     *
     * @param string $bucket bucket
     * @param array $options 关联数组
     * @throws OssException
     * @return ListMultipartUploadInfo
     */
    public function listMultipartUploads($bucket, $options = null)
    {
        $this->precheckCommon($bucket, NULL, $options, false);
        $options[self::OSS_METHOD] = self::OSS_HTTP_GET;
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = '/';
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
     * 从一个已存在的Object中拷贝数据来上传一个Part
     *
     * @param string $fromBucket 源bucket名称
     * @param string $fromObject 源object名称
     * @param string $toBucket 目标bucket名称
     * @param string $toObject 目标object名称
     * @param int $partNumber 分块上传的块id
     * @param string $uploadId 初始化multipart upload返回的uploadid
     * @param array $options Key-Value数组
     * @return null
     * @throws OssException
     */
    public function uploadPartCopy($fromBucket, $fromObject, $toBucket, $toObject, $partNumber, $uploadId, $options = NULL)
    {
        $this->precheckCommon($fromBucket, $fromObject, $options);
        $this->precheckCommon($toBucket, $toObject, $options);

        //如果没有设置$options['isFullCopy']，则需要强制判断copy的起止位置
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

        $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE] = '/' . $fromBucket . '/' . $fromObject;
        $options[self::OSS_HEADERS][self::OSS_OBJECT_COPY_SOURCE_RANGE] = "bytes=" . $start_range . "-" . $end_range;
        $response = $this->auth($options);
        $result = new UploadPartResult($response);
        return $result->getData();
    }

    /**
     * multipart上传统一封装，从初始化到完成multipart，以及出错后中止动作
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $file 需要上传的本地文件的路径
     * @param array $options Key-Value数组
     * @return null
     * @throws OssException
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
        $uploadFile = OssUtil::encodePath($file);
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = $this->getMimeType($object, $uploadFile);
        }

        $upload_position = isset($options[self::OSS_SEEK_TO]) ? (integer)$options[self::OSS_SEEK_TO] : 0;

        if (isset($options[self::OSS_CONTENT_LENGTH])) {
            $upload_file_size = (integer)$options[self::OSS_CONTENT_LENGTH];
        } else {
            $upload_file_size = filesize($uploadFile);
            if ($upload_file_size !== false) {
                $upload_file_size -= $upload_position;
            }
        }

        if ($upload_position === false || !isset($upload_file_size) || $upload_file_size === false || $upload_file_size < 0) {
            throw new OssException('The size of `fileUpload` cannot be determined in ' . __FUNCTION__ . '().');
        }
        // 处理partSize
        if (isset($options[self::OSS_PART_SIZE])) {
            $options[self::OSS_PART_SIZE] = $this->computePartSize($options[self::OSS_PART_SIZE]);
        } else {
            $options[self::OSS_PART_SIZE] = self::OSS_MID_PART_SIZE;
        }

        $is_check_md5 = $this->isCheckMD5($options);
        // 如果上传的文件小于partSize,则直接使用普通方式上传
        if ($upload_file_size < $options[self::OSS_PART_SIZE] && !isset($options[self::OSS_UPLOAD_ID])) {
            return $this->uploadFile($bucket, $object, $uploadFile, $options);
        }

        // 初始化multipart
        if (isset($options[self::OSS_UPLOAD_ID])) {
            $uploadId = $options[self::OSS_UPLOAD_ID];
        } else {
            // 初始化
            $uploadId = $this->initiateMultipartUpload($bucket, $object, $options);
        }

        // 获取的分片
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
        return $this->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
    }

    /**
     * 上传本地目录内的文件或者目录到指定bucket的指定prefix的object中
     *
     * @param string $bucket bucket名称
     * @param string $prefix 需要上传到的object的key前缀，可以理解成bucket中的子目录，结尾不能是'/'，接口中会补充'/'
     * @param string $localDirectory 需要上传的本地目录
     * @param string $exclude 需要排除的目录
     * @param bool $recursive 是否递归的上传localDirectory下的子目录内容
     * @param bool $checkMd5
     * @return array 返回两个列表 array("succeededList" => array("object"), "failedList" => array("object"=>"errorMessage"))
     * @throws OssException
     */
    public function uploadDir($bucket, $prefix, $localDirectory, $exclude = '.|..|.svn|.git', $recursive = false, $checkMd5 = true)
    {
        $retArray = array("succeededList" => array(), "failedList" => array());
        if (empty($bucket)) throw new OssException("parameter error, bucket is empty");
        if (!is_string($prefix)) throw new OssException("parameter error, prefix is not string");
        if (empty($localDirectory)) throw new OssException("parameter error, localDirectory is empty");
        $directory = $localDirectory;
        $directory = OssUtil::encodePath($directory);
        //判断是否目录
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
            $realObject = (!empty($prefix) ? $prefix . '/' : '') . $item['file'];

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
     * 支持生成get和put签名, 用户可以生成一个具有一定有效期的
     * 签名过的url
     *
     * @param string $bucket
     * @param string $object
     * @param int $timeout
     * @param string $method
     * @param array $options Key-Value数组
     * @return string
     * @throws OssException
     */
    public function signUrl($bucket, $object, $timeout = 60, $method = self::OSS_HTTP_GET, $options = NULL)
    {
        $this->precheckCommon($bucket, $object, $options);
        //method
        if (self::OSS_HTTP_GET !== $method && self::OSS_HTTP_PUT !== $method) {
            throw new OssException("method is invalid");
        }
        $options[self::OSS_BUCKET] = $bucket;
        $options[self::OSS_OBJECT] = $object;
        $options[self::OSS_METHOD] = $method;
        if (!isset($options[self::OSS_CONTENT_TYPE])) {
            $options[self::OSS_CONTENT_TYPE] = '';
        }
        $timeout = time() + $timeout;
        $options[self::OSS_PREAUTH] = $timeout;
        $options[self::OSS_DATE] = $timeout;
        $this->setSignStsInUrl(true);
        return $this->auth($options);
    }

    /**
     * 检测options参数
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
     * 校验bucket参数
     *
     * @param string $bucket
     * @param string $errMsg
     * @throws OssException
     */
    private function precheckBucket($bucket, $errMsg = 'bucket is not allowed empty')
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($bucket, $errMsg);
    }

    /**
     * 校验object参数
     *
     * @param string $object
     * @throws OssException
     */
    private function precheckObject($object)
    {
        OssUtil::throwOssExceptionWithMessageIfEmpty($object, "object name is empty");
    }

    /**
     * 校验option restore
     *
     * @param string $restore
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
                    default:
                        break;
            }
        }
        throw new OssException('storage name is invalid');
    }

    /**
     * 校验bucket,options参数
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
     * 参数校验
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
     * 检测md5
     *
     * @param array $options
     * @return bool|null
     */
    private function isCheckMD5($options)
    {
        return $this->getValue($options, self::OSS_CHECK_MD5, false, true, true);
    }

    /**
     * 获取value
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
     * 获取mimetype类型
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
     * 验证并且执行请求，按照OSS Api协议，执行操作
     *
     * @param array $options
     * @return ResponseCore
     * @throws OssException
     * @throws RequestCore_Exception
     */
    private function auth($options)
    {
        OssUtil::validateOptions($options);
        //验证bucket，list_bucket时不需要验证
        $this->authPrecheckBucket($options);
        //验证object
        $this->authPrecheckObject($options);
        //Object名称的编码必须是utf8
        $this->authPrecheckObjectEncoding($options);
        //验证ACL
        $this->authPrecheckAcl($options);
        // 获得当次请求使用的协议头，是https还是http
        $scheme = $this->useSSL ? 'https://' : 'http://';
        // 获得当次请求使用的hostname，如果是公共域名或者专有域名，bucket拼在前面构成三级域名
        $hostname = $this->generateHostname($options[self::OSS_BUCKET]);
        $string_to_sign = '';
        $headers = $this->generateHeaders($options, $hostname);
        $signable_query_string_params = $this->generateSignableQueryStringParam($options);
        $signable_query_string = OssUtil::toQueryString($signable_query_string_params);
        $resource_uri = $this->generateResourceUri($options);
        //生成请求URL
        $conjunction = '?';
        $non_signable_resource = '';
        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $conjunction = '&';
        }
        if ($signable_query_string !== '') {
            $signable_query_string = $conjunction . $signable_query_string;
            $conjunction = '&';
        }
        $query_string = $this->generateQueryString($options);
        if ($query_string !== '') {
            $non_signable_resource .= $conjunction . $query_string;
            $conjunction = '&';
        }
        $this->requestUrl = $scheme . $hostname . $resource_uri . $signable_query_string . $non_signable_resource;

        //创建请求
        $request = new RequestCore($this->requestUrl, $this->requestProxy);
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
            $string_to_sign .= $options[self::OSS_METHOD] . "\n";
        }

        if (isset($options[self::OSS_CONTENT])) {
            $request->set_body($options[self::OSS_CONTENT]);
            if ($headers[self::OSS_CONTENT_TYPE] === 'application/x-www-form-urlencoded') {
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

        uksort($headers, 'strnatcasecmp');

        foreach ($headers as $header_key => $header_value) {
            $header_value = str_replace(array("\r", "\n"), '', $header_value);
            if ($header_value !== '' || $header_key === self::OSS_ACCEPT_ENCODING) {
                $request->add_header($header_key, $header_value);
            }

            if (
                strtolower($header_key) === 'content-md5' ||
                strtolower($header_key) === 'content-type' ||
                strtolower($header_key) === 'date' ||
                (isset($options['self::OSS_PREAUTH']) && (integer)$options['self::OSS_PREAUTH'] > 0)
            ) {
                $string_to_sign .= $header_value . "\n";
            } elseif (substr(strtolower($header_key), 0, 6) === self::OSS_DEFAULT_PREFIX) {
                $string_to_sign .= strtolower($header_key) . ':' . $header_value . "\n";
            }
        }
        // 生成 signable_resource
        $signable_resource = $this->generateSignableResource($options);
        $string_to_sign .= rawurldecode($signable_resource) . urldecode($signable_query_string);

        //对?后面的要签名的string字母序排序
        $string_to_sign_ordered = $this->stringToSignSorted($string_to_sign);

        $signature = base64_encode(hash_hmac('sha1', $string_to_sign_ordered, $this->accessKeySecret, true));
        $request->add_header('Authorization', 'OSS ' . $this->accessKeyId . ':' . $signature);

        if (isset($options[self::OSS_PREAUTH]) && (integer)$options[self::OSS_PREAUTH] > 0) {
            $signed_url = $this->requestUrl . $conjunction . self::OSS_URL_ACCESS_KEY_ID . '=' . rawurlencode($this->accessKeyId) . '&' . self::OSS_URL_EXPIRES . '=' . $options[self::OSS_PREAUTH] . '&' . self::OSS_URL_SIGNATURE . '=' . rawurlencode($signature);
            return $signed_url;
        } elseif (isset($options[self::OSS_PREAUTH])) {
            return $this->requestUrl;
        }

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
        $response_header['oss-request-url'] = $this->requestUrl;
        $response_header['oss-redirects'] = $this->redirects;
        $response_header['oss-stringtosign'] = $string_to_sign;
        $response_header['oss-requestheaders'] = $request->request_headers;

        $data = new ResponseCore($response_header, $request->get_response_body(), $request->get_response_code());
        //retry if OSS Internal Error
        if ((integer)$request->get_response_code() === 500) {
            if ($this->redirects <= $this->maxRetries) {
                //设置休眠
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
     * 设置最大尝试次数
     *
     * @param int $maxRetries
     * @return void
     */
    public function setMaxTries($maxRetries = 3)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * 获取最大尝试次数
     *
     * @return int
     */
    public function getMaxRetries()
    {
        return $this->maxRetries;
    }

    /**
     * 打开sts enable标志，使用户构造函数中传入的$sts生效
     *
     * @param boolean $enable
     */
    public function setSignStsInUrl($enable)
    {
        $this->enableStsInUrl = $enable;
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
     * 检查bucket名称格式是否正确，如果非法抛出异常
     *
     * @param $options
     * @throws OssException
     */
    private function authPrecheckBucket($options)
    {
        if (!(('/' == $options[self::OSS_OBJECT]) && ('' == $options[self::OSS_BUCKET]) && ('GET' == $options[self::OSS_METHOD])) && !OssUtil::validateBucket($options[self::OSS_BUCKET])) {
            throw new OssException('"' . $options[self::OSS_BUCKET] . '"' . 'bucket name is invalid');
        }
    }

    /**
     *
     * 检查object名称格式是否正确，如果非法抛出异常
     *
     * @param $options
     * @throws OssException
     */
    private function authPrecheckObject($options)
    {
        if (isset($options[self::OSS_OBJECT]) && $options[self::OSS_OBJECT] === '/') {
            return;
        }

        if (isset($options[self::OSS_OBJECT]) && !OssUtil::validateObject($options[self::OSS_OBJECT])) {
            throw new OssException('"' . $options[self::OSS_OBJECT] . '"' . ' object name is invalid');
        }
    }

    /**
     * 检查object的编码，如果是gbk或者gb2312则尝试将其转化为utf8编码
     *
     * @param mixed $options 参数
     */
    private function authPrecheckObjectEncoding(&$options)
    {
        $tmp_object = $options[self::OSS_OBJECT];
        try {
            if (OssUtil::isGb2312($options[self::OSS_OBJECT])) {
                $options[self::OSS_OBJECT] = iconv('GB2312', "UTF-8//IGNORE", $options[self::OSS_OBJECT]);
            } elseif (OssUtil::checkChar($options[self::OSS_OBJECT], true)) {
                $options[self::OSS_OBJECT] = iconv('GBK', "UTF-8//IGNORE", $options[self::OSS_OBJECT]);
            }
        } catch (\Exception $e) {
            try {
                $tmp_object = iconv(mb_detect_encoding($tmp_object), "UTF-8", $tmp_object);
            } catch (\Exception $e) {
            }
        }
        $options[self::OSS_OBJECT] = $tmp_object;
    }

    /**
     * 检查ACL是否是预定义中三种之一，如果不是抛出异常
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
     * 获得档次请求使用的域名
     * bucket在前的三级域名，或者二级域名，如果是cname或者ip的话，则是二级域名
     *
     * @param $bucket
     * @return string 剥掉协议头的域名
     */
    private function generateHostname($bucket)
    {
        if ($this->hostType === self::OSS_HOST_TYPE_IP) {
            $hostname = $this->hostname;
        } elseif ($this->hostType === self::OSS_HOST_TYPE_CNAME) {
            $hostname = $this->hostname;
        } else {
            // 专有域或者官网endpoint
            $hostname = ($bucket == '') ? $this->hostname : ($bucket . '.') . $this->hostname;
        }
        return $hostname;
    }

    /**
     * 获得当次请求的资源定位字段
     *
     * @param $options
     * @return string 资源定位字段
     */
    private function generateResourceUri($options)
    {
        $resource_uri = "";

        // resource_uri + bucket
        if (isset($options[self::OSS_BUCKET]) && '' !== $options[self::OSS_BUCKET]) {
            if ($this->hostType === self::OSS_HOST_TYPE_IP) {
                $resource_uri = '/' . $options[self::OSS_BUCKET];
            }
        }

        // resource_uri + object
        if (isset($options[self::OSS_OBJECT]) && '/' !== $options[self::OSS_OBJECT]) {
            $resource_uri .= '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[self::OSS_OBJECT]));
        }

        // resource_uri + sub_resource
        $conjunction = '?';
        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $resource_uri .= $conjunction . $options[self::OSS_SUB_RESOURCE];
        }
        return $resource_uri;
    }

    /**
     * 生成signalbe_query_string_param, array类型
     *
     * @param array $options
     * @return array
     */
    private function generateSignableQueryStringParam($options)
    {
        $signableQueryStringParams = array();
        $signableList = array(
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
        );

        foreach ($signableList as $item) {
            if (isset($options[$item])) {
                $signableQueryStringParams[$item] = $options[$item];
            }
        }

        if ($this->enableStsInUrl && (!is_null($this->securityToken))) {
            $signableQueryStringParams["security-token"] = $this->securityToken;
        }

        return $signableQueryStringParams;
    }

    /**
     *  生成用于签名resource段
     *
     * @param mixed $options
     * @return string
     */
    private function generateSignableResource($options)
    {
        $signableResource = "";
        $signableResource .= '/';
        if (isset($options[self::OSS_BUCKET]) && '' !== $options[self::OSS_BUCKET]) {
            $signableResource .= $options[self::OSS_BUCKET];
            // 如果操作没有Object操作的话，这里最后是否有斜线有个trick，ip的域名下，不需要加'/'， 否则需要加'/'
            if ($options[self::OSS_OBJECT] == '/') {
                if ($this->hostType !== self::OSS_HOST_TYPE_IP) {
                    $signableResource .= "/";
                }
            }
        }
        //signable_resource + object
        if (isset($options[self::OSS_OBJECT]) && '/' !== $options[self::OSS_OBJECT]) {
            $signableResource .= '/' . str_replace(array('%2F', '%25'), array('/', '%'), rawurlencode($options[self::OSS_OBJECT]));
        }
        if (isset($options[self::OSS_SUB_RESOURCE])) {
            $signableResource .= '?' . $options[self::OSS_SUB_RESOURCE];
        }
        return $signableResource;
    }

    /**
     * 生成query_string
     *
     * @param mixed $options
     * @return string
     */
    private function generateQueryString($options)
    {
        //请求参数
        $queryStringParams = array();
        if (isset($options[self::OSS_QUERY_STRING])) {
            $queryStringParams = array_merge($queryStringParams, $options[self::OSS_QUERY_STRING]);
        }
        return OssUtil::toQueryString($queryStringParams);
    }

    private function stringToSignSorted($string_to_sign)
    {
        $queryStringSorted = '';
        $explodeResult = explode('?', $string_to_sign);
        $index = count($explodeResult);
        if ($index === 1)
            return $string_to_sign;

        $queryStringParams = explode('&', $explodeResult[$index - 1]);
        sort($queryStringParams);

        foreach($queryStringParams as $params)
        {
             $queryStringSorted .= $params . '&';    
        }

        $queryStringSorted = substr($queryStringSorted, 0, -1);

        return $explodeResult[0] . '?' . $queryStringSorted;
    }

    /**
     * 初始化headers
     *
     * @param mixed $options
     * @param string $hostname hostname
     * @return array
     */
    private function generateHeaders($options, $hostname)
    {
        $headers = array(
            self::OSS_CONTENT_MD5 => '',
            self::OSS_CONTENT_TYPE => isset($options[self::OSS_CONTENT_TYPE]) ? $options[self::OSS_CONTENT_TYPE] : self::DEFAULT_CONTENT_TYPE,
            self::OSS_DATE => isset($options[self::OSS_DATE]) ? $options[self::OSS_DATE] : gmdate('D, d M Y H:i:s \G\M\T'),
            self::OSS_HOST => $hostname,
        );
        if (isset($options[self::OSS_CONTENT_MD5])) {
            $headers[self::OSS_CONTENT_MD5] = $options[self::OSS_CONTENT_MD5];
        }

        //添加stsSecurityToken
        if ((!is_null($this->securityToken)) && (!$this->enableStsInUrl)) {
            $headers[self::OSS_SECURITY_TOKEN] = $this->securityToken;
        }
        //合并HTTP headers
        if (isset($options[self::OSS_HEADERS])) {
            $headers = array_merge($headers, $options[self::OSS_HEADERS]);
        }
        return $headers;
    }

    /**
     * 生成请求用的UserAgent
     *
     * @return string
     */
    private function generateUserAgent()
    {
        return self::OSS_NAME . "/" . self::OSS_VERSION . " (" . php_uname('s') . "/" . php_uname('r') . "/" . php_uname('m') . ";" . PHP_VERSION . ")";
    }

    /**
     * 检查endpoint的种类
     * 如有有协议头，剥去协议头
     * 并且根据参数 is_cname 和endpoint本身，判定域名类型，是ip，cname，还是专有域或者官网域名
     *
     * @param string $endpoint
     * @param boolean $isCName
     * @return string 剥掉协议头的域名
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
     * 用来检查sdk所以来的扩展是否打开
     *
     * @throws OssException
     */
    public static function checkEnv()
    {
        if (function_exists('get_loaded_extensions')) {
            //检测curl扩展
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
     //* 设置http库的请求超时时间，单位秒
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * 设置http库的连接超时时间，单位秒
     *
     * @param int $connectTimeout
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
    }

    // 生命周期相关常量
    const OSS_LIFECYCLE_EXPIRATION = "Expiration";
    const OSS_LIFECYCLE_TIMING_DAYS = "Days";
    const OSS_LIFECYCLE_TIMING_DATE = "Date";
    //OSS 内部常量
    const OSS_BUCKET = 'bucket';
    const OSS_OBJECT = 'object';
    const OSS_HEADERS = OssUtil::OSS_HEADERS;
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
    const OSS_PREAUTH = 'preauth';
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

    //私有URL变量
    const OSS_URL_ACCESS_KEY_ID = 'OSSAccessKeyId';
    const OSS_URL_EXPIRES = 'Expires';
    const OSS_URL_SIGNATURE = 'Signature';
    //HTTP方法
    const OSS_HTTP_GET = 'GET';
    const OSS_HTTP_PUT = 'PUT';
    const OSS_HTTP_HEAD = 'HEAD';
    const OSS_HTTP_POST = 'POST';
    const OSS_HTTP_DELETE = 'DELETE';
    const OSS_HTTP_OPTIONS = 'OPTIONS';
    //其他常量
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
    //支持STS SecurityToken
    const OSS_SECURITY_TOKEN = "x-oss-security-token";
    const OSS_ACL_TYPE_PRIVATE = 'private';
    const OSS_ACL_TYPE_PUBLIC_READ = 'public-read';
    const OSS_ACL_TYPE_PUBLIC_READ_WRITE = 'public-read-write';
    const OSS_ENCODING_TYPE = "encoding-type";
    const OSS_ENCODING_TYPE_URL = "url";

    // 域名类型
    const OSS_HOST_TYPE_NORMAL = "normal";//http://bucket.oss-cn-hangzhou.aliyuncs.com/object
    const OSS_HOST_TYPE_IP = "ip";  //http://1.1.1.1/bucket/object
    const OSS_HOST_TYPE_SPECIAL = 'special'; //http://bucket.guizhou.gov/object
    const OSS_HOST_TYPE_CNAME = "cname";  //http://mydomain.com/object
    //OSS ACL数组
    static $OSS_ACL_TYPES = array(
        self::OSS_ACL_TYPE_PRIVATE,
        self::OSS_ACL_TYPE_PUBLIC_READ,
        self::OSS_ACL_TYPE_PUBLIC_READ_WRITE
    );
    // OssClient版本信息
    const OSS_NAME = "aliyun-sdk-php";
    const OSS_VERSION = "2.3.0";
    const OSS_BUILD = "20180105";
    const OSS_AUTHOR = "";
    const OSS_OPTIONS_ORIGIN = 'Origin';
    const OSS_OPTIONS_REQUEST_METHOD = 'Access-Control-Request-Method';
    const OSS_OPTIONS_REQUEST_HEADERS = 'Access-Control-Request-Headers';

    //是否使用ssl
    private $useSSL = false;
    private $maxRetries = 3;
    private $redirects = 0;

    // 用户提供的域名类型，有四种 OSS_HOST_TYPE_NORMAL, OSS_HOST_TYPE_IP, OSS_HOST_TYPE_SPECIAL, OSS_HOST_TYPE_CNAME
    private $hostType = self::OSS_HOST_TYPE_NORMAL;
    private $requestUrl;
    private $accessKeyId;
    private $accessKeySecret;
    private $hostname;
    private $securityToken;
    private $requestProxy = null;
    private $enableStsInUrl = false;
    private $timeout = 0;
    private $connectTimeout = 0;
}
