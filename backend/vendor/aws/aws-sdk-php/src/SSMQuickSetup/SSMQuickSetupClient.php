<?php
namespace Aws\SSMQuickSetup;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Systems Manager QuickSetup** service.
 * @method \Aws\Result createConfigurationManager(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createConfigurationManagerAsync(array $args = [])
 * @method \Aws\Result deleteConfigurationManager(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConfigurationManagerAsync(array $args = [])
 * @method \Aws\Result getConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConfigurationAsync(array $args = [])
 * @method \Aws\Result getConfigurationManager(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConfigurationManagerAsync(array $args = [])
 * @method \Aws\Result getServiceSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getServiceSettingsAsync(array $args = [])
 * @method \Aws\Result listConfigurationManagers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listConfigurationManagersAsync(array $args = [])
 * @method \Aws\Result listConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listConfigurationsAsync(array $args = [])
 * @method \Aws\Result listQuickSetupTypes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listQuickSetupTypesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateConfigurationDefinition(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateConfigurationDefinitionAsync(array $args = [])
 * @method \Aws\Result updateConfigurationManager(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateConfigurationManagerAsync(array $args = [])
 * @method \Aws\Result updateServiceSettings(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateServiceSettingsAsync(array $args = [])
 */
class SSMQuickSetupClient extends AwsClient {}
