<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Utils\Helper;
use AlibabaCloud\Credentials\Utils\Filter;
use AlibabaCloud\Credentials\Request\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use RuntimeException;
use Exception;
use AlibabaCloud\Credentials\Credential\RefreshResult;

/**
 * @internal This class is intended for internal use within the package. 
 * Class OIDCRoleArnCredentialsProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class OIDCRoleArnCredentialsProvider extends SessionCredentialsProvider
{

    /**
     * @var string
     */
    private $roleArn;

    /**
     * @var string
     */
    private $oidcProviderArn;

    /**
     * @var string
     */
    private $oidcTokenFilePath;

    /**
     * @var string
     */
    private $roleSessionName;

    /**
     * @description role session expiration
     * @example 3600
     * @var int
     */
    private $durationSeconds = 3600;

    /**
     * @var string
     */
    private $policy;

    /**
     * @var string
     */
    private $stsEndpoint;

    /**
     * @var int
     */
    private $connectTimeout = 5;

    /**
     * @var int
     */
    private $readTimeout = 5;

    /**
     * OIDCRoleArnCredentialsProvider constructor.
     *
     * @param array $params
     * @param array $options
     */
    public function __construct(array $params = [], array $options = [])
    {
        $this->filterOptions($options);
        $this->filterRoleArn($params);
        $this->filterOIDCProviderArn($params);
        $this->filterOIDCTokenFilePath($params);
        $this->filterRoleSessionName($params);
        $this->filterDurationSeconds($params);
        $this->filterPolicy($params);
        $this->filterSTSEndpoint($params);
    }

    private function filterRoleArn(array $params)
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_ROLE_ARN')) {
            $this->roleArn = Helper::env('ALIBABA_CLOUD_ROLE_ARN');
        }

        if (isset($params['roleArn'])) {
            $this->roleArn = $params['roleArn'];
        }

        Filter::roleArn($this->roleArn);
    }

    private function filterOIDCProviderArn(array $params)
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_OIDC_PROVIDER_ARN')) {
            $this->oidcProviderArn = Helper::env('ALIBABA_CLOUD_OIDC_PROVIDER_ARN');
        }

        if (isset($params['oidcProviderArn'])) {
            $this->oidcProviderArn = $params['oidcProviderArn'];
        }

        Filter::oidcProviderArn($this->oidcProviderArn);
    }

    private function filterOIDCTokenFilePath(array $params)
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_OIDC_TOKEN_FILE')) {
            $this->oidcTokenFilePath = Helper::env('ALIBABA_CLOUD_OIDC_TOKEN_FILE');
        }

        if (isset($params['oidcTokenFilePath'])) {
            $this->oidcTokenFilePath = $params['oidcTokenFilePath'];
        }

        Filter::oidcTokenFilePath($this->oidcTokenFilePath);
    }

    private function filterRoleSessionName(array $params)
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_ROLE_SESSION_NAME')) {
            $this->roleSessionName = Helper::env('ALIBABA_CLOUD_ROLE_SESSION_NAME');
        }

        if (isset($params['roleSessionName'])) {
            $this->roleSessionName = $params['roleSessionName'];
        }

        if (is_null($this->roleSessionName) || $this->roleSessionName === '') {
            $this->roleSessionName = 'phpSdkRoleSessionName';
        }
    }

    private function filterDurationSeconds(array $params)
    {
        if (isset($params['durationSeconds'])) {
            if (is_int($params['durationSeconds'])) {
                $this->durationSeconds = $params['durationSeconds'];
            }
        }
        if ($this->durationSeconds < 900) {
            throw new InvalidArgumentException('Role session expiration should be in the range of 900s - max session duration');
        }
    }

    private function filterPolicy(array $params)
    {
        if (isset($params['policy'])) {
            if (is_string($params['policy'])) {
                $this->policy = $params['policy'];
            }

            if (is_array($params['policy'])) {
                $this->policy = json_encode($params['policy']);
            }
        }
    }

    private function filterSTSEndpoint(array $params)
    {
        $prefix = 'sts';
        if (Helper::envNotEmpty('ALIBABA_CLOUD_VPC_ENDPOINT_ENABLED') || (isset($params['enableVpc']) && $params['enableVpc'] === true)) {
            $prefix = 'sts-vpc';
        }
        if (Helper::envNotEmpty('ALIBABA_CLOUD_STS_REGION')) {
            $this->stsEndpoint = $prefix . '.' . Helper::env('ALIBABA_CLOUD_STS_REGION') . '.aliyuncs.com';
        }

        if (isset($params['stsRegionId'])) {
            $this->stsEndpoint = $prefix . '.' . $params['stsRegionId'] . '.aliyuncs.com';
        }

        if (isset($params['stsEndpoint'])) {
            $this->stsEndpoint = $params['stsEndpoint'];
        }

        if (is_null($this->stsEndpoint) || $this->stsEndpoint === '') {
            $this->stsEndpoint = 'sts.aliyuncs.com';
        }
    }

    private function filterOptions(array $options)
    {
        if (isset($options['connectTimeout'])) {
            $this->connectTimeout = $options['connectTimeout'];
        }

        if (isset($options['readTimeout'])) {
            $this->readTimeout = $options['readTimeout'];
        }

        Filter::timeout($this->connectTimeout, $this->readTimeout);
    }

    /**
     * Get credentials by request.
     *
     * @return RefreshResult
     * @throws RuntimeException
     * @throws GuzzleException
     */
    public function refreshCredentials()
    {
        $options = Request::commonOptions();
        $options['read_timeout'] = $this->readTimeout;
        $options['connect_timeout'] = $this->connectTimeout;

        $options['query']['Action'] = 'AssumeRoleWithOIDC';
        $options['query']['Version'] = '2015-04-01';
        $options['query']['Format'] = 'JSON';
        $options['query']['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        $options['query']['RoleArn'] = $this->roleArn;
        $options['query']['OIDCProviderArn'] = $this->oidcProviderArn;
        try {
            $oidcToken = file_get_contents($this->oidcTokenFilePath);
            $options['query']['OIDCToken'] = $oidcToken;
        } catch (Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
        $options['query']['RoleSessionName'] = $this->roleSessionName;
        $options['query']['DurationSeconds'] = (string) $this->durationSeconds;
        if (!is_null($this->policy)) {
            $options['query']['Policy'] = $this->policy;
        }

        $url = (new Uri())->withScheme('https')->withHost($this->stsEndpoint);

        $result = Request::createClient()->request('POST', $url, $options);

        if ($result->getStatusCode() !== 200) {
            throw new RuntimeException('Error refreshing credentials from OIDC, statusCode: ' . $result->getStatusCode() . ', result: ' . (string) $result);
        }

        $json = $result->toArray();
        $credentials = $json['Credentials'];

        if (!isset($credentials['AccessKeyId']) || !isset($credentials['AccessKeySecret']) || !isset($credentials['SecurityToken'])) {
            throw new RuntimeException('Error retrieving credentials from OIDC result:' . $result->toJson());
        }

        return new RefreshResult(new Credentials([
            'accessKeyId' => $credentials['AccessKeyId'],
            'accessKeySecret' => $credentials['AccessKeySecret'],
            'securityToken' => $credentials['SecurityToken'],
            'expiration' => \strtotime($credentials['Expiration']),
            'providerName' => $this->getProviderName(),
        ]), $this->getStaleTime(strtotime($credentials['Expiration'])));
    }

    public function key()
    {
        return 'oidc_role_arn#roleArn#' . $this->roleArn . '#oidcProviderArn#' . $this->oidcProviderArn . '#roleSessionName#' . $this->roleSessionName;
    }

    public function getProviderName()
    {
        return 'oidc_role_arn';
    }
}
