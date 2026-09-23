<?php
namespace Aws\WorkspacesInstances;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Workspaces Instances** service.
 * @method \Aws\Result associateVolume(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateVolumeAsync(array $args = [])
 * @method \Aws\Result createVolume(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createVolumeAsync(array $args = [])
 * @method \Aws\Result createWorkspaceInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkspaceInstanceAsync(array $args = [])
 * @method \Aws\Result deleteVolume(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVolumeAsync(array $args = [])
 * @method \Aws\Result deleteWorkspaceInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkspaceInstanceAsync(array $args = [])
 * @method \Aws\Result disassociateVolume(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateVolumeAsync(array $args = [])
 * @method \Aws\Result getWorkspaceInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkspaceInstanceAsync(array $args = [])
 * @method \Aws\Result listInstanceTypes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInstanceTypesAsync(array $args = [])
 * @method \Aws\Result listRegions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRegionsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listWorkspaceInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkspaceInstancesAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class WorkspacesInstancesClient extends AwsClient {}
