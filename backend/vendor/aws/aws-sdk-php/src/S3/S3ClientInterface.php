<?php
namespace Aws\S3;

use Aws\AwsClientInterface;
use Aws\CommandInterface;
use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * **Amazon Simple Storage Service** client.
 *
 * @method \Aws\Result abortMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise abortMultipartUploadAsync(array $args = [])
 * @method \Aws\Result completeMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise completeMultipartUploadAsync(array $args = [])
 * @method \Aws\Result copyObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise copyObjectAsync(array $args = [])
 * @method \Aws\Result createBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBucketAsync(array $args = [])
 * @method \Aws\Result createBucketMetadataConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBucketMetadataConfigurationAsync(array $args = [])
 * @method \Aws\Result createBucketMetadataTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBucketMetadataTableConfigurationAsync(array $args = [])
 * @method \Aws\Result createMultipartUpload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMultipartUploadAsync(array $args = [])
 * @method \Aws\Result createSession(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSessionAsync(array $args = [])
 * @method \Aws\Result deleteBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketAsync(array $args = [])
 * @method \Aws\Result deleteBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketCorsAsync(array $args = [])
 * @method \Aws\Result deleteBucketEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketEncryptionAsync(array $args = [])
 * @method \Aws\Result deleteBucketIntelligentTieringConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result deleteBucketMetadataConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketMetadataConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketMetadataTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketMetadataTableConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteBucketOwnershipControls(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketOwnershipControlsAsync(array $args = [])
 * @method \Aws\Result deleteBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketPolicyAsync(array $args = [])
 * @method \Aws\Result deleteBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketReplicationAsync(array $args = [])
 * @method \Aws\Result deleteBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketTaggingAsync(array $args = [])
 * @method \Aws\Result deleteBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result deleteObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectAsync(array $args = [])
 * @method \Aws\Result deleteObjectAnnotation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectAnnotationAsync(array $args = [])
 * @method \Aws\Result deleteObjectTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectTaggingAsync(array $args = [])
 * @method \Aws\Result deleteObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteObjectsAsync(array $args = [])
 * @method \Aws\Result deletePublicAccessBlock(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deletePublicAccessBlockAsync(array $args = [])
 * @method \Aws\Result getBucketAbac(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAbacAsync(array $args = [])
 * @method \Aws\Result getBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAccelerateConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAclAsync(array $args = [])
 * @method \Aws\Result getBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketCorsAsync(array $args = [])
 * @method \Aws\Result getBucketEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketEncryptionAsync(array $args = [])
 * @method \Aws\Result getBucketIntelligentTieringConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result getBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLifecycleConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLocationAsync(array $args = [])
 * @method \Aws\Result getBucketLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketLoggingAsync(array $args = [])
 * @method \Aws\Result getBucketMetadataConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketMetadataConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketMetadataTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketMetadataTableConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketNotification(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketNotificationAsync(array $args = [])
 * @method \Aws\Result getBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketNotificationConfigurationAsync(array $args = [])
 * @method \Aws\Result getBucketOwnershipControls(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketOwnershipControlsAsync(array $args = [])
 * @method \Aws\Result getBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketPolicyAsync(array $args = [])
 * @method \Aws\Result getBucketPolicyStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketPolicyStatusAsync(array $args = [])
 * @method \Aws\Result getBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketReplicationAsync(array $args = [])
 * @method \Aws\Result getBucketRequestPayment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketRequestPaymentAsync(array $args = [])
 * @method \Aws\Result getBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketTaggingAsync(array $args = [])
 * @method \Aws\Result getBucketVersioning(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketVersioningAsync(array $args = [])
 * @method \Aws\Result getBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result getObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAsync(array $args = [])
 * @method \Aws\Result getObjectAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAclAsync(array $args = [])
 * @method \Aws\Result getObjectAnnotation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAnnotationAsync(array $args = [])
 * @method \Aws\Result getObjectAttributes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectAttributesAsync(array $args = [])
 * @method \Aws\Result getObjectLegalHold(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectLegalHoldAsync(array $args = [])
 * @method \Aws\Result getObjectLockConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectLockConfigurationAsync(array $args = [])
 * @method \Aws\Result getObjectRetention(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectRetentionAsync(array $args = [])
 * @method \Aws\Result getObjectTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectTaggingAsync(array $args = [])
 * @method \Aws\Result getObjectTorrent(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getObjectTorrentAsync(array $args = [])
 * @method \Aws\Result getPublicAccessBlock(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPublicAccessBlockAsync(array $args = [])
 * @method \Aws\Result headBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise headBucketAsync(array $args = [])
 * @method \Aws\Result headObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise headObjectAsync(array $args = [])
 * @method \Aws\Result listBucketAnalyticsConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketAnalyticsConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBucketIntelligentTieringConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketIntelligentTieringConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBucketInventoryConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketInventoryConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBucketMetricsConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketMetricsConfigurationsAsync(array $args = [])
 * @method \Aws\Result listBuckets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBucketsAsync(array $args = [])
 * @method \Aws\Result listDirectoryBuckets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDirectoryBucketsAsync(array $args = [])
 * @method \Aws\Result listMultipartUploads(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listMultipartUploadsAsync(array $args = [])
 * @method \Aws\Result listObjectAnnotations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectAnnotationsAsync(array $args = [])
 * @method \Aws\Result listObjectVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectVersionsAsync(array $args = [])
 * @method \Aws\Result listObjects(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectsAsync(array $args = [])
 * @method \Aws\Result listObjectsV2(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listObjectsV2Async(array $args = [])
 * @method \Aws\Result listParts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPartsAsync(array $args = [])
 * @method \Aws\Result putBucketAbac(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAbacAsync(array $args = [])
 * @method \Aws\Result putBucketAccelerateConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAccelerateConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAclAsync(array $args = [])
 * @method \Aws\Result putBucketAnalyticsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketAnalyticsConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketCors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketCorsAsync(array $args = [])
 * @method \Aws\Result putBucketEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketEncryptionAsync(array $args = [])
 * @method \Aws\Result putBucketIntelligentTieringConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketIntelligentTieringConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketInventoryConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketInventoryConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLifecycleAsync(array $args = [])
 * @method \Aws\Result putBucketLifecycleConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLifecycleConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketLogging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketLoggingAsync(array $args = [])
 * @method \Aws\Result putBucketMetricsConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketMetricsConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketNotification(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketNotificationAsync(array $args = [])
 * @method \Aws\Result putBucketNotificationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketNotificationConfigurationAsync(array $args = [])
 * @method \Aws\Result putBucketOwnershipControls(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketOwnershipControlsAsync(array $args = [])
 * @method \Aws\Result putBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketPolicyAsync(array $args = [])
 * @method \Aws\Result putBucketReplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketReplicationAsync(array $args = [])
 * @method \Aws\Result putBucketRequestPayment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketRequestPaymentAsync(array $args = [])
 * @method \Aws\Result putBucketTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketTaggingAsync(array $args = [])
 * @method \Aws\Result putBucketVersioning(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketVersioningAsync(array $args = [])
 * @method \Aws\Result putBucketWebsite(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putBucketWebsiteAsync(array $args = [])
 * @method \Aws\Result putObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectAsync(array $args = [])
 * @method \Aws\Result putObjectAcl(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectAclAsync(array $args = [])
 * @method \Aws\Result putObjectAnnotation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectAnnotationAsync(array $args = [])
 * @method \Aws\Result putObjectLegalHold(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectLegalHoldAsync(array $args = [])
 * @method \Aws\Result putObjectLockConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectLockConfigurationAsync(array $args = [])
 * @method \Aws\Result putObjectRetention(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectRetentionAsync(array $args = [])
 * @method \Aws\Result putObjectTagging(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putObjectTaggingAsync(array $args = [])
 * @method \Aws\Result putPublicAccessBlock(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putPublicAccessBlockAsync(array $args = [])
 * @method \Aws\Result renameObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise renameObjectAsync(array $args = [])
 * @method \Aws\Result restoreObject(array $args = [])
 * @method \GuzzleHttp\Promise\Promise restoreObjectAsync(array $args = [])
 * @method \Aws\Result selectObjectContent(array $args = [])
 * @method \GuzzleHttp\Promise\Promise selectObjectContentAsync(array $args = [])
 * @method \Aws\Result updateBucketMetadataAnnotationTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateBucketMetadataAnnotationTableConfigurationAsync(array $args = [])
 * @method \Aws\Result updateBucketMetadataInventoryTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateBucketMetadataInventoryTableConfigurationAsync(array $args = [])
 * @method \Aws\Result updateBucketMetadataJournalTableConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateBucketMetadataJournalTableConfigurationAsync(array $args = [])
 * @method \Aws\Result updateObjectEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateObjectEncryptionAsync(array $args = [])
 * @method \Aws\Result uploadPart(array $args = [])
 * @method \GuzzleHttp\Promise\Promise uploadPartAsync(array $args = [])
 * @method \Aws\Result uploadPartCopy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise uploadPartCopyAsync(array $args = [])
 * @method \Aws\Result writeGetObjectResponse(array $args = [])
 * @method \GuzzleHttp\Promise\Promise writeGetObjectResponseAsync(array $args = [])
 */
interface S3ClientInterface extends AwsClientInterface
{
    /**
     * Create a pre-signed URL for the given S3 command object.
     *
     * @param CommandInterface              $command Command to create a pre-signed
     *                                               URL for.
     * @param int|string|\DateTimeInterface $expires The time at which the URL should
     *                                               expire. This can be a Unix
     *                                               timestamp, a PHP DateTime object,
     *                                               or a string that can be evaluated
     *                                               by strtotime().
     *
     * @return RequestInterface
     */
    public function createPresignedRequest(CommandInterface $command, $expires, array $options = []);

    /**
     * Returns the URL to an object identified by its bucket and key.
     *
     * The URL returned by this method is not signed nor does it ensure that the
     * bucket and key given to the method exist. If you need a signed URL, then
     * use the {@see \Aws\S3\S3Client::createPresignedRequest} method and get
     * the URI of the signed request.
     *
     * @param string $bucket  The name of the bucket where the object is located
     * @param string $key     The key of the object
     *
     * @return string The URL to the object
     */
    public function getObjectUrl($bucket, $key);

    /**
     * @deprecated Use doesBucketExistV2() instead
     *
     * Determines whether or not a bucket exists by name.
     *
     * @param string $bucket  The name of the bucket
     *
     * @return bool
     */
    public function doesBucketExist($bucket);

    /**
     * Determines whether or not a bucket exists by name. This method uses S3's
     * HeadBucket operation and requires the relevant bucket permissions in the
     * default case to prevent errors.
     *
     * @param string $bucket  The name of the bucket
     * @param bool $accept403 Set to true for this method to return true in the case of
     *                        invalid bucket-level permissions. Credentials MUST be valid
     *                        to avoid inaccuracies. Using the default value of false will
     *                        cause an exception to be thrown instead.
     *
     * @return bool
     * @throws S3Exception|\Exception if there is an unhandled exception
     */
    public function doesBucketExistV2($bucket, $accept403);

    /**
     * @deprecated Use doesObjectExistV2() instead
     *
     * Determines whether or not an object exists by name.
     *
     * @param string $bucket  The name of the bucket
     * @param string $key     The key of the object
     * @param array  $options Additional options available in the HeadObject
     *                        operation (e.g., VersionId).
     *
     * @return bool
     */
    public function doesObjectExist($bucket, $key, array $options = []);

    /**
     * Determines whether or not an object exists by name. This method uses S3's HeadObject
     * operation and requires the relevant bucket and object permissions to prevent errors.
     *
     * @param string $bucket The name of the bucket
     * @param string $key The key of the object
     * @param bool $includeDeleteMarkers Set to true to consider delete markers
     *                                   existing objects. Using the default value
     *                                   of false will ignore delete markers and
     *                                   return false.
     * @param array $options Additional options available in the HeadObject
     *                        operation (e.g., VersionId).
     *
     * @return bool
     * @throws S3Exception|\Exception if there is an unhandled exception
     */
    public function doesObjectExistV2($bucket, $key, $includeDeleteMarkers = false, array $options = []);

    /**
     * Register the Amazon S3 stream wrapper with this client instance.
     */
    public function registerStreamWrapper();

    /**
     * Registers the Amazon S3 stream wrapper with this client instance.
     *
     *This version uses doesObjectExistV2 and doesBucketExistV2 to check
     * resource existence.
     */
    public function registerStreamWrapperV2();

    /**
     * Deletes objects from Amazon S3 that match the result of a ListObjects
     * operation. For example, this allows you to do things like delete all
     * objects that match a specific key prefix.
     *
     * @param string $bucket  Bucket that contains the object keys
     * @param string $prefix  Optionally delete only objects under this key prefix
     * @param string $regex   Delete only objects that match this regex
     * @param array  $options Aws\S3\BatchDelete options array.
     *
     * @see Aws\S3\S3Client::listObjects
     * @throws \RuntimeException if no prefix and no regex is given
     */
    public function deleteMatchingObjects(
        $bucket,
        $prefix = '',
        $regex = '',
        array $options = []
    );

    /**
     * Deletes objects from Amazon S3 that match the result of a ListObjects
     * operation. For example, this allows you to do things like delete all
     * objects that match a specific key prefix.
     *
     * @param string $bucket  Bucket that contains the object keys
     * @param string $prefix  Optionally delete only objects under this key prefix
     * @param string $regex   Delete only objects that match this regex
     * @param array  $options Aws\S3\BatchDelete options array.
     *
     * @see Aws\S3\S3Client::listObjects
     *
     * @return PromiseInterface     A promise that is settled when matching
     *                              objects are deleted.
     */
    public function deleteMatchingObjectsAsync(
        $bucket,
        $prefix = '',
        $regex = '',
        array $options = []
    );

    /**
     * Upload a file, stream, or string to a bucket.
     *
     * If the upload size exceeds the specified threshold, the upload will be
     * performed using concurrent multipart uploads.
     *
     * The options array accepts the following options:
     *
     * - before_upload: (callable) Callback to invoke before any upload
     *   operations during the upload process. The callback should have a
     *   function signature like `function (Aws\Command $command) {...}`.
     * - concurrency: (int, default=int(3)) Maximum number of concurrent
     *   `UploadPart` operations allowed during a multipart upload.
     * - mup_threshold: (int, default=int(16777216)) The size, in bytes, allowed
     *   before the upload must be sent via a multipart upload. Default: 16 MB.
     * - params: (array, default=array([])) Custom parameters to use with the
     *   upload. For single uploads, they must correspond to those used for the
     *   `PutObject` operation. For multipart uploads, they correspond to the
     *   parameters of the `CreateMultipartUpload` operation.
     * - part_size: (int) Part size to use when doing a multipart upload.
     *
     * @param string $bucket  Bucket to upload the object.
     * @param string $key     Key of the object.
     * @param mixed  $body    Object data to upload. Can be a
     *                        StreamInterface, PHP stream resource, or a
     *                        string of data to upload.
     * @param string $acl     ACL to apply to the object (default: private).
     * @param array  $options Options used to configure the upload process.
     *
     * @see Aws\S3\MultipartUploader for more info about multipart uploads.
     * @return ResultInterface Returns the result of the upload.
     */
    public function upload(
        $bucket,
        $key,
        $body,
        $acl = 'private',
        array $options = []
    );

    /**
     * Upload a file, stream, or string to a bucket asynchronously.
     *
     * @param string $bucket  Bucket to upload the object.
     * @param string $key     Key of the object.
     * @param mixed  $body    Object data to upload. Can be a
     *                        StreamInterface, PHP stream resource, or a
     *                        string of data to upload.
     * @param string $acl     ACL to apply to the object (default: private).
     * @param array  $options Options used to configure the upload process.
     *
     * @see self::upload
     * @return PromiseInterface     Returns a promise that will be fulfilled
     *                              with the result of the upload.
     */
    public function uploadAsync(
        $bucket,
        $key,
        $body,
        $acl = 'private',
        array $options = []
    );

    /**
     * Copy an object of any size to a different location.
     *
     * If the upload size exceeds the maximum allowable size for direct S3
     * copying, a multipart copy will be used.
     *
     * The options array accepts the following options:
     *
     * - before_upload: (callable) Callback to invoke before any upload
     *   operations during the upload process. The callback should have a
     *   function signature like `function (Aws\Command $command) {...}`.
     * - concurrency: (int, default=int(5)) Maximum number of concurrent
     *   `UploadPart` operations allowed during a multipart upload.
     * - params: (array, default=array([])) Custom parameters to use with the
     *   upload. For single uploads, they must correspond to those used for the
     *   `CopyObject` operation. For multipart uploads, they correspond to the
     *   parameters of the `CreateMultipartUpload` operation.
     * - part_size: (int) Part size to use when doing a multipart upload.
     *
     * @param string $fromBucket    Bucket where the copy source resides.
     * @param string $fromKey       Key of the copy source.
     * @param string $destBucket    Bucket to which to copy the object.
     * @param string $destKey       Key to which to copy the object.
     * @param string $acl           ACL to apply to the copy (default: private).
     * @param array  $options       Options used to configure the upload process.
     *
     * @see Aws\S3\MultipartCopy for more info about multipart uploads.
     * @return ResultInterface Returns the result of the copy.
     */
    public function copy(
        $fromBucket,
        $fromKey,
        $destBucket,
        $destKey,
        $acl = 'private',
        array $options = []
    );

    /**
     * Copy an object of any size to a different location asynchronously.
     *
     * @param string $fromBucket    Bucket where the copy source resides.
     * @param string $fromKey       Key of the copy source.
     * @param string $destBucket    Bucket to which to copy the object.
     * @param string $destKey       Key to which to copy the object.
     * @param string $acl           ACL to apply to the copy (default: private).
     * @param array  $options       Options used to configure the upload process.
     *
     * @see self::copy for more info about the parameters above.
     * @return PromiseInterface     Returns a promise that will be fulfilled
     *                              with the result of the copy.
     */
    public function copyAsync(
        $fromBucket,
        $fromKey,
        $destBucket,
        $destKey,
        $acl = 'private',
        array $options = []
    );

    /**
     * Recursively uploads all files in a given directory to a given bucket.
     *
     * @param string $directory Full path to a directory to upload
     * @param string $bucket    Name of the bucket
     * @param string $keyPrefix Virtual directory key prefix to add to each upload
     * @param array  $options   Options available in Aws\S3\Transfer::__construct
     *
     * @see Aws\S3\Transfer for more options and customization
     */
    public function uploadDirectory(
        $directory,
        $bucket,
        $keyPrefix = null,
        array $options = []
    );

    /**
     * Recursively uploads all files in a given directory to a given bucket.
     *
     * @param string $directory Full path to a directory to upload
     * @param string $bucket    Name of the bucket
     * @param string $keyPrefix Virtual directory key prefix to add to each upload
     * @param array  $options   Options available in Aws\S3\Transfer::__construct
     *
     * @see Aws\S3\Transfer for more options and customization
     *
     * @return PromiseInterface A promise that is settled when the upload is
     *                          complete.
     */
    public function uploadDirectoryAsync(
        $directory,
        $bucket,
        $keyPrefix = null,
        array $options = []
    );

    /**
     * Downloads a bucket to the local filesystem
     *
     * @param string $directory Directory to download to
     * @param string $bucket    Bucket to download from
     * @param string $keyPrefix Only download objects that use this key prefix
     * @param array  $options   Options available in Aws\S3\Transfer::__construct
     */
    public function downloadBucket(
        $directory,
        $bucket,
        $keyPrefix = '',
        array $options = []
    );

    /**
     * Downloads a bucket to the local filesystem
     *
     * @param string $directory Directory to download to
     * @param string $bucket    Bucket to download from
     * @param string $keyPrefix Only download objects that use this key prefix
     * @param array  $options   Options available in Aws\S3\Transfer::__construct
     *
     * @return PromiseInterface A promise that is settled when the download is
     *                          complete.
     */
    public function downloadBucketAsync(
        $directory,
        $bucket,
        $keyPrefix = '',
        array $options = []
    );

    /**
     * Returns the region in which a given bucket is located.
     *
     * @param string $bucketName
     *
     * @return string
     */
    public function determineBucketRegion($bucketName);

    /**
     * Returns a promise fulfilled with the region in which a given bucket is
     * located.
     *
     * @param string $bucketName
     *
     * @return PromiseInterface
     */
    public function determineBucketRegionAsync($bucketName);
}
