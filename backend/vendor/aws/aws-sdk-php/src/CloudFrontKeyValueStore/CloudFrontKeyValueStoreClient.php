<?php
namespace Aws\CloudFrontKeyValueStore;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudFront KeyValueStore** service.
 * @method \Aws\Result deleteKey(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteKeyAsync(array $args = [])
 * @method \Aws\Result describeKeyValueStore(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeKeyValueStoreAsync(array $args = [])
 * @method \Aws\Result getKey(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getKeyAsync(array $args = [])
 * @method \Aws\Result listKeys(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listKeysAsync(array $args = [])
 * @method \Aws\Result putKey(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putKeyAsync(array $args = [])
 * @method \Aws\Result updateKeys(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateKeysAsync(array $args = [])
 */
class CloudFrontKeyValueStoreClient extends AwsClient {}
