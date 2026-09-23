<?php
namespace Aws\Signin;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Sign-In Service** service.
 * @method \Aws\Result createOAuth2Token(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createOAuth2TokenAsync(array $args = [])
 * @method \Aws\Result createOAuth2TokenWithIAM(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createOAuth2TokenWithIAMAsync(array $args = [])
 * @method \Aws\Result deleteConsoleAuthorizationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteConsoleAuthorizationConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteResourcePermissionStatement(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteResourcePermissionStatementAsync(array $args = [])
 * @method \Aws\Result getConsoleAuthorizationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getConsoleAuthorizationConfigurationAsync(array $args = [])
 * @method \Aws\Result getResourcePolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getResourcePolicyAsync(array $args = [])
 * @method \Aws\Result introspectOAuth2TokenWithIAM(array $args = [])
 * @method \GuzzleHttp\Promise\Promise introspectOAuth2TokenWithIAMAsync(array $args = [])
 * @method \Aws\Result listResourcePermissionStatements(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listResourcePermissionStatementsAsync(array $args = [])
 * @method \Aws\Result putConsoleAuthorizationConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putConsoleAuthorizationConfigurationAsync(array $args = [])
 * @method \Aws\Result putResourcePermissionStatement(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putResourcePermissionStatementAsync(array $args = [])
 * @method \Aws\Result revokeOAuth2TokenWithIAM(array $args = [])
 * @method \GuzzleHttp\Promise\Promise revokeOAuth2TokenWithIAMAsync(array $args = [])
 */
class SigninClient extends AwsClient {}
