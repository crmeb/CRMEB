<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Utils\Helper;
use AlibabaCloud\Credentials\Utils\Filter;
use AlibabaCloud\Credentials\Request\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\GuzzleException;
use AlibabaCloud\Credentials\Credential\RefreshResult;

use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * @internal This class is intended for internal use within the package.
 * Class RsaKeyPairCredentialsProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class RsaKeyPairCredentialsProvider extends SessionCredentialsProvider
{

    /**
     * @var string
     */
    private $publicKeyId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @description role session expiration
     * @example 3600
     * @var int
     */
    private $durationSeconds = 3600;

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
     * RsaKeyPairCredentialsProvider constructor.
     *
     * @param array $params
     * @param array $options
     */
    public function __construct(array $params = [], array $options = [])
    {
        $this->filterOptions($options);
        $this->filterDurationSeconds($params);
        $this->filterSTSEndpoint($params);
        $this->publicKeyId = isset($params['publicKeyId']) ? $params['publicKeyId'] : null;
        $privateKeyFile = isset($params['privateKeyFile']) ? $params['privateKeyFile'] : null;
        Filter::publicKeyId($this->publicKeyId);
        Filter::privateKeyFile($privateKeyFile);

        try {
            $this->privateKey = file_get_contents($privateKeyFile);
        } catch (Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage());
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

    private function filterSTSEndpoint(array $params)
    {
        if (isset($params['stsEndpoint'])) {
            $this->stsEndpoint = $params['stsEndpoint'];
        }

        if (is_null($this->stsEndpoint) || $this->stsEndpoint === '') {
            $this->stsEndpoint = 'sts.ap-northeast-1.aliyuncs.com';
        }
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

        $options['query']['Action'] = 'GenerateSessionAccessKey';
        $options['query']['Version'] = '2015-04-01';
        $options['query']['Format'] = 'JSON';
        $options['query']['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        $options['query']['SignatureMethod'] = 'SHA256withRSA';
        $options['query']['SignatureType'] = 'PRIVATEKEY';
        $options['query']['SignatureVersion'] = '1.0';
        $options['query']['SignatureNonce'] = Request::uuid(json_encode($options['query']));
        $options['query']['DurationSeconds'] = (string) $this->durationSeconds;
        $options['query']['AccessKeyId'] = $this->publicKeyId;
        $options['query']['Signature'] = Request::shaHmac256WithRsasign(
            Request::signString('GET', $options['query']),
            $this->privateKey
        );

        $url = (new Uri())->withScheme('https')->withHost($this->stsEndpoint);

        $result = Request::createClient()->request('GET', $url, $options);

        if ($result->getStatusCode() !== 200) {
            throw new RuntimeException('Error refreshing credentials from RsaKeyPair, statusCode: ' . $result->getStatusCode() . ', result: ' . (string) $result);
        }

        $json = $result->toArray();

        if (!isset($json['SessionAccessKey']['SessionAccessKeyId']) || !isset($json['SessionAccessKey']['SessionAccessKeySecret'])) {
            throw new RuntimeException('Error retrieving credentials from RsaKeyPair result:' . $result->toJson());
        }

        $credentials = [];
        $credentials['AccessKeyId'] = $json['SessionAccessKey']['SessionAccessKeyId'];
        $credentials['AccessKeySecret'] = $json['SessionAccessKey']['SessionAccessKeySecret'];
        $credentials['Expiration'] = $json['SessionAccessKey']['Expiration'];
        $credentials['SecurityToken'] = null;


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
        return 'rsa_key_pair#publicKeyId#' . $this->publicKeyId;
    }

    public function getProviderName()
    {
        return 'rsa_key_pair';
    }

    /**
     * @return string
     */
    public function getPublicKeyId()
    {
        return $this->publicKeyId;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }
}
