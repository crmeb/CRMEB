<?php
namespace Aws\BCMDataExports;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Billing and Cost Management Data Exports** service.
 * @method \Aws\Result createExport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createExportAsync(array $args = [])
 * @method \Aws\Result deleteExport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteExportAsync(array $args = [])
 * @method \Aws\Result getExecution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getExecutionAsync(array $args = [])
 * @method \Aws\Result getExport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getExportAsync(array $args = [])
 * @method \Aws\Result getTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTableAsync(array $args = [])
 * @method \Aws\Result listExecutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listExecutionsAsync(array $args = [])
 * @method \Aws\Result listExports(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listExportsAsync(array $args = [])
 * @method \Aws\Result listTables(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateExport(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateExportAsync(array $args = [])
 */
class BCMDataExportsClient extends AwsClient {}
