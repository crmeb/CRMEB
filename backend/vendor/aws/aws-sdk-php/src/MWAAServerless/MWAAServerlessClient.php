<?php
namespace Aws\MWAAServerless;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AmazonMWAAServerless** service.
 * @method \Aws\Result createWorkflow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createWorkflowAsync(array $args = [])
 * @method \Aws\Result deleteWorkflow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteWorkflowAsync(array $args = [])
 * @method \Aws\Result getTaskInstance(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getTaskInstanceAsync(array $args = [])
 * @method \Aws\Result getWorkflow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkflowAsync(array $args = [])
 * @method \Aws\Result getWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkflowRunAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listTaskInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTaskInstancesAsync(array $args = [])
 * @method \Aws\Result listWorkflowRuns(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkflowRunsAsync(array $args = [])
 * @method \Aws\Result listWorkflowVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkflowVersionsAsync(array $args = [])
 * @method \Aws\Result listWorkflows(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkflowsAsync(array $args = [])
 * @method \Aws\Result startWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startWorkflowRunAsync(array $args = [])
 * @method \Aws\Result stopWorkflowRun(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopWorkflowRunAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateWorkflow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateWorkflowAsync(array $args = [])
 */
class MWAAServerlessClient extends AwsClient {}
