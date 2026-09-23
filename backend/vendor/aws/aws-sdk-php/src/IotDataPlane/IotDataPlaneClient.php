<?php
namespace Aws\IotDataPlane;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Data Plane** service.
 *
 * @method \Aws\Result deleteConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConnectionAsync(array $args = [])
 * @method \Aws\Result deleteThingShadow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteThingShadowAsync(array $args = [])
 * @method \Aws\Result getConnection(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConnectionAsync(array $args = [])
 * @method \Aws\Result getRetainedMessage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRetainedMessageAsync(array $args = [])
 * @method \Aws\Result getThingShadow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getThingShadowAsync(array $args = [])
 * @method \Aws\Result listNamedShadowsForThing(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listNamedShadowsForThingAsync(array $args = [])
 * @method \Aws\Result listRetainedMessages(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRetainedMessagesAsync(array $args = [])
 * @method \Aws\Result listSubscriptions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSubscriptionsAsync(array $args = [])
 * @method \Aws\Result publish(array $args = [])
 * @method \GuzzleHttp\Promise\Promise publishAsync(array $args = [])
 * @method \Aws\Result sendDirectMessage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sendDirectMessageAsync(array $args = [])
 * @method \Aws\Result updateThingShadow(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateThingShadowAsync(array $args = [])
 */
class IotDataPlaneClient extends AwsClient {}
