<?php
namespace Aws\Interconnect;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Interconnect** service.
 * @method \Aws\Result acceptConnectionProposal(array $args = [])
 * @method \GuzzleHttp\Promise\Promise acceptConnectionProposalAsync(array $args = [])
 * @method \Aws\Result createConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createConnectionAsync(array $args = [])
 * @method \Aws\Result deleteConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConnectionAsync(array $args = [])
 * @method \Aws\Result describeConnectionProposal(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeConnectionProposalAsync(array $args = [])
 * @method \Aws\Result getConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConnectionAsync(array $args = [])
 * @method \Aws\Result getEnvironment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEnvironmentAsync(array $args = [])
 * @method \Aws\Result listAttachPoints(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAttachPointsAsync(array $args = [])
 * @method \Aws\Result listConnections(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listConnectionsAsync(array $args = [])
 * @method \Aws\Result listEnvironments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateConnectionAsync(array $args = [])
 */
class InterconnectClient extends AwsClient {}
