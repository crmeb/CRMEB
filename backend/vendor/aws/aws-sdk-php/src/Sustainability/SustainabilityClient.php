<?php
namespace Aws\Sustainability;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Sustainability** service.
 * @method \Aws\Result getEstimatedCarbonEmissions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEstimatedCarbonEmissionsAsync(array $args = [])
 * @method \Aws\Result getEstimatedCarbonEmissionsDimensionValues(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEstimatedCarbonEmissionsDimensionValuesAsync(array $args = [])
 * @method \Aws\Result getEstimatedWaterAllocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEstimatedWaterAllocationAsync(array $args = [])
 * @method \Aws\Result getEstimatedWaterAllocationDimensionValues(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEstimatedWaterAllocationDimensionValuesAsync(array $args = [])
 */
class SustainabilityClient extends AwsClient {}
