<?php
namespace Aws\LambdaCore;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Lambda Core** service.
 * @method \Aws\Result createNetworkConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createNetworkConnectorAsync(array $args = [])
 * @method \Aws\Result deleteNetworkConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteNetworkConnectorAsync(array $args = [])
 * @method \Aws\Result getNetworkConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getNetworkConnectorAsync(array $args = [])
 * @method \Aws\Result listNetworkConnectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listNetworkConnectorsAsync(array $args = [])
 * @method \Aws\Result updateNetworkConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateNetworkConnectorAsync(array $args = [])
 */
class LambdaCoreClient extends AwsClient {}
