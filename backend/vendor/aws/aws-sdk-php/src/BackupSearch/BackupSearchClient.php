<?php
namespace Aws\BackupSearch;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Backup Search** service.
 * @method \Aws\Result getSearchJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSearchJobAsync(array $args = [])
 * @method \Aws\Result getSearchResultExportJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSearchResultExportJobAsync(array $args = [])
 * @method \Aws\Result listSearchJobBackups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSearchJobBackupsAsync(array $args = [])
 * @method \Aws\Result listSearchJobResults(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSearchJobResultsAsync(array $args = [])
 * @method \Aws\Result listSearchJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSearchJobsAsync(array $args = [])
 * @method \Aws\Result listSearchResultExportJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSearchResultExportJobsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result startSearchJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startSearchJobAsync(array $args = [])
 * @method \Aws\Result startSearchResultExportJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startSearchResultExportJobAsync(array $args = [])
 * @method \Aws\Result stopSearchJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopSearchJobAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class BackupSearchClient extends AwsClient {}
