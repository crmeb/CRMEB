<?php
namespace Aws\MarketplaceDeployment;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Marketplace Deployment Service** service.
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putDeploymentParameter(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putDeploymentParameterAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class MarketplaceDeploymentClient extends AwsClient {}
