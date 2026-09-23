<?php

namespace Aws\Token;

use Aws\Configuration\ConfigurationResolver;
use Aws\Exception\TokenException;
use GuzzleHttp\Promise;

/**
 * Token provider for Bedrock that sources bearer tokens from environment variables.
 */
class BedrockTokenProvider extends TokenProvider
{
    /** @var string used to resolve the AWS_BEARER_TOKEN_BEDROCK env var */
    public const TOKEN_ENV_KEY = 'bearer_token_bedrock';
    public const BEARER_AUTH = 'smithy.api#httpBearerAuth';

    /**
     * Create a default Bedrock token provider that checks for a bearer token
     * in the AWS_BEARER_TOKEN_BEDROCK environment variable.
     *
     * This provider is automatically wrapped in a memoize function that caches
     * previously provided tokens.
     *
     * @param array $config Optional array of token provider options.
     *
     * @return callable
     */
    public static function defaultProvider(array $config = []): callable
    {
        $defaultChain = ['env' => self::env(self::TOKEN_ENV_KEY)];

        return self::memoize(
            call_user_func_array(
                [TokenProvider::class, 'chain'],
                array_values($defaultChain)
            )
        );
    }

    /**
     * Token provider that creates a token from an environment variable.
     *
     * @param string $configKey The configuration key that will be transformed
     *                          to an environment variable name by ConfigurationResolver
     *
     * @return callable
     */
    public static function env(string $configKey): callable
    {
        return static function () use ($configKey) {
            $tokenValue = ConfigurationResolver::env($configKey);
            if (empty($tokenValue)) {
                return Promise\Create::rejectionFor(
                    new TokenException(
                        "No token found in environment variable " .
                        ConfigurationResolver::$envPrefix . strtoupper($configKey)
                    )
                );
            }

            return Promise\Create::promiseFor(new Token($tokenValue));
        };
    }

    /**
     * Create a token provider from a raw token value string.
     * Bedrock bearer tokens sourced from env do not have an expiration
     *
     * @param string $tokenValue The bearer token value
     *
     * @return callable
     */
    public static function fromTokenValue(
        string $tokenValue,
        ?TokenSource $source = null
    ): callable
    {
        $token = new Token($tokenValue, null, $source);
        return self::fromToken($token);
    }

    /**
     * Create a Bedrock token provider if the service is 'bedrock' and a token is available.
     * Sets auth scheme preference to `bearer` auth.
     *
     * @param array $args Configuration arguments containing 'config' array
     *
     * @return callable|null Returns a token provider if conditions are met, null otherwise
     */
    public static function createIfAvailable(array &$args): ?callable
    {
        $tokenValue = ConfigurationResolver::env(self::TOKEN_ENV_KEY);

        if ($tokenValue) {
            $authSchemePreference = $args['config']['auth_scheme_preference'] ?? [];
            array_unshift($authSchemePreference, self::BEARER_AUTH);
            $args['config']['auth_scheme_preference'] = $authSchemePreference;

            return self::fromTokenValue($tokenValue, TokenSource::BEARER_SERVICE_ENV_VARS);
        }

        return null;
    }
}
