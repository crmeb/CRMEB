<?php
namespace Aws\Account;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Account** service.
 * @method \Aws\Result acceptPrimaryEmailUpdate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise acceptPrimaryEmailUpdateAsync(array $args = [])
 * @method \Aws\Result deleteAlternateContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteAlternateContactAsync(array $args = [])
 * @method \Aws\Result disableRegion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableRegionAsync(array $args = [])
 * @method \Aws\Result enableRegion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableRegionAsync(array $args = [])
 * @method \Aws\Result getAccountInformation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccountInformationAsync(array $args = [])
 * @method \Aws\Result getAlternateContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAlternateContactAsync(array $args = [])
 * @method \Aws\Result getContactInformation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getContactInformationAsync(array $args = [])
 * @method \Aws\Result getGovCloudAccountInformation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getGovCloudAccountInformationAsync(array $args = [])
 * @method \Aws\Result getPrimaryEmail(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPrimaryEmailAsync(array $args = [])
 * @method \Aws\Result getPrimaryEmailUpdateStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPrimaryEmailUpdateStatusAsync(array $args = [])
 * @method \Aws\Result getRegionOptStatus(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRegionOptStatusAsync(array $args = [])
 * @method \Aws\Result listRegions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRegionsAsync(array $args = [])
 * @method \Aws\Result putAccountName(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putAccountNameAsync(array $args = [])
 * @method \Aws\Result putAlternateContact(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putAlternateContactAsync(array $args = [])
 * @method \Aws\Result putContactInformation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putContactInformationAsync(array $args = [])
 * @method \Aws\Result startPrimaryEmailUpdate(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startPrimaryEmailUpdateAsync(array $args = [])
 */
class AccountClient extends AwsClient {}
