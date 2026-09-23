<?php
namespace Aws\LicenseManagerLinuxSubscriptions;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS License Manager Linux Subscriptions** service.
 * @method \Aws\Result deregisterSubscriptionProvider(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterSubscriptionProviderAsync(array $args = [])
 * @method \Aws\Result getRegisteredSubscriptionProvider(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRegisteredSubscriptionProviderAsync(array $args = [])
 * @method \Aws\Result getServiceSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getServiceSettingsAsync(array $args = [])
 * @method \Aws\Result listLinuxSubscriptionInstances(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLinuxSubscriptionInstancesAsync(array $args = [])
 * @method \Aws\Result listLinuxSubscriptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listLinuxSubscriptionsAsync(array $args = [])
 * @method \Aws\Result listRegisteredSubscriptionProviders(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRegisteredSubscriptionProvidersAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result registerSubscriptionProvider(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerSubscriptionProviderAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateServiceSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateServiceSettingsAsync(array $args = [])
 */
class LicenseManagerLinuxSubscriptionsClient extends AwsClient {}
