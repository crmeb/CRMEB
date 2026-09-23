<?php
namespace Aws\Repostspace;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS re:Post Private** service.
 * @method \Aws\Result batchAddChannelRoleToAccessors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchAddChannelRoleToAccessorsAsync(array $args = [])
 * @method \Aws\Result batchAddRole(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchAddRoleAsync(array $args = [])
 * @method \Aws\Result batchRemoveChannelRoleFromAccessors(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchRemoveChannelRoleFromAccessorsAsync(array $args = [])
 * @method \Aws\Result batchRemoveRole(array $args = [])
 * @method \GuzzleHttp\Promise\Promise batchRemoveRoleAsync(array $args = [])
 * @method \Aws\Result createChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createChannelAsync(array $args = [])
 * @method \Aws\Result createSpace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSpaceAsync(array $args = [])
 * @method \Aws\Result deleteSpace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSpaceAsync(array $args = [])
 * @method \Aws\Result deregisterAdmin(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterAdminAsync(array $args = [])
 * @method \Aws\Result getChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getChannelAsync(array $args = [])
 * @method \Aws\Result getSpace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSpaceAsync(array $args = [])
 * @method \Aws\Result listChannels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listChannelsAsync(array $args = [])
 * @method \Aws\Result listSpaces(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSpacesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result registerAdmin(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerAdminAsync(array $args = [])
 * @method \Aws\Result sendInvites(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sendInvitesAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateChannelAsync(array $args = [])
 * @method \Aws\Result updateSpace(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSpaceAsync(array $args = [])
 */
class RepostspaceClient extends AwsClient {}
