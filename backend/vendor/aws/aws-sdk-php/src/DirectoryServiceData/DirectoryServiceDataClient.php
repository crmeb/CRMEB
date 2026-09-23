<?php
namespace Aws\DirectoryServiceData;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Directory Service Data** service.
 * @method \Aws\Result addGroupMember(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addGroupMemberAsync(array $args = [])
 * @method \Aws\Result createGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createGroupAsync(array $args = [])
 * @method \Aws\Result createUser(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createUserAsync(array $args = [])
 * @method \Aws\Result deleteGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteGroupAsync(array $args = [])
 * @method \Aws\Result deleteUser(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteUserAsync(array $args = [])
 * @method \Aws\Result describeGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeGroupAsync(array $args = [])
 * @method \Aws\Result describeUser(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeUserAsync(array $args = [])
 * @method \Aws\Result disableUser(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableUserAsync(array $args = [])
 * @method \Aws\Result listGroupMembers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listGroupMembersAsync(array $args = [])
 * @method \Aws\Result listGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listGroupsAsync(array $args = [])
 * @method \Aws\Result listGroupsForMember(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listGroupsForMemberAsync(array $args = [])
 * @method \Aws\Result listUsers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 * @method \Aws\Result removeGroupMember(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeGroupMemberAsync(array $args = [])
 * @method \Aws\Result searchGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchGroupsAsync(array $args = [])
 * @method \Aws\Result searchUsers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise searchUsersAsync(array $args = [])
 * @method \Aws\Result updateGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateGroupAsync(array $args = [])
 * @method \Aws\Result updateUser(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateUserAsync(array $args = [])
 */
class DirectoryServiceDataClient extends AwsClient {}
