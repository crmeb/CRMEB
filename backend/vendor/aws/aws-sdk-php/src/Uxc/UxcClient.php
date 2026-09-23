<?php
namespace Aws\Uxc;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS User Experience Customization** service.
 * @method \Aws\Result getAccountCustomizations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccountCustomizationsAsync(array $args = [])
 * @method \Aws\Result listServices(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listServicesAsync(array $args = [])
 * @method \Aws\Result updateAccountCustomizations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateAccountCustomizationsAsync(array $args = [])
 */
class UxcClient extends AwsClient {}
