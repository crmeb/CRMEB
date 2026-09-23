<?php
namespace Aws\SupportAuthZ;

use Aws\AwsClient;

/**
 * This client is used to interact with the **SupportAuthZ** service.
 * @method \Aws\Result createSupportPermit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSupportPermitAsync(array $args = [])
 * @method \Aws\Result deleteSupportPermit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSupportPermitAsync(array $args = [])
 * @method \Aws\Result getAction(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getActionAsync(array $args = [])
 * @method \Aws\Result getSupportPermit(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSupportPermitAsync(array $args = [])
 * @method \Aws\Result listActions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listActionsAsync(array $args = [])
 * @method \Aws\Result listSupportPermitRequests(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSupportPermitRequestsAsync(array $args = [])
 * @method \Aws\Result listSupportPermits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSupportPermitsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result rejectSupportPermitRequest(array $args = [])
 * @method \GuzzleHttp\Promise\Promise rejectSupportPermitRequestAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class SupportAuthZClient extends AwsClient {}
