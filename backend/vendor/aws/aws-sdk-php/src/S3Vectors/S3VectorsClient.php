<?php
namespace Aws\S3Vectors;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon S3 Vectors** service.
 * @method \Aws\Result createIndex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createIndexAsync(array $args = [])
 * @method \Aws\Result createVectorBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createVectorBucketAsync(array $args = [])
 * @method \Aws\Result deleteIndex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteIndexAsync(array $args = [])
 * @method \Aws\Result deleteVectorBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVectorBucketAsync(array $args = [])
 * @method \Aws\Result deleteVectorBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVectorBucketPolicyAsync(array $args = [])
 * @method \Aws\Result deleteVectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVectorsAsync(array $args = [])
 * @method \Aws\Result getIndex(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getIndexAsync(array $args = [])
 * @method \Aws\Result getVectorBucket(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getVectorBucketAsync(array $args = [])
 * @method \Aws\Result getVectorBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getVectorBucketPolicyAsync(array $args = [])
 * @method \Aws\Result getVectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getVectorsAsync(array $args = [])
 * @method \Aws\Result listIndexes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listIndexesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listVectorBuckets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listVectorBucketsAsync(array $args = [])
 * @method \Aws\Result listVectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listVectorsAsync(array $args = [])
 * @method \Aws\Result putVectorBucketPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putVectorBucketPolicyAsync(array $args = [])
 * @method \Aws\Result putVectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putVectorsAsync(array $args = [])
 * @method \Aws\Result queryVectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise queryVectorsAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class S3VectorsClient extends AwsClient {}
