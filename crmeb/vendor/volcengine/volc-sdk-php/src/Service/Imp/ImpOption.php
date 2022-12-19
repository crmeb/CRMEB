<?php

namespace Volc\Service\Imp;

use Exception;

class ImpOption
{
    public static $apiList = [
        'SubmitJob' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SubmitJob',
                    'Version' => '2021-06-11',
                ],
            ]
        ],

        'KillJob' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'KillJob',
                    'Version' => '2021-06-11',
                ],
            ]
        ],

        'RetrieveJob' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'RetrieveJob',
                    'Version' => '2021-06-11',
                ],
            ]
        ]
    ];

    /**
     * @throws Exception
     */
    public static function getConfig(string $region = '')
    {
        switch ($region) {
            case 'cn-north-1':
                $config = [
                    'host' => 'http://open.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'imp',
                        ],
                    ],
                ];
                break;
            default:
                throw new Exception("Cant find the region, please check it carefully");
        }
        return $config;
    }
}
