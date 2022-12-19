<?php
namespace Volc\Service;
use Volc\Base\V4Curl;

class AdBlocker extends V4Curl
{
    protected function getConfig(string $region)
    {
        switch ($region) {
            case 'cn-north-1':
                $config = [
                    'host' => 'https://riskcontrol.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json'
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'AdBlocker',
                        ],
                    ],
                ];
                break;
            default:
                throw new \Exception(sprintf("AdBlocker not support region, %s", $region));
        }
        return $config;
    }

    protected function requestWithRetry(string $api, array $configs): string
    {
        try {
            $response = $this->request($api, $configs);
            return (string)$response->getBody();
        }
        catch (\Exception $e)
        {
            $response = $this->request($api, $configs);
            return (string)$response->getBody();
        }
    }

    protected $apiList = [
        'AdBlock' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'AdBlock',
                    'Version' => '2021-01-06',
                ],
            ]
        ],
    ];

    public function adBlock(int $appId, string $service, string $parameters)
    {
        $commitBody = array();
        $commitBody["AppId"] = $appId;
        $commitBody["Service"] = $service;
        $commitBody["Parameters"] = $parameters;
        $commitReq = [
            "json" => $commitBody
        ];
        return $this->requestWithRetry("AdBlock", $commitReq);
    }
}