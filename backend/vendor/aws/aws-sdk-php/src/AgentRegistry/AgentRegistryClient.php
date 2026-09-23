<?php
namespace Aws\AgentRegistry;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Agent Registry** service.
 * @method \Aws\Result batchGetDiscoverableRegistryRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchGetDiscoverableRegistryRecordAsync(array $args = [])
 * @method \Aws\Result listDiscoverableRegistryRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDiscoverableRegistryRecordsAsync(array $args = [])
 * @method \Aws\Result searchDiscoverableRegistryRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchDiscoverableRegistryRecordsAsync(array $args = [])
 */
class AgentRegistryClient extends AwsClient {}
