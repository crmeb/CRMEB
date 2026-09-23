<?php
namespace Aws\DSQL;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Aurora DSQL** service.
 * @method \Aws\Result createCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \Aws\Result createStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createStreamAsync(array $args = [])
 * @method \Aws\Result deleteCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \Aws\Result deleteClusterPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteClusterPolicyAsync(array $args = [])
 * @method \Aws\Result deleteStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteStreamAsync(array $args = [])
 * @method \Aws\Result getCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getClusterAsync(array $args = [])
 * @method \Aws\Result getClusterPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getClusterPolicyAsync(array $args = [])
 * @method \Aws\Result getStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getStreamAsync(array $args = [])
 * @method \Aws\Result getVpcEndpointServiceName(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getVpcEndpointServiceNameAsync(array $args = [])
 * @method \Aws\Result listClusters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putClusterPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putClusterPolicyAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateClusterAsync(array $args = [])
 */
class DSQLClient extends AwsClient {}
