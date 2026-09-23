<?php
namespace Aws\PCS;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Parallel Computing Service** service.
 * @method \Aws\Result createCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createClusterAsync(array $args = [])
 * @method \Aws\Result createComputeNodeGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createComputeNodeGroupAsync(array $args = [])
 * @method \Aws\Result createQueue(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createQueueAsync(array $args = [])
 * @method \Aws\Result deleteCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteClusterAsync(array $args = [])
 * @method \Aws\Result deleteComputeNodeGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteComputeNodeGroupAsync(array $args = [])
 * @method \Aws\Result deleteQueue(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteQueueAsync(array $args = [])
 * @method \Aws\Result getCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getClusterAsync(array $args = [])
 * @method \Aws\Result getComputeNodeGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getComputeNodeGroupAsync(array $args = [])
 * @method \Aws\Result getQueue(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getQueueAsync(array $args = [])
 * @method \Aws\Result listClusters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listClustersAsync(array $args = [])
 * @method \Aws\Result listComputeNodeGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listComputeNodeGroupsAsync(array $args = [])
 * @method \Aws\Result listQueues(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listQueuesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result registerComputeNodeGroupInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerComputeNodeGroupInstanceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateCluster(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateClusterAsync(array $args = [])
 * @method \Aws\Result updateComputeNodeGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateComputeNodeGroupAsync(array $args = [])
 * @method \Aws\Result updateQueue(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateQueueAsync(array $args = [])
 */
class PCSClient extends AwsClient {}
