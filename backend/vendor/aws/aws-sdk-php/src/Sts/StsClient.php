<?php
namespace Aws\Sts;

use Aws\Arn\ArnParser;
use Aws\AwsClient;
use Aws\CacheInterface;
use Aws\Credentials\Credentials;
use Aws\HandlerList;
use Aws\Middleware;
use Aws\Result;
use Aws\Retry\ConfigurationInterface as RetryConfigurationInterface;
use Aws\Retry\ConfigurationProvider as RetryConfigurationProvider;
use Aws\Retry\V3\OptIn as NewRetriesOptIn;
use Aws\Retry\V3\RetryMiddleware as RetryV3Middleware;
use Aws\RetryMiddleware;
use Aws\Sts\RegionalEndpoints\ConfigurationProvider;

/**
 * This client is used to interact with the **AWS Security Token Service (AWS STS)**.
 *
 * @method \Aws\Result assumeRole(array $args = [])
 * @method \GuzzleHttp\Promise\Promise assumeRoleAsync(array $args = [])
 * @method \Aws\Result assumeRoleWithSAML(array $args = [])
 * @method \GuzzleHttp\Promise\Promise assumeRoleWithSAMLAsync(array $args = [])
 * @method \Aws\Result assumeRoleWithWebIdentity(array $args = [])
 * @method \GuzzleHttp\Promise\Promise assumeRoleWithWebIdentityAsync(array $args = [])
 * @method \Aws\Result assumeRoot(array $args = [])
 * @method \GuzzleHttp\Promise\Promise assumeRootAsync(array $args = [])
 * @method \Aws\Result decodeAuthorizationMessage(array $args = [])
 * @method \GuzzleHttp\Promise\Promise decodeAuthorizationMessageAsync(array $args = [])
 * @method \Aws\Result getAccessKeyInfo(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getAccessKeyInfoAsync(array $args = [])
 * @method \Aws\Result getCallerIdentity(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getCallerIdentityAsync(array $args = [])
 * @method \Aws\Result getDelegatedAccessToken(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getDelegatedAccessTokenAsync(array $args = [])
 * @method \Aws\Result getFederationToken(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getFederationTokenAsync(array $args = [])
 * @method \Aws\Result getSessionToken(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSessionTokenAsync(array $args = [])
 * @method \Aws\Result getWebIdentityToken(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getWebIdentityTokenAsync(array $args = [])
 */
class StsClient extends AwsClient
{

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see \Aws\AwsClient::__construct}, StsClient accepts the following
     * options:
     *
     * - sts_regional_endpoints:
     *   (Aws\Sts\RegionalEndpoints\ConfigurationInterface|Aws\CacheInterface\|callable|string|array)
     *   Specifies whether to use regional or legacy endpoints for legacy regions.
     *   Provide an Aws\Sts\RegionalEndpoints\ConfigurationInterface object, an
     *   instance of Aws\CacheInterface, a callable configuration provider used
     *   to create endpoint configuration, a string value of `legacy` or
     *   `regional`, or an associative array with the following keys:
     *   endpoint_types (string)  Set to `legacy` or `regional`, defaults to
     *   `legacy`
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        if (
            !isset($args['sts_regional_endpoints'])
            || $args['sts_regional_endpoints'] instanceof CacheInterface
        ) {
            $args['sts_regional_endpoints'] = ConfigurationProvider::defaultProvider($args);
        }
        $this->addBuiltIns($args);
        parent::__construct($args);
    }

    public static function getArguments()
    {
        $args = parent::getArguments();
        // Off-path STS keeps the default ClientResolver retry handling. The
        // override below adds IDPCommunicationError as a transient error and
        // is only registered when the AWS_NEW_RETRIES_2026 flag is on.
        if (NewRetriesOptIn::isEnabled()) {
            $args['retries']['fn'] = [__CLASS__, '_applyRetryConfig'];
        }
        return $args;
    }

    /**
     * @internal Only invoked when AWS_NEW_RETRIES_2026=true. The off-path
     *           uses the default ClientResolver::_apply_retries.
     */
    public static function _applyRetryConfig(
        $value,
        array &$args,
        HandlerList $list
    ): void
    {
        if (!$value) {
            return;
        }

        $config = RetryConfigurationProvider::unwrap($value);

        if ($config->getMode() === 'legacy') {
            $decider = RetryMiddleware::createDefaultDecider($config->getMaxAttempts() - 1);
            $list->appendSign(
                Middleware::retry($decider, null, $args['stats']['retries']),
                'retry'
            );
            return;
        }

        $list->appendSign(
            RetryV3Middleware::wrap(
                $config,
                [
                    'collect_stats' => $args['stats']['retries'],
                    'service'       => $args['service'],
                    'transient_error_codes' => ['IDPCommunicationError'],
                ]
            ),
            'retry'
        );
    }

    /**
     * Creates credentials from the result of an STS operations
     *
     * @param Result $result Result of an STS operation
     *
     * @return Credentials
     * @throws \InvalidArgumentException if the result contains no credentials
     */
    public function createCredentials(Result $result, $source=null)
    {
        if (!$result->hasKey('Credentials')) {
            throw new \InvalidArgumentException('Result contains no credentials');
        }

        $accountId = null;
        if ($result->hasKey('AssumedRoleUser')) {
            $parsedArn = ArnParser::parse($result->get('AssumedRoleUser')['Arn']);
            $accountId = $parsedArn->getAccountId();
        } elseif ($result->hasKey('FederatedUser')) {
            $parsedArn = ArnParser::parse($result->get('FederatedUser')['Arn']);
            $accountId = $parsedArn->getAccountId();
        }

        $credentials = $result['Credentials'];
        $expiration = isset($credentials['Expiration']) && $credentials['Expiration'] instanceof \DateTimeInterface
            ? (int) $credentials['Expiration']->format('U')
            : null;

        return new Credentials(
            $credentials['AccessKeyId'],
            $credentials['SecretAccessKey'],
            isset($credentials['SessionToken']) ? $credentials['SessionToken'] : null,
            $expiration,
            $accountId,
            $source
        );
    }

    /**
     * Adds service-specific client built-in value
     *
     * @return void
     */
    private function addBuiltIns($args)
    {
        $key = 'AWS::STS::UseGlobalEndpoint';
        $result = $args['sts_regional_endpoints'] instanceof \Closure ?
            $args['sts_regional_endpoints']()->wait() : $args['sts_regional_endpoints'];

        if (is_string($result)) {
            if ($result === 'regional') {
                $value = false;
            } else if ($result === 'legacy') {
                $value = true;
            } else {
                return;
            }
        } else {
            if ($result->getEndpointsType() === 'regional') {
                $value = false;
            } else {
                $value = true;
            }
        }

        $this->clientBuiltIns[$key] = $value;
    }
}
