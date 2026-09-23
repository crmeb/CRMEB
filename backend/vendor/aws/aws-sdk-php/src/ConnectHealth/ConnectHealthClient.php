<?php
namespace Aws\ConnectHealth;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Connect Health** service.
 * @method \Aws\Result activateSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise activateSubscriptionAsync(array $args = [])
 * @method \Aws\Result createDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDomainAsync(array $args = [])
 * @method \Aws\Result createSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSubscriptionAsync(array $args = [])
 * @method \Aws\Result deactivateSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deactivateSubscriptionAsync(array $args = [])
 * @method \Aws\Result deleteDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDomainAsync(array $args = [])
 * @method \Aws\Result getDomain(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDomainAsync(array $args = [])
 * @method \Aws\Result getMedicalScribeListeningSession(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getMedicalScribeListeningSessionAsync(array $args = [])
 * @method \Aws\Result getPatientInsightsJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPatientInsightsJobAsync(array $args = [])
 * @method \Aws\Result getSubscription(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSubscriptionAsync(array $args = [])
 * @method \Aws\Result listDomains(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDomainsAsync(array $args = [])
 * @method \Aws\Result listSubscriptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSubscriptionsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result startPatientInsightsJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startPatientInsightsJobAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class ConnectHealthClient extends AwsClient {}
