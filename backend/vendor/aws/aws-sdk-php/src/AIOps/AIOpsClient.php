<?php
namespace Aws\AIOps;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS AI Ops** service.
 * @method \Aws\Result createInvestigationGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createInvestigationGroupAsync(array $args = [])
 * @method \Aws\Result deleteInvestigationGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInvestigationGroupAsync(array $args = [])
 * @method \Aws\Result deleteInvestigationGroupPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteInvestigationGroupPolicyAsync(array $args = [])
 * @method \Aws\Result getInvestigationGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getInvestigationGroupAsync(array $args = [])
 * @method \Aws\Result getInvestigationGroupPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getInvestigationGroupPolicyAsync(array $args = [])
 * @method \Aws\Result listInvestigationGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listInvestigationGroupsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putInvestigationGroupPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putInvestigationGroupPolicyAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateInvestigationGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateInvestigationGroupAsync(array $args = [])
 */
class AIOpsClient extends AwsClient {}
