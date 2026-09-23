<?php
namespace Aws\Credentials;

use Aws\Configuration\ConfigurationResolver;
use Aws\Exception\CredentialsException;
use Aws\Signin\SigninClient;
use Aws\Signin\Exception\SigninException;
use GuzzleHttp\Promise;

/**
 * Credential provider for login using console credentials
 */
final class LoginCredentialProvider
{
    private const EXT_OPENSSL = 'openssl';
    private const DEFAULT_REFRESH_THRESHOLD = 180; // 3 minutes
    private const ENV_CACHE_DIRECTORY = 'AWS_LOGIN_CACHE_DIRECTORY';
    private const DEFAULT_CACHE_DIRECTORY = '/.aws/login/cache';
    private const REAUTHENTICATE_MSG = ' Please reauthenticate using `aws login`.';
    private const PROFILE_DEFAULT = 'default';
    private const PROFILE_SECONDARY = 'profile ';
    private const GRANT_TYPE = 'refresh_token';
    private const REQUEST_KEY_GRANT_TYPE = 'grantType';
    private const REQUEST_KEY_CLIENT_ID = 'clientId';
    private const REQUEST_KEY_REFRESH_TOKEN = 'refreshToken';
    private const REQUEST_KEY_TOKEN_INPUT = 'tokenInput';
    private const RESULT_TOKEN_OUTPUT = 'tokenOutput';
    private const RESULT_EXPIRES_IN = 'expiresIn';
    private const CLIENT_SIGNATURE_DPOP = 'dpop';
    private const KEY_CLIENT_SIGNATURE_VERSION = 'signature_version';
    private const KEY_CLIENT_REGION = 'region';
    private const KEY_CLIENT_CREDENTIALS = 'credentials';
    private const KEY_PROFILE_LOGIN_SESSION = 'login_session';
    private const KEY_ACCESS_TOKEN = 'accessToken';
    private const KEY_DPOP_KEY = 'dpopKey';
    private const KEY_ACCESS_KEY_ID = 'accessKeyId';
    private const KEY_SECRET_ACCESS_KEY = 'secretAccessKey';
    private const KEY_SESSION_TOKEN = 'sessionToken';
    private const KEY_ACCOUNT_ID = 'accountId';
    private const KEY_EXPIRES_AT = 'expiresAt';
    private const REQUIRED_CACHE_KEYS = [
        self::KEY_ACCESS_TOKEN,
        self::REQUEST_KEY_CLIENT_ID,
        self::REQUEST_KEY_REFRESH_TOKEN,
        self::KEY_DPOP_KEY
    ];
    private const REQUIRED_ACCESS_TOKEN_KEYS = [
        self::KEY_ACCESS_KEY_ID,
        self::KEY_SECRET_ACCESS_KEY,
        self::KEY_SESSION_TOKEN,
        self::KEY_ACCOUNT_ID,
        self::KEY_EXPIRES_AT
    ];

    /** @var string The profile name used for login session configuration */
    private string $profileName;
    
    /** @var SigninClient The Signin service client used for token refresh operations */
    private SigninClient $client;
    
    /** @var string The file path to the cached token location */
    private string $tokenLocation;
    
    /** @var array|null The cached token data including access token, refresh token, and DPoP key */
    private ?array $token = null;

    /**
     * @param string $profileName Profile containing your console login session information
     * @param string|null $region Region used for refresh requests. If not provided,
     *                            attempts will be made to resolve a region using
     *                            `AWS_REGION`, then the profile specified for `login`.
     */
    public function __construct(
        string $profileName,
        ?string $region = null
    ) {
        if (!extension_loaded(self::EXT_OPENSSL)) {
            throw new \RuntimeException(
                'The `openssl` extension is required to use Login credentials. '
                . 'Please install or enable the `openssl` extension.'
            );
        }

        $this->profileName = $profileName;
        $this->client = $this->createSigninClient($profileName, $region);
        $this->tokenLocation = $this->resolveTokenLocation($profileName);
    }

    /**
     * Returns a promise that resolves to AWS credentials
     * 
     * This method loads the cached token, refreshes it if necessary,
     * and returns AWS credentials sourced from the access token.
     *
     * @return Promise\PromiseInterface A promise that resolves to a Credentials object
     * @throws CredentialsException If re-authentication is required or credentials cannot be loaded
     * @throws SigninException If the token refresh fails with a SigninException
     */
    public function __invoke(): Promise\PromiseInterface
    {
        return Promise\Coroutine::of(function () {
            $this->token ??= $this->loadToken();
            $credentials = $this->token[self::KEY_ACCESS_TOKEN];
            
            if ($this->shouldRefresh($credentials)) {
                try {
                    $credentials = yield from $this->refresh($credentials);
                } catch (CredentialsException $e) {
                    // For specific re-authentication errors, re-throw
                    throw $e;
                } catch (SigninException $e) {
                    // For SigninException not handled by refresh(), re-throw
                    throw new CredentialsException(
                        'Unable to refresh login credentials: ' . $e->getAwsErrorMessage(),
                        $e->getCode(),
                        $e
                    );
                } catch (\Exception $e) {
                    // For other refresh failures, log and continue with existing token
                    trigger_error(
                        'Continuing with existing token after refresh failure: ' . $e->getMessage(),
                        E_USER_NOTICE
                    );
                }
            }

            yield $credentials;
        });
    }

