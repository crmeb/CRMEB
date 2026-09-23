<?php

namespace AlibabaCloud\Credentials\Providers;

use AlibabaCloud\Credentials\Utils\Helper;
use AlibabaCloud\Credentials\Utils\Filter;

/**
 * @internal This class is intended for internal use within the package. 
 * Class StaticAKCredentialsProvider
 *
 * @package AlibabaCloud\Credentials\Providers
 */
class StaticAKCredentialsProvider implements CredentialsProvider
{

    /**
     * @var string
     */
    private $accessKeyId;

    /**
     * @var string
     */
    private $accessKeySecret;

    /**
     * StaticAKCredentialsProvider constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->filterAK($params);
    }

    private function filterAK(array $params)
    {
        if (Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_ID')) {
            $this->accessKeyId =  Helper::env('ALIBABA_CLOUD_ACCESS_KEY_ID');
        }

        if (Helper::envNotEmpty('ALIBABA_CLOUD_ACCESS_KEY_SECRET')) {
            $this->accessKeySecret =  Helper::env('ALIBABA_CLOUD_ACCESS_KEY_SECRET');
        }

        if (isset($params['accessKeyId'])) {
            $this->accessKeyId = $params['accessKeyId'];
        }
        if (isset($params['accessKeySecret'])) {
            $this->accessKeySecret = $params['accessKeySecret'];
        }

        Filter::accessKey($this->accessKeyId, $this->accessKeySecret);
    }

    /**
     * Get credential.
     *
     * @return Credentials
     */
    public function getCredentials()
    {
        return new Credentials([
            'accessKeyId' => $this->accessKeyId,
            'accessKeySecret' => $this->accessKeySecret,
            'providerName' => $this->getProviderName(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getProviderName()
    {
        return "static_ak";
    }
}