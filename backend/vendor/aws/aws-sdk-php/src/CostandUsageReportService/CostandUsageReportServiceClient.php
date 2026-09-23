<?php
namespace Aws\CostandUsageReportService;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Cost and Usage Report Service** service.
 * @method \Aws\Result deleteReportDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteReportDefinitionAsync(array $args = [])
 * @method \Aws\Result describeReportDefinitions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeReportDefinitionsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result modifyReportDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise modifyReportDefinitionAsync(array $args = [])
 * @method \Aws\Result putReportDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putReportDefinitionAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class CostandUsageReportServiceClient extends AwsClient {}
