<?php
namespace Aws\PricingPlanManager;

use Aws\AwsClient;

/**
 * This client is used to interact with the **PricingPlanManager** service.
 * @method \Aws\Result approvePaidSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise approvePaidSubscriptionAsync(array $args = [])
 * @method \Aws\Result associateResourcesToSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateResourcesToSubscriptionAsync(array $args = [])
 * @method \Aws\Result cancelSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelSubscriptionAsync(array $args = [])
 * @method \Aws\Result cancelSubscriptionChange(array $args = [])
 * @method \GuzzleHttp\Promise\Promise cancelSubscriptionChangeAsync(array $args = [])
 * @method \Aws\Result createSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSubscriptionAsync(array $args = [])
 * @method \Aws\Result disassociateResourcesFromSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateResourcesFromSubscriptionAsync(array $args = [])
 * @method \Aws\Result getSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSubscriptionAsync(array $args = [])
 * @method \Aws\Result listSubscriptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSubscriptionsAsync(array $args = [])
 * @method \Aws\Result updateSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSubscriptionAsync(array $args = [])
 */
class PricingPlanManagerClient extends AwsClient {}
