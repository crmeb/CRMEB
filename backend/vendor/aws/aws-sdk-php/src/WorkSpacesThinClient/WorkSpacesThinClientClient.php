<?php
namespace Aws\WorkSpacesThinClient;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon WorkSpaces Thin Client** service.
 * @method \Aws\Result createEnvironment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEnvironmentAsync(array $args = [])
 * @method \Aws\Result deleteDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDeviceAsync(array $args = [])
 * @method \Aws\Result deleteEnvironment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEnvironmentAsync(array $args = [])
 * @method \Aws\Result deregisterDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterDeviceAsync(array $args = [])
 * @method \Aws\Result getDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeviceAsync(array $args = [])
 * @method \Aws\Result getEnvironment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEnvironmentAsync(array $args = [])
 * @method \Aws\Result getSoftwareSet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSoftwareSetAsync(array $args = [])
 * @method \Aws\Result listDevices(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDevicesAsync(array $args = [])
 * @method \Aws\Result listEnvironments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEnvironmentsAsync(array $args = [])
 * @method \Aws\Result listSoftwareSets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSoftwareSetsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateDevice(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDeviceAsync(array $args = [])
 * @method \Aws\Result updateEnvironment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateEnvironmentAsync(array $args = [])
 * @method \Aws\Result updateSoftwareSet(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSoftwareSetAsync(array $args = [])
 */
class WorkSpacesThinClientClient extends AwsClient {}
