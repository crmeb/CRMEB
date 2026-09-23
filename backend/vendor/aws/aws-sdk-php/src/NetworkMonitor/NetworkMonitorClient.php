<?php
namespace Aws\NetworkMonitor;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CloudWatch Network Monitor** service.
 * @method \Aws\Result createMonitor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createMonitorAsync(array $args = [])
 * @method \Aws\Result createProbe(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createProbeAsync(array $args = [])
 * @method \Aws\Result deleteMonitor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteMonitorAsync(array $args = [])
 * @method \Aws\Result deleteProbe(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteProbeAsync(array $args = [])
 * @method \Aws\Result getMonitor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getMonitorAsync(array $args = [])
 * @method \Aws\Result getProbe(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getProbeAsync(array $args = [])
 * @method \Aws\Result listMonitors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listMonitorsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateMonitor(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateMonitorAsync(array $args = [])
 * @method \Aws\Result updateProbe(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateProbeAsync(array $args = [])
 */
class NetworkMonitorClient extends AwsClient {}