    /**
     * Refreshes the access token using the refresh token and returns new credentials,
     * or, if refreshed from another source, returns the externally refreshed credentials.
     *
     * @param Credentials $currentCredentials The current credentials to refresh
     *
     * @return \Generator Generator that yields and returns refreshed credentials
     * @throws CredentialsException If re-authentication is required due to expired or changed credentials
     * @throws SigninException If the token refresh fails with a SigninException
     * @throws \Exception For unexpected errors during refresh
     */
    private function refresh(Credentials $currentCredentials): \Generator
    {
        // Check for external refresh
        if ($refreshedToken = $this->getExternalRefresh($currentCredentials)) {
            $this->token = $refreshedToken;
            
            return $refreshedToken[self::KEY_ACCESS_TOKEN];
        }

        try {
            $refreshed = (yield $this->client->createOAuth2TokenAsync([
                self::REQUEST_KEY_TOKEN_INPUT => [
                    self::REQUEST_KEY_CLIENT_ID => $this->token[self::REQUEST_KEY_CLIENT_ID],
                    self::REQUEST_KEY_GRANT_TYPE => self::GRANT_TYPE,
                    self::REQUEST_KEY_REFRESH_TOKEN => $this->token[self::REQUEST_KEY_REFRESH_TOKEN]
                ],
                self::KEY_DPOP_KEY => $this->token[self::KEY_DPOP_KEY]
            ]))->get(self::RESULT_TOKEN_OUTPUT);

            $newCredentials = self::createCredentials(
                $refreshed[self::KEY_ACCESS_TOKEN],
                time() + $refreshed[self::RESULT_EXPIRES_IN],
                $currentCredentials->getAccountId()
            );

            $this->token[self::KEY_ACCESS_TOKEN] = $newCredentials;
            $this->token[self::REQUEST_KEY_REFRESH_TOKEN] = $refreshed[self::REQUEST_KEY_REFRESH_TOKEN];
        } catch (\Exception $e) {
            throw $this->handleRefreshException($e);
        }

        try {
            $this->writeToCache();
        } catch (\JsonException|\RuntimeException $e) {
            trigger_error(
                'Failed to update credential cache during refresh: ' . $e->getMessage()
                . '. Using refreshed credentials in memory.',
                E_USER_NOTICE
            );
        }
        
        return $newCredentials;
    }

