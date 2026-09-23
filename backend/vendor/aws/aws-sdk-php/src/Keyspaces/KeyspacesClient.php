<?php
namespace Aws\Keyspaces;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Keyspaces** service.
 * @method \Aws\Result createKeyspace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createKeyspaceAsync(array $args = [])
 * @method \Aws\Result createTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTableAsync(array $args = [])
 * @method \Aws\Result createType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createTypeAsync(array $args = [])
 * @method \Aws\Result deleteKeyspace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteKeyspaceAsync(array $args = [])
 * @method \Aws\Result deleteTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTableAsync(array $args = [])
 * @method \Aws\Result deleteType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteTypeAsync(array $args = [])
 * @method \Aws\Result getKeyspace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getKeyspaceAsync(array $args = [])
 * @method \Aws\Result getTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTableAsync(array $args = [])
 * @method \Aws\Result getTableAutoScalingSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTableAutoScalingSettingsAsync(array $args = [])
 * @method \Aws\Result getType(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTypeAsync(array $args = [])
 * @method \Aws\Result listKeyspaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listKeyspacesAsync(array $args = [])
 * @method \Aws\Result listTables(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTablesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listTypes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTypesAsync(array $args = [])
 * @method \Aws\Result restoreTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise restoreTableAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateKeyspace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateKeyspaceAsync(array $args = [])
 * @method \Aws\Result updateTable(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateTableAsync(array $args = [])
 */
class KeyspacesClient extends AwsClient {}
