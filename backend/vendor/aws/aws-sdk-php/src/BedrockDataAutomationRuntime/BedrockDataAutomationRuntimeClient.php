<?php
namespace Aws\BedrockDataAutomationRuntime;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Runtime for Amazon Bedrock Data Automation** service.
 * @method \Aws\Result getDataAutomationStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDataAutomationStatusAsync(array $args = [])
 * @method \Aws\Result invokeDataAutomation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeDataAutomationAsync(array $args = [])
 * @method \Aws\Result invokeDataAutomationAsync(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeDataAutomationAsyncAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class BedrockDataAutomationRuntimeClient extends AwsClient {}