    /**
     * Gets externally refreshed token if token has been refreshed from another source.
     * If the new token does not need refreshing, returns it.
     * 
     * @param Credentials $currentCredentials Current credentials to compare against
     * @return array|null Returns the updated token array if externally refreshed, null otherwise
     */
    private function getExternalRefresh(Credentials $currentCredentials): ?array
    {
        try {
            $latestToken = $this->loadToken();
            $latestCredentials = $latestToken[self::KEY_ACCESS_TOKEN];
            
            // Refresh token must be different
            if ($latestToken[self::REQUEST_KEY_REFRESH_TOKEN]
                === $this->token[self::REQUEST_KEY_REFRESH_TOKEN]
            ) {
                return null;
            }
            
            // Expiration must be newer
            if ($latestCredentials->getExpiration()
                <= $currentCredentials->getExpiration()
            ) {
                return null;
            }
            
            // New token should not need refresh itself
            if ($this->shouldRefresh($latestCredentials)) {
                return null;
            }

            return $latestToken;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Writes the updated access token and refresh token to the cache file
     *
     * @throws \JsonException|\RuntimeException
     */
    private function writeToCache(): void
    {
        $credentials = $this->token[self::KEY_ACCESS_TOKEN];
        $updates = [
            self::KEY_ACCESS_TOKEN => [
                self::KEY_ACCESS_KEY_ID => $credentials->getAccessKeyId(),
                self::KEY_SECRET_ACCESS_KEY => $credentials->getSecretKey(),
                self::KEY_SESSION_TOKEN => $credentials->getSecurityToken(),
                self::KEY_ACCOUNT_ID => $credentials->getAccountId(),
                self::KEY_EXPIRES_AT => gmdate('Y-m-d\TH:i:s\Z', $credentials->getExpiration())
            ],
            self::REQUEST_KEY_REFRESH_TOKEN => $this->token[self::REQUEST_KEY_REFRESH_TOKEN]
        ];
        
        $existing = json_decode(
            file_get_contents($this->tokenLocation), true, 512, JSON_THROW_ON_ERROR
        );
        $merged = array_merge($existing, $updates);

        $result = file_put_contents(
            $this->tokenLocation,
            json_encode($merged, JSON_THROW_ON_ERROR),
            LOCK_EX
        );

        if (!$result) {
            throw new \RuntimeException('Failed to write cache file');
        }
    }

    /**
     * Loads and validates the token from the cache file
     *
     * @return array The loaded token data with Credentials object, DPoP key, and other fields
     * @throws CredentialsException If the cache file is invalid, missing required keys,
     *                              or DPoP key cannot be loaded
     */
    private function loadToken(): array
    {
        try {
            $cached = json_decode(
                file_get_contents($this->tokenLocation),
                true, 512, JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new CredentialsException(
                'Invalid JSON in cache file: ' . $e->getMessage(),
                0,
                $e
            );
        }

        if (self::hasAllRequiredKeys($cached, self::REQUIRED_CACHE_KEYS)
            && self::hasAllRequiredKeys(
                $cached[self::KEY_ACCESS_TOKEN] ?? [],
                self::REQUIRED_ACCESS_TOKEN_KEYS
            )
        ) {
            // Convert expiresAt to Unix timestamp
            $expiresAt = strtotime($cached[self::KEY_ACCESS_TOKEN][self::KEY_EXPIRES_AT]);
            if ($expiresAt === false) {
                throw new CredentialsException(
                    'Invalid expiration date format in cached token `'
                    . self::KEY_EXPIRES_AT . '` field.' . self::REAUTHENTICATE_MSG
                );
            }

            $cached[self::KEY_ACCESS_TOKEN] = self::createCredentials(
                $cached[self::KEY_ACCESS_TOKEN],
                $expiresAt,
                $cached[self::KEY_ACCESS_TOKEN][self::KEY_ACCOUNT_ID]
            );
            
            // Load DPoP key
            $cached[self::KEY_DPOP_KEY] = openssl_pkey_get_private($cached[self::KEY_DPOP_KEY]);
            if ($cached[self::KEY_DPOP_KEY] === false) {
                $error = openssl_error_string();
                throw new CredentialsException(
                    'Failed to load DPoP private key from cached token for profile '
                    . "{$this->profileName}: " . ($error ? ": {$error}" : '.')
                    . self::REAUTHENTICATE_MSG
                );
            }

            return $cached;
        }

        throw new CredentialsException(
            'Missing required keys in cached token for profile '
            . $this->profileName . '.' . self::REAUTHENTICATE_MSG
        );
    }

    /**
     * @param Credentials $credentials The credentials to check for expiration
     *
     * @return bool True if the token expires within the refresh threshold
     */
    private function shouldRefresh(Credentials $credentials): bool
    {
        return ($credentials->getExpiration() - time()) <= self::DEFAULT_REFRESH_THRESHOLD;
    }

    /**
     * Handles exceptions thrown during token refresh
     *
     * @param \Exception $e The exception thrown during refresh
     * 
     * @return \Exception The exception to be thrown (either transformed or original)
     */
    private function handleRefreshException(\Exception $e): \Exception
    {
        if ($e instanceof SigninException) {
            trigger_error(
                'Failed to refresh login credentials: ' . $e->getAwsErrorMessage(),
                E_USER_NOTICE
            );
            
            if ($e->getAwsErrorCode() === 'AccessDeniedException') {
                $error = strtolower($e->get('error'));
                switch ($error) {
                    case 'token_expired':
                        return new CredentialsException(
                            'Your session has expired.' . self::REAUTHENTICATE_MSG,
                            0,
                            $e
                        );
                    case 'user_credentials_changed':
                        return new CredentialsException(
                            'Unable to refresh credentials because of a change in your password. '
                            . 'Please reauthenticate with your new password.',
                            0,
                            $e
                        );
                    case 'insufficient_permissions':
                        return new CredentialsException(
                            'Unable to refresh credentials due to insufficient permissions. '
                            . 'You may be missing permission for the `CreateOAuth2Token` action.',
                            0,
                            $e
                        );
                }
            }
            
            return $e;
        }
        
        // For all other exceptions
        trigger_error(
            'Unexpected error refreshing login credentials: ' . $e->getMessage(),
            E_USER_NOTICE
        );
        
        return $e;
    }

    /**
     * Resolves the cache file location for the given profile
     *
     * @param string $profileName The profile name to resolve the token location for
     *
     * @return string The full path to the cache file
     * @throws CredentialsException If the profile doesn't exist, lacks login_session,
     *                              or cache file is not readable
     */
    private function resolveTokenLocation(string $profileName): string
    {
        $configFile = CredentialProvider::getConfigFileName();
        if (!is_readable($configFile)) {
            throw new CredentialsException(
                'Unable to load configuration file at ' . $configFile
                . '. Please ensure the file exists at the specified location.'
            );
        }

        // Ensure profile and session are set
        $profiles = CredentialProvider::loadProfiles($configFile);
        if ($profileName === self::PROFILE_DEFAULT) {
            $profileData = $profiles[self::PROFILE_DEFAULT] ?? null;
        } elseif (str_starts_with($profileName, self::PROFILE_SECONDARY)) {
            $profileData = $profiles[$profileName] ?? null;
        } else {
            // Try without prefix first, then with prefix
            $profileData = $profiles[$profileName] 
                        ?? $profiles[self::PROFILE_SECONDARY . $profileName]
                        ?? null;
        }
        
        if (!$profileData) {
            throw new CredentialsException(
                "Profile '{$profileName}' does not exist. "
                . "Please ensure the specified profile is set at {$configFile}."
            );
        }
        
        if (empty($session = $profileData[self::KEY_PROFILE_LOGIN_SESSION] ?? null)) {
            throw new CredentialsException(
                "Profile '{$profileName}' did not contain a "
                . self::KEY_PROFILE_LOGIN_SESSION . " value. "
                . 're-authentication using `aws login` may be needed.'
            );
        }

        // Resolve location and ensure it exists
        $cacheDirectory = getenv(self::ENV_CACHE_DIRECTORY)
            ?: CredentialProvider::getHomeDir() . self::DEFAULT_CACHE_DIRECTORY;
        $cacheFile = $cacheDirectory . DIRECTORY_SEPARATOR . hash('sha256', trim($session)) . '.json';
        if (!@is_readable($cacheFile)) {
            throw new CredentialsException(
                "Failed to load cached credentials for profile "
                . "'{$profileName}'." . self::REAUTHENTICATE_MSG
            );
        }

        return $cacheFile;
    }

    /**
     * Creates a SigninClient configured for DPoP authentication
     *
     * @param string $profile
     * @param string|null $region The AWS region for the Signin service
     *
     * @return SigninClient A configured SigninClient instance with DPoP signature version
     */
    private function createSigninClient(string $profile, ?string $region): SigninClient
    {
        $resolvedRegion = $region
            ?? ConfigurationResolver::env(self::KEY_CLIENT_REGION)
            ?? ConfigurationResolver::ini(self::KEY_CLIENT_REGION, 'string', $profile)
            ?? ConfigurationResolver::ini(
                self::KEY_CLIENT_REGION,
                'string',
                self::PROFILE_SECONDARY . $profile
            );

        if (empty($resolvedRegion)) {
            throw new CredentialsException(
                'Unable to determine region for the Sign-In service client '
                . ' used for refreshing Login credentials. You can provide a region in-code '
                . 'when constructing the provider, by setting the AWS_REGION environment variable'
                . ', or by setting a `region` in your specified profile.'
            );
        }

        return new SigninClient([
            self::KEY_CLIENT_REGION => $resolvedRegion,
            self::KEY_CLIENT_SIGNATURE_VERSION => self::CLIENT_SIGNATURE_DPOP,
            self::KEY_CLIENT_CREDENTIALS => false
        ]);
    }

    /**
     * Creates a Credentials object from token data
     *
     * @param array $tokenData The token data containing access key, secret, and session token
     * @param int $expiration Unix timestamp for credential expiration
     * @param string $accountId The AWS account ID
     *
     * @return Credentials The created Credentials object
     */
    private static function createCredentials(
        array $tokenData,
        int $expiration,
        string $accountId
    ): Credentials
    {
        return new Credentials(
            $tokenData[self::KEY_ACCESS_KEY_ID],
            $tokenData[self::KEY_SECRET_ACCESS_KEY],
            $tokenData[self::KEY_SESSION_TOKEN],
            $expiration,
            $accountId,
            CredentialSources::PROFILE_LOGIN
        );
    }

    /**
     * Checks if all required keys are present and non-empty in the data array
     *
     * @param array $data The data array to check
     * @param array $requiredKeys The list of keys that must be present and non-empty
     *
     * @return bool True if all required keys are present and non-empty, false otherwise
     */
    private static function hasAllRequiredKeys(
        array $data,
        array $requiredKeys
    ): bool
    {
        foreach ($requiredKeys as $key) {
            if (empty($data[$key])) {
                return false;
            }
        }

        return true;
    }
}
