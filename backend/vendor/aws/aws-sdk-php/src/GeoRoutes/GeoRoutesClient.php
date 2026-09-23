<?php
namespace Aws\GeoRoutes;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Location Service Routes V2** service.
 * @method \Aws\Result calculateIsolines(array $args = [])
 * @method \GuzzleHttp\Promise\Promise calculateIsolinesAsync(array $args = [])
 * @method \Aws\Result calculateRouteMatrix(array $args = [])
 * @method \GuzzleHttp\Promise\Promise calculateRouteMatrixAsync(array $args = [])
 * @method \Aws\Result calculateRoutes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise calculateRoutesAsync(array $args = [])
 * @method \Aws\Result optimizeWaypoints(array $args = [])
 * @method \GuzzleHttp\Promise\Promise optimizeWaypointsAsync(array $args = [])
 * @method \Aws\Result snapToRoads(array $args = [])
 * @method \GuzzleHttp\Promise\Promise snapToRoadsAsync(array $args = [])
 */
class GeoRoutesClient extends AwsClient {}
