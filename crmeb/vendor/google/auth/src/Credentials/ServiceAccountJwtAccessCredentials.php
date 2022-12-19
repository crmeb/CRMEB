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

namespace Google\Auth\Credentials;

use Google\Auth\CredentialsLoader;
use Google\Auth\GetQuotaProjectInterface;
use Google\Auth\OAuth2;
use Google\Auth\ProjectIdProviderInterface;
use Google\Auth\ServiceAccountSignerTrait;
use Google\Auth\SignBlobInterface;

/**
 * Authenticates requests using Google's Service Account credentials via
 * JWT Access.
 *
 * This class allows authorizing requests for service accounts directly
 * from credentials from a json key file downloaded from the developer
 * console (via 'Generate new Json Key').  It is not part of any OAuth2
 * flow, rather it creates a JWT and sends that as a credential.
 */
class ServiceAccountJwtAccessCredentials extends CredentialsLoader implements
    GetQuotaProjectInterface,
    SignBlobInterface,
    ProjectIdProviderInterface
{
    use ServiceAccountSignerTrait;

    /**
     * The OAuth2 instance used to conduct authorization.
     *
     * @var OAuth2
     */
    protected $auth;

    /**
     * The quota project associated with the JSON credentials
     *
     * @var string
     */
    protected $quotaProject;

    /**
     * @var string
     */
    public $projectId;

    /**
     * Create a new ServiceAccountJwtAccessCredentials.
     *
     * @param string|array<mixed> $jsonKey JSON credential file path or JSON credentials
     *   as an associative array
     * @param string|string[] $scope the scope of the access request, expressed
     *   either as an Array or as a space-delimited String.
     */
    public function __construct($jsonKey, $scope = null)
    {
        if (is_string($jsonKey)) {
            if (!file_exists($jsonKey)) {
                throw new \InvalidArgumentException('file does not exist');
            }
            $jsonKeyStream = file_get_contents($jsonKey);
            if (!$jsonKey = json_decode((string) $jsonKeyStream, true)) {
                throw new \LogicException('invalid json for auth config');
            }
        }
        if (!array_key_exists('client_email', $jsonKey)) {
            throw new \InvalidArgumentException(
                'json key is missing the client_email field'
            );
        }
        if (!array_key_exists('private_key', $jsonKey)) {
            throw new \InvalidArgumentException(
                'json key is missing the private_key field'
            );
        }
        if (array_key_exists('quota_project_id', $jsonKey)) {
            $this->quotaProject = (string) $jsonKey['quota_project_id'];
        }
        $this->auth = new OAuth2([
            'issuer' => $jsonKey['client_email'],
            'sub' => $jsonKey['client_email'],
            'signingAlgorithm' => 'RS256',
            'signingKey' => $jsonKey['private_key'],
            'scope' => $scope,
        ]);

        $this->projectId = isset($jsonKey['project_id'])
            ? $jsonKey['project_id']
            : null;
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
        $scope = $this->auth->getScope();
        if (empty($authUri) && empty($scope)) {
            return $metadata;
        }

        $this->auth->setAudience($authUri);

        return parent::updateMetadata($metadata, $authUri, $httpHandler);
    }

    /**
     * Implements FetchAuthTokenInterface#fetchAuthToken.
     *
     * @param callable $httpHandler
     *
     * @return null|array{access_token:string} A set of auth related metadata
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        $audience = $this->auth->getAudience();
        $scope = $this->auth->getScope();
        if (empty($audience) && empty($scope)) {
            return null;
        }

        if (!empty($audience) && !empty($scope)) {
            throw new \UnexpectedValueException(
                'Cannot sign both audience and scope in JwtAccess'
            );
        }

        $access_token = $this->auth->toJwt();

        // Set the self-signed access token in OAuth2 for getLastReceivedToken
        $this->auth->setAccessToken($access_token);

        return ['access_token' => $access_token];
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->auth->getCacheKey();
    }

    /**
     * @return array<mixed>
     */
    public function getLastReceivedToken()
    {
        return $this->auth->getLastReceivedToken();
    }

    /**
     * Get the project ID from the service account keyfile.
     *
     * Returns null if the project ID does not exist in the keyfile.
     *
     * @param callable $httpHandler Not used by this credentials type.
     * @return string|null
     */
    public function getProjectId(callable $httpHandler = null)
    {
        return $this->projectId;
    }

    /**
     * Get the client name from the keyfile.
     *
     * In this case, it returns the keyfile's client_email key.
     *
     * @param callable $httpHandler Not used by this credentials type.
     * @return string
     */
    public function getClientName(callable $httpHandler = null)
    {
        return $this->auth->getIssuer();
    }

    /**
     * Get the quota project used for this API request
     *
     * @return string|null
     */
    public function getQuotaProject()
    {
        return $this->quotaProject;
    }
}
