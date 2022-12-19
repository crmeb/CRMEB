<?php

namespace Volc\Service\Live;


use Exception;


class LiveOption
{
    public static $apiList = [
        // **********************************************************************
        // 直播
        // **********************************************************************
        'DescribeCDNSnapshotHistory' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCDNSnapshotHistory',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeRecordTaskFileHistory' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeRecordTaskFileHistory',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeLiveStreamInfoByPage' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeLiveStreamInfoByPage',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'KillStream' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'KillStream',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeClosedStreamInfoByPage' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeClosedStreamInfoByPage',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeLiveStreamState' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeLiveStreamState',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeForbiddenStreamInfoByPage' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeForbiddenStreamInfoByPage',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'UpdateRelaySourceV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateRelaySourceV2',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DeleteRelaySourceV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteRelaySourceV2',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeRelaySourceV2' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeRelaySourceV2',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'CreateVQScoreTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateVQScoreTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeVQScoreTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVQScoreTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'ListVQScoreTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListVQScoreTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GeneratePlayURL' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GeneratePlayURL',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GeneratePushURL' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GeneratePushURL',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'CreatePullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreatePullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'ListPullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListPullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'UpdatePullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdatePullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'StopPullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StopPullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'RestartPullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'RestartPullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DeletePullToPushTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeletePullToPushTask',
                    'Version' => '2020-08-01',
                ],
            ]
        ], 'UpdateDenyConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDenyConfig',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeDenyConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeDenyConfig',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
    ];

    /**
     * @throws Exception
     */
    public static function getConfig(string $region = '')
    {
        switch ($region) {
            case 'cn-north-1':
                $config = [
                    'host' => 'https://live.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'live',
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