<?php
namespace Aws\AgentRegistryControl;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Agent Registry Control** service.
 * @method \Aws\Result createRegistry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createRegistryAsync(array $args = [])
 * @method \Aws\Result createRegistryRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createRegistryRecordAsync(array $args = [])
 * @method \Aws\Result deleteRegistry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteRegistryAsync(array $args = [])
 * @method \Aws\Result deleteRegistryRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteRegistryRecordAsync(array $args = [])
 * @method \Aws\Result getRegistry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRegistryAsync(array $args = [])
 * @method \Aws\Result getRegistryRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRegistryRecordAsync(array $args = [])
 * @method \Aws\Result listRegistries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRegistriesAsync(array $args = [])
 * @method \Aws\Result listRegistryRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRegistryRecordsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result submitRegistryRecordForApproval(array $args = [])
 * @method \GuzzleHttp\Promise\Promise submitRegistryRecordForApprovalAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateRegistry(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRegistryAsync(array $args = [])
 * @method \Aws\Result updateRegistryRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRegistryRecordAsync(array $args = [])
 * @method \Aws\Result updateRegistryRecordStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRegistryRecordStatusAsync(array $args = [])
 */
class AgentRegistryControlClient extends AwsClient {}
