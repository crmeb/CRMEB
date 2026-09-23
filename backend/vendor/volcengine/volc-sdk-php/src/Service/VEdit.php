<?php

namespace Volc\Service;

use Volc\Base\V4Curl;

class VEdit extends V4Curl
{
    protected function getConfig(string $region)
    {
        switch ($region) {
            case 'cn-north-1':
                $config = [
                    'host' => 'https://vedit.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json'
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'edit',
                        ],
                    ],
                ];
                break;
            default:
                throw new \Exception(sprintf("edit not support region, %s", $region));
        }
        return $config;
    }

    protected $apiList = [
        'SubmitDirectEditTaskAsync' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitDirectEditTaskAsync',
                    'Version' => '2018-01-01',
                ],
            ]
        ],
        'SubmitTemplateTaskAsync' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitTemplateTaskAsync',
                    'Version' => '2018-01-01',
                ],
            ]
        ],
        'GetDirectEditResult' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetDirectEditResult',
                    'Version' => '2018-01-01',
                ],
            ]
        ],
    ];


    public function submitTemplateTaskAsync(array $query = [])
    {
        $response = $this->request('SubmitTemplateTaskAsync', $query);
        return $response->getBody();
    }

    public function submitDirectEditTaskAsync(array $query = [])
    {
        $response = $this->request('SubmitDirectEditTaskAsync', $query);
        return $response->getBody();
    }

    public function getDirectEditResult(array $query = [])
    {
        $response = $this->request('GetDirectEditResult', $query);
        return $response->getBody();
    }
}
