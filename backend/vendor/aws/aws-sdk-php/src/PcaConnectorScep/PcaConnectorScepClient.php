<?php
namespace Aws\PcaConnectorScep;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Private CA Connector for SCEP** service.
 * @method \Aws\Result createChallenge(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createChallengeAsync(array $args = [])
 * @method \Aws\Result createConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createConnectorAsync(array $args = [])
 * @method \Aws\Result deleteChallenge(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteChallengeAsync(array $args = [])
 * @method \Aws\Result deleteConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConnectorAsync(array $args = [])
 * @method \Aws\Result getChallengeMetadata(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getChallengeMetadataAsync(array $args = [])
 * @method \Aws\Result getChallengePassword(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getChallengePasswordAsync(array $args = [])
 * @method \Aws\Result getConnector(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConnectorAsync(array $args = [])
 * @method \Aws\Result listChallengeMetadata(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listChallengeMetadataAsync(array $args = [])
 * @method \Aws\Result listConnectors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listConnectorsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class PcaConnectorScepClient extends AwsClient {}
