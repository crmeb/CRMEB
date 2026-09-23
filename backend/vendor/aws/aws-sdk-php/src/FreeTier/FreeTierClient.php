<?php
namespace Aws\FreeTier;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Free Tier** service.
 * @method \Aws\Result getAccountActivity(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccountActivityAsync(array $args = [])
 * @method \Aws\Result getAccountPlanState(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccountPlanStateAsync(array $args = [])
 * @method \Aws\Result getFreeTierUsage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getFreeTierUsageAsync(array $args = [])
 * @method \Aws\Result listAccountActivities(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAccountActivitiesAsync(array $args = [])
 * @method \Aws\Result upgradeAccountPlan(array $args = [])
 * @method \GuzzleHttp\Promise\Promise upgradeAccountPlanAsync(array $args = [])
 */
class FreeTierClient extends AwsClient {}
