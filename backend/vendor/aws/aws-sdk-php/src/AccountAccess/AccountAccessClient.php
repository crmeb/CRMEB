<?php
namespace Aws\AccountAccess;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Account Access** service.
 * @method \Aws\Result createApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \Aws\Result createEntitlement(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEntitlementAsync(array $args = [])
 * @method \Aws\Result deleteApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \Aws\Result deleteEntitlement(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEntitlementAsync(array $args = [])
 * @method \Aws\Result getApplication(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @method \Aws\Result getEntitlement(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEntitlementAsync(array $args = [])
 * @method \Aws\Result listApplications(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \Aws\Result listEntitlements(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEntitlementsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class AccountAccessClient extends AwsClient {}
