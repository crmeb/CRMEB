<?php


namespace Volc\Service;
use Volc\Base\V4Curl;

class GameProduct extends V4Curl
{
    protected function getConfig(string $region)
    {
        switch ($region) {
            case 'cn-north-1':
                $config = [
                    'host' => 'https://open.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json'
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'game_protect',
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
        'RiskResult' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'RiskResult',
                    'Version' => '2021-04-25',
                ],
            ]
        ],
    ];

    public function riskResult(int $appId, int $startTime, int $endTime, int $pageSize, int $pageNum): string
    {
        $commitBody = array();
        $commitBody["AppId"] = $appId;
        $commitBody["Service"] = "anti_plugin";
        $commitBody["StartTime"] = $startTime;
        $commitBody["EndTime"] = $endTime;
        $commitBody["PageSize"] = $pageSize;
        $commitBody["PageNum"] = $pageNum;
        $commitReq = [
            "query" => $commitBody
        ];
        return $this->requestWithRetry("RiskResult", $commitReq);
    }
}