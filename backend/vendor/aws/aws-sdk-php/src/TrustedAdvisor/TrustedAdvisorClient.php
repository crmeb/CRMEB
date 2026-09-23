<?php
namespace Aws\TrustedAdvisor;

use Aws\AwsClient;

/**
 * This client is used to interact with the **TrustedAdvisor Public API** service.
 * @method \Aws\Result batchUpdateRecommendationResourceExclusion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchUpdateRecommendationResourceExclusionAsync(array $args = [])
 * @method \Aws\Result getOrganizationRecommendation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getOrganizationRecommendationAsync(array $args = [])
 * @method \Aws\Result getRecommendation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecommendationAsync(array $args = [])
 * @method \Aws\Result listChecks(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listChecksAsync(array $args = [])
 * @method \Aws\Result listOrganizationRecommendationAccounts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOrganizationRecommendationAccountsAsync(array $args = [])
 * @method \Aws\Result listOrganizationRecommendationResources(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOrganizationRecommendationResourcesAsync(array $args = [])
 * @method \Aws\Result listOrganizationRecommendations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listOrganizationRecommendationsAsync(array $args = [])
 * @method \Aws\Result listRecommendationResources(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecommendationResourcesAsync(array $args = [])
 * @method \Aws\Result listRecommendations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \Aws\Result listRecommendationsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecommendationsForResourceAsync(array $args = [])
 * @method \Aws\Result updateOrganizationRecommendationLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateOrganizationRecommendationLifecycleAsync(array $args = [])
 * @method \Aws\Result updateRecommendationLifecycle(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateRecommendationLifecycleAsync(array $args = [])
 */
class TrustedAdvisorClient extends AwsClient {}
