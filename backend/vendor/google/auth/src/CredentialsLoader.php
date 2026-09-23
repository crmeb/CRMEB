<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth;

use Google\Auth\Credentials\ImpersonatedServiceAccountCredentials;
use Google\Auth\Credentials\InsecureCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\UserRefreshCredentials;
use RuntimeException;
use UnexpectedValueException;

/**
 * CredentialsLoader contains the behaviour used to locate and find default
 * credentials files on the file system.
 */
abstract class CredentialsLoader implements
    FetchAuthTokenInterface,
    UpdateMetadataInterface
{
    const TOKEN_CREDENTIAL_URI = 'https://oauth2.googleapis.com/token';
    const ENV_VAR = 'GOOGLE_APPLICATION_CREDENTIALS';
    const WELL_KNOWN_PATH = 'gcloud/application_default_credentials.json';
    const NON_WINDOWS_WELL_KNOWN_PATH_BASE = '.config';
    const MTLS_WELL_KNOWN_PATH = '.secureConnect/context_aware_metadata.json';
    const MTLS_CERT_ENV_VAR = 'GOOGLE_API_USE_CLIENT_CERTIFICATE';

    /**
     * @param string $cause
     * @return string
     */
    private static function unableToReadEnv($cause)
    {
        $msg = 'Unable to read the credential file specified by ';
        $msg .= ' GOOGLE_APPLICATION_CREDENTIALS: ';
        $msg .= $cause;

        return $msg;
    }

    /**
     * @return bool
     */
    private static function isOnWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Load a JSON key from the path specified in the environment.
     *
     * Load a JSON key from the path specified in the environment
     * variable GOOGLE_APPLICATION_CREDENTIALS. Return null if
     * GOOGLE_APPLICATION_CREDENTIALS is not specified.
     *
     * @return array<mixed>|null JSON key | null
     */
    public static function fromEnv()
    {
        $path = getenv(self::ENV_VAR);
        if (empty($path)) {
            return null;
        }
        if (!file_exists($path)) {
            $cause = 'file ' . $path . ' does not exist';
            throw new \DomainException(self::unableToReadEnv($cause));
        }
        $jsonKey = file_get_contents($path);
        return json_decode((string) $jsonKey, true);
    }

    /**
     * Load a JSON key from a well known path.
     *
     * The well known path is OS dependent:
     *
     * * windows: %APPDATA%/gcloud/application_default_credentials.json
     * * others: $HOME/.config/gcloud/application_default_credentials.json
     *
     * If the file does not exist, this returns null.
     *
     * @return array<mixed>|null JSON key | null
     */
    public static function fromWellKnownFile()
    {
        $rootEnv = self::isOnWindows() ? 'APPDATA' : 'HOME';
        $path = [getenv($rootEnv)];
        if (!self::isOnWindows()) {
            $path[] = self::NON_WINDOWS_WELL_KNOWN_PATH_BASE;
        }
        $path[] = self::WELL_KNOWN_PATH;
        $path = implode(DIRECTORY_SEPARATOR, $path);
        if (!file_exists($path)) {
            return null;
        }
        $jsonKey = file_get_contents($path);
        return json_decode((string) $jsonKey, true);
    }

    /**
     * Create a new Credentials instance.
     *
     * @param string|string[] $scope the scope of the access request, expressed
     *        either as an Array or as a space-delimited String.
     * @param array<mixed> $jsonKey the JSON credentials.
     * @param string|string[] $defaultScope The default scope to use if no
     *   user-defined scopes exist, expressed either as an Array or as a
     *   space-delimited string.
     *
     * @return ServiceAccountCredentials|UserRefreshCredentials|ImpersonatedServiceAccountCredentials
     */
    public static function makeCredentials(
        $scope,
        array $jsonKey,
        $defaultScope = null
    ) {
        if (!array_key_exists('type', $jsonKey)) {
            throw new \InvalidArgumentException('json key is missing the type field');
        }

        if ($jsonKey['type'] == 'service_account') {
            // Do not pass $defaultScope to ServiceAccountCredentials
            return new ServiceAccountCredentials($scope, $jsonKey);
        }

        if ($jsonKey['type'] == 'authorized_user') {
            $anyScope = $scope ?: $defaultScope;
            return new UserRefreshCredentials($anyScope, $jsonKey);
        }

        if ($jsonKey['type'] == 'impersonated_service_account') {
            $anyScope = $scope ?: $defaultScope;
            return new ImpersonatedServiceAccountCredentials($anyScope, $jsonKey);
        }

        throw new \InvalidArgumentException('invalid value in the type field');
    }

    /**
     * Create an authorized HTTP Client from an instance of FetchAuthTokenInterface.
     *
     * @param FetchAuthTokenInterface $fetcher is used to fetch the auth token
     * @param array<mixed> $httpClientOptions (optional) Array of request options to apply.
     * @param callable $httpHandler (optional) http client to fetch the token.
     * @param callable $tokenCallback (optional) function to be called when a new token is fetched.
     * @return \GuzzleHttp\Client
     */
    public static function makeHttpClient(
        FetchAuthTokenInterface $fetcher,
        array $httpClientOptions = [],
        callable $httpHandler = null,
        callable $tokenCallback = null
    ) {
        $middleware = new Middleware\AuthTokenMiddleware(
            $fetcher,
            $httpHandler,
            $tokenCallback
        );
        $stack = \GuzzleHttp\HandlerStack::create();
        $stack->push($middleware);

        return new \GuzzleHttp\Client([
            'handler' => $stack,
            'auth' => 'google_auth',
        ] + $httpClientOptions);
    }

    /**
     * Create a new instance of InsecureCredentials.
     *
     * @return InsecureCredentials
     */
    public static function makeInsecureCredentials()
    {
        return new InsecureCredentials();
    }

    /**
     * export a callback function which updates runtime metadata.
     *
     * @return callable updateMetadata function
     * @deprecated
     */
    public function getUpdateMetadataFunc()
    {
        return [$this, 'updateMetadata'];
    }

    /**
     * Updates metadata with the authorization token.
     *
     * @param array<mixed> $metadata metadata hashmap
     * @param string $authUri optional auth uri
     * @param callable $httpHandler callback which delivers psr7 request
     * @return array<mixed> updated metadata hashmap
     */
    public function updateMetadata(
        $metadata,
        $authUri = null,
        callable $httpHandler = null
    ) {
        if (isset($metadata[self::AUTH_METADATA_KEY])) {
            // Auth metadata has already been set
            return $metadata;
        }
        $result = $this->fetchAuthToken($httpHandler);
        $metadata_copy = $metadata;
        if (isset($result['access_token'])) {
            $metadata_copy[self::AUTH_METADATA_KEY] = ['Bearer ' . $result['access_token']];
        } elseif (isset($result['id_token'])) {
            $metadata_copy[self::AUTH_METADATA_KEY] = ['Bearer ' . $result['id_token']];
        }
        return $metadata_copy;
    }

    /**
     * Gets a callable which returns the default device certification.
     *
     * @throws UnexpectedValueException
     * @return callable|null
     */
    public static function getDefaultClientCertSource()
    {
        if (!$clientCertSourceJson = self::loadDefaultClientCertSourceFile()) {
            return null;
        }
        $clientCertSourceCmd = $clientCertSourceJson['cert_provider_command'];

        return function () use ($clientCertSourceCmd) {
            $cmd = array_map('escapeshellarg', $clientCertSourceCmd);
            exec(implode(' ', $cmd), $output, $returnVar);

            if (0 === $returnVar) {
                return implode(PHP_EOL, $output);
            }
            throw new RuntimeException(
                '"cert_provider_command" failed with a nonzero exit code'
            );
        };
    }

    /**
     * Determines whether or not the default device certificate should be loaded.
     *
     * @return bool
     */
    public static function shouldLoadClientCertSource()
    {
        return filter_var(getenv(self::MTLS_CERT_ENV_VAR), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{cert_provider_command:string[]}|null
     */
    private static function loadDefaultClientCertSourceFile()
    {
        $rootEnv = self::isOnWindows() ? 'APPDATA' : 'HOME';
        $path = sprintf('%s/%s', getenv($rootEnv), self::MTLS_WELL_KNOWN_PATH);
        if (!file_exists($path)) {
            return null;
        }
        $jsonKey = file_get_contents($path);
        $clientCertSourceJson = json_decode((string) $jsonKey, true);
        if (!$clientCertSourceJson) {
            throw new UnexpectedValueException('Invalid client cert source JSON');
        }
        if (!isset($clientCertSourceJson['cert_provider_command'])) {
            throw new UnexpectedValueException(
                'cert source requires "cert_provider_command"'
            );
        }
        if (!is_array($clientCertSourceJson['cert_provider_command'])) {
            throw new UnexpectedValueException(
                'cert source expects "cert_provider_command" to be an array'
            );
        }
        return $clientCertSourceJson;
    }
}
