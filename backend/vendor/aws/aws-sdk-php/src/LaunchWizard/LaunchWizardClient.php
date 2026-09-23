<?php
namespace Aws\LaunchWizard;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Launch Wizard** service.
 * @method \Aws\Result createDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDeploymentAsync(array $args = [])
 * @method \Aws\Result deleteDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDeploymentAsync(array $args = [])
 * @method \Aws\Result getDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentAsync(array $args = [])
 * @method \Aws\Result getDeploymentPatternVersion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDeploymentPatternVersionAsync(array $args = [])
 * @method \Aws\Result getWorkload(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkloadAsync(array $args = [])
 * @method \Aws\Result getWorkloadDeploymentPattern(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWorkloadDeploymentPatternAsync(array $args = [])
 * @method \Aws\Result listDeploymentEvents(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentEventsAsync(array $args = [])
 * @method \Aws\Result listDeploymentPatternVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentPatternVersionsAsync(array $args = [])
 * @method \Aws\Result listDeployments(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDeploymentsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listWorkloadDeploymentPatterns(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkloadDeploymentPatternsAsync(array $args = [])
 * @method \Aws\Result listWorkloads(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listWorkloadsAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateDeployment(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateDeploymentAsync(array $args = [])
 */
class LaunchWizardClient extends AwsClient {}
