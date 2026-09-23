<?php
namespace Aws\BedrockRuntime;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Bedrock Runtime** service.
 * @method \Aws\Result applyGuardrail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise applyGuardrailAsync(array $args = [])
 * @method \Aws\Result converse(array $args = [])
 * @method \GuzzleHttp\Promise\Promise converseAsync(array $args = [])
 * @method \Aws\Result converseStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise converseStreamAsync(array $args = [])
 * @method \Aws\Result countTokens(array $args = [])
 * @method \GuzzleHttp\Promise\Promise countTokensAsync(array $args = [])
 * @method \Aws\Result getAsyncInvoke(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAsyncInvokeAsync(array $args = [])
 * @method \Aws\Result invokeGuardrailChecks(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeGuardrailChecksAsync(array $args = [])
 * @method \Aws\Result invokeModel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeModelAsync(array $args = [])
 * @method \Aws\Result invokeModelWithResponseStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise invokeModelWithResponseStreamAsync(array $args = [])
 * @method \Aws\Result listAsyncInvokes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAsyncInvokesAsync(array $args = [])
 * @method \Aws\Result startAsyncInvoke(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startAsyncInvokeAsync(array $args = [])
 */
class BedrockRuntimeClient extends AwsClient {}
