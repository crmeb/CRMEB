<?php
namespace Aws\NotificationsContacts;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS User Notifications Contacts** service.
 * @method \Aws\Result activateEmailContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise activateEmailContactAsync(array $args = [])
 * @method \Aws\Result createEmailContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEmailContactAsync(array $args = [])
 * @method \Aws\Result deleteEmailContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEmailContactAsync(array $args = [])
 * @method \Aws\Result getEmailContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getEmailContactAsync(array $args = [])
 * @method \Aws\Result listEmailContacts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEmailContactsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result sendActivationCode(array $args = [])
 * @method \GuzzleHttp\Promise\Promise sendActivationCodeAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 */
class NotificationsContactsClient extends AwsClient {}
