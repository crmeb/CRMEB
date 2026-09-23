<?php
namespace Aws\KeyspacesStreams;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Keyspaces Streams** service.
 * @method \Aws\Result getRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \Aws\Result getShardIterator(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \Aws\Result getStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getStreamAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class KeyspacesStreamsClient extends AwsClient {}
