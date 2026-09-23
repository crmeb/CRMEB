<?php
namespace Aws\SagemakerJobRuntime;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Sagemaker Job Runtime Service** service.
 * @method \Aws\Result completeRollout(array $args = [])
 * @method \GuzzleHttp\Promise\Promise completeRolloutAsync(array $args = [])
 * @method \Aws\Result sample(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sampleAsync(array $args = [])
 * @method \Aws\Result sampleWithResponseStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sampleWithResponseStreamAsync(array $args = [])
 * @method \Aws\Result updateReward(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRewardAsync(array $args = [])
 */
class SagemakerJobRuntimeClient extends AwsClient {}
