<?php

namespace Volc\Service;

use Volc\Base\V4Curl;

class Sts extends V4Curl
{
    protected function getConfig(string $region = '')
    {
        return [
            'host' => 'https://sts.volcengineapi.com',
            'config' => [
                'timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'cn-north-1',
                    'service' => 'sts',
                ],
            ],
        ];
    }

    protected $apiList = [
        'AssumeRole' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'AssumeRole',
                    'Version' => '2018-01-01',
                ],
            ],
        ],
    ];

    public function assumeRole(array $query = [])
    {
        $response = $this->request('AssumeRole', $query);
        return $response->getBody();
    }

}