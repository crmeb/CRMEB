<?php
namespace Aws\CostOptimizationHub;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Cost Optimization Hub** service.
 * @method \Aws\Result getPreferences(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPreferencesAsync(array $args = [])
 * @method \Aws\Result getRecommendation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecommendationAsync(array $args = [])
 * @method \Aws\Result listEfficiencyMetrics(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEfficiencyMetricsAsync(array $args = [])
 * @method \Aws\Result listEnrollmentStatuses(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEnrollmentStatusesAsync(array $args = [])
 * @method \Aws\Result listRecommendationSummaries(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecommendationSummariesAsync(array $args = [])
 * @method \Aws\Result listRecommendations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \Aws\Result updateEnrollmentStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateEnrollmentStatusAsync(array $args = [])
 * @method \Aws\Result updatePreferences(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updatePreferencesAsync(array $args = [])
 */
class CostOptimizationHubClient extends AwsClient {}
