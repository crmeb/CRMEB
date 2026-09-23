<?php
namespace Aws\Billing;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Billing** service.
 * @method \Aws\Result associateSourceViews(array $args = [])
 * @method \GuzzleHttp\Promise\Promise associateSourceViewsAsync(array $args = [])
 * @method \Aws\Result createBillingView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBillingViewAsync(array $args = [])
 * @method \Aws\Result deleteBillingView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteBillingViewAsync(array $args = [])
 * @method \Aws\Result disassociateSourceViews(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disassociateSourceViewsAsync(array $args = [])
 * @method \Aws\Result getBillingPreferences(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBillingPreferencesAsync(array $args = [])
 * @method \Aws\Result getBillingView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getBillingViewAsync(array $args = [])
 * @method \Aws\Result getCreditAllocationHistory(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getCreditAllocationHistoryAsync(array $args = [])
 * @method \Aws\Result getCredits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getCreditsAsync(array $args = [])
 * @method \Aws\Result getEnterpriseSupportChargeSummary(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEnterpriseSupportChargeSummaryAsync(array $args = [])
 * @method \Aws\Result getEnterpriseSupportContractDetails(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEnterpriseSupportContractDetailsAsync(array $args = [])
 * @method \Aws\Result getResourcePolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \Aws\Result listBillingViews(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBillingViewsAsync(array $args = [])
 * @method \Aws\Result listEnterpriseSupportLinkedAccountCharges(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEnterpriseSupportLinkedAccountChargesAsync(array $args = [])
 * @method \Aws\Result listSourceViewsForBillingView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSourceViewsForBillingViewAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result redeemCredits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise redeemCreditsAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateBillingPreferences(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateBillingPreferencesAsync(array $args = [])
 * @method \Aws\Result updateBillingView(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateBillingViewAsync(array $args = [])
 */
class BillingClient extends AwsClient {}
