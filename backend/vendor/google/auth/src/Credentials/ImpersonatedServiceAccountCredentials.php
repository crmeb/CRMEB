<?php

/*
 * Copyright 2022 Google Inc.
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

namespace Google\Auth\Credentials;

use Google\Auth\CredentialsLoader;
use Google\Auth\IamSignerTrait;
use Google\Auth\SignBlobInterface;

class ImpersonatedServiceAccountCredentials extends CredentialsLoader implements SignBlobInterface
{
    use IamSignerTrait;

    /**
     * @var string
     */
    protected $impersonatedServiceAccountName;

    /**
     * @var UserRefreshCredentials
     */
    protected $sourceCredentials;

    /**
     * Instantiate an instance of ImpersonatedServiceAccountCredentials from a credentials file that has be created with
     * the --impersonated-service-account flag.
     *
     * @param string|string[] $scope the scope of the access request, expressed
     *   either as an Array or as a space-delimited String.
     * @param string|array<mixed> $jsonKey JSON credential file path or JSON credentials
     *   as an associative array
     */
    public function __construct(
        $scope,
        $jsonKey
    ) {
        if (is_string($jsonKey)) {
            if (!file_exists($jsonKey)) {
                throw new \InvalidArgumentException('file does not exist');
            }
            $json = file_get_contents($jsonKey);
            if (!$jsonKey = json_decode((string) $json, true)) {
                throw new \LogicException('invalid json for auth config');
            }
        }
        if (!array_key_exists('service_account_impersonation_url', $jsonKey)) {
            throw new \LogicException('json key is missing the service_account_impersonation_url field');
        }
        if (!array_key_exists('source_credentials', $jsonKey)) {
            throw new \LogicException('json key is missing the source_credentials field');
        }

        $this->impersonatedServiceAccountName = $this->getImpersonatedServiceAccountNameFromUrl($jsonKey['service_account_impersonation_url']);

        $this->sourceCredentials = new UserRefreshCredentials($scope, $jsonKey['source_credentials']);
    }

    /**
     * Helper function for extracting the Server Account Name from the URL saved in the account credentials file
     * @param $serviceAccountImpersonationUrl string URL from the 'service_account_impersonation_url' field
     * @return string Service account email or ID.
     */
    private function getImpersonatedServiceAccountNameFromUrl(string $serviceAccountImpersonationUrl)
    {
        $fields = explode('/', $serviceAccountImpersonationUrl);
        $lastField = end($fields);
        $splitter = explode(':', $lastField);
        return $splitter[0];
    }

    /**
     * Get the client name from the keyfile
     *
     * In this implementation, it will return the issuers email from the oauth token.
     *
     * @param callable|null $unusedHttpHandler not used by this credentials type.
     * @return string Token issuer email
     */
    public function getClientName(callable $unusedHttpHandler = null)
    {
        return $this->impersonatedServiceAccountName;
    }

    /**
     * @param callable $httpHandler
     *
     * @return array<mixed> {
     *     A set of auth related metadata, containing the following
     *
     *     @type string $access_token
     *     @type int $expires_in
     *     @type string $scope
     *     @type string $token_type
     *     @type string $id_token
     * }
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        return $this->sourceCredentials->fetchAuthToken($httpHandler);
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->sourceCredentials->getCacheKey();
    }

    /**
     * @return array<mixed>
     */
    public function getLastReceivedToken()
    {
        return $this->sourceCredentials->getLastReceivedToken();
    }
}
