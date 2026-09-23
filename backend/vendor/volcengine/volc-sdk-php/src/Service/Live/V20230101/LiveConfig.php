<?php

namespace Volc\Service\Live\V20230101;

class LiveConfig
{
    public static function region()
    {
        return [
            'cn-north-1' => [
                'host' => 'https://live.volcengineapi.com',
                'config' => [
                    'timeout' => 5.0,
                    'headers' => [
                        'Accept' => 'application/json'
                    ],
                    'v4_credentials' => [
                        'region' => 'cn-north-1',
                        'service' => 'live'
                    ]
                ]
            ]
        ];
    }

    public static function apiList()
    {
        return [
            'DeleteTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListCommonTransPresetDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListCommonTransPresetDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'TranscodingJobStatus' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'TranscodingJobStatus',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'ListVhostTransCodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostTransCodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'RestartTranscodingJob' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'RestartTranscodingJob',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DeleteWatermarkPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteWatermarkPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateWatermarkPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateWatermarkPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListWatermarkPresetDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListWatermarkPresetDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateWatermarkPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateWatermarkPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateWatermarkPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateWatermarkPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateWatermarkPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateWatermarkPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteWatermarkPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteWatermarkPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListWatermarkPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListWatermarkPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListVhostWatermarkPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostWatermarkPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'StopPullRecordTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'StopPullRecordTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateLiveStreamRecordIndexFiles' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateLiveStreamRecordIndexFiles',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreatePullRecordTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreatePullRecordTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteRecordPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteRecordPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateRecordPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateRecordPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GetPullRecordTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GetPullRecordTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeRecordTaskFileHistory' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeRecordTaskFileHistory',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListVhostRecordPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostRecordPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListPullRecordTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListPullRecordTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateRecordPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateRecordPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteSnapshotPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteSnapshotPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateSnapshotPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateSnapshotPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeCDNSnapshotHistory' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeCDNSnapshotHistory',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListVhostSnapshotPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostSnapshotPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateSnapshotPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateSnapshotPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteTimeShiftPresetV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteTimeShiftPresetV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateTimeShiftPresetV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateTimeShiftPresetV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListTimeShiftPresetV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListTimeShiftPresetV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateTimeShiftPresetV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateTimeShiftPresetV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteCallback' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteCallback',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeCallback' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeCallback',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateCallback' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateCallback',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteCert' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteCert',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeCertDetailSecretV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeCertDetailSecretV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListCertV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListCertV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateCert' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateCert',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'BindCert' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'BindCert',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UnbindCert' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UnbindCert',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteDomain' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteDomain',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'EnableDomain' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'EnableDomain',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateDomainV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateDomainV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateDomainVhost' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateDomainVhost',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeDomain' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeDomain',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListDomainDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListDomainDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateDomain' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateDomain',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DisableDomain' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DisableDomain',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateLiveVideoQualityAnalysisTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateLiveVideoQualityAnalysisTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteLiveVideoQualityAnalysisTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteLiveVideoQualityAnalysisTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GetLiveVideoQualityAnalysisTaskDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GetLiveVideoQualityAnalysisTaskDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListLiveVideoQualityAnalysisTasks' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListLiveVideoQualityAnalysisTasks',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'StopPullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'StopPullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreatePullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreatePullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreatePullToPushGroup' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreatePullToPushGroup',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeletePullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeletePullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeletePullToPushGroup' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeletePullToPushGroup',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ContinuePullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ContinuePullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdatePullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdatePullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdatePullToPushGroup' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdatePullToPushGroup',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListPullToPushGroup' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListPullToPushGroup',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListPullToPushTask' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListPullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'ListPullToPushTaskV2' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListPullToPushTaskV2',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'RelaunchPullToPushTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'RelaunchPullToPushTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteRelaySourceV4' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteRelaySourceV4',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteRelaySourceV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteRelaySourceV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateRelaySourceV4' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateRelaySourceV4',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListRelaySourceV4' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListRelaySourceV4',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeRelaySourceV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeRelaySourceV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateRelaySourceV4' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateRelaySourceV4',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateRelaySourceV3' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateRelaySourceV3',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'KillStream' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'KillStream',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeClosedStreamInfoByPage' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeClosedStreamInfoByPage',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeLiveStreamInfoByPage' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveStreamInfoByPage',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeLiveStreamState' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveStreamState',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeForbiddenStreamInfoByPage' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeForbiddenStreamInfoByPage',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'ForbidStream' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ForbidStream',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ResumeStream' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ResumeStream',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GeneratePlayURL' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GeneratePlayURL',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GeneratePushURL' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GeneratePushURL',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteStreamQuotaConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteStreamQuotaConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeStreamQuotaConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeStreamQuotaConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateStreamQuotaConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateStreamQuotaConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteSnapshotAuditPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteSnapshotAuditPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateSnapshotAuditPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateSnapshotAuditPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListVhostSnapshotAuditPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostSnapshotAuditPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateSnapshotAuditPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateSnapshotAuditPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeIpInfo' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeIpInfo',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveTopPlayData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveTopPlayData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveRegionData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveRegionData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => false,
            ],
            'DescribeLiveSourceStreamMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveSourceStreamMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePushStreamMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePushStreamMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveCallbackData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveCallbackData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchStreamSessionData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchStreamSessionData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveStreamSessionData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveStreamSessionData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePlayStatusCodeData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePlayStatusCodeData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchSourceStreamMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchSourceStreamMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchSourceStreamAvgMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchSourceStreamAvgMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchPushStreamMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchPushStreamMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchPushStreamAvgMetrics' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchPushStreamAvgMetrics',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchStreamTranscodeData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchStreamTranscodeData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveStreamCountData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveStreamCountData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePushStreamCountData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePushStreamCountData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePushStreamInfoData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePushStreamInfoData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveTranscodeInfoData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveTranscodeInfoData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveSourceBandwidthData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveSourceBandwidthData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveSourceTrafficData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveSourceTrafficData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveMetricBandwidthData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveMetricBandwidthData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveMetricTrafficData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveMetricTrafficData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBatchStreamTrafficData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBatchStreamTrafficData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveEdgeStatData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveEdgeStatData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveISPData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveISPData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => false,
            ],
            'DescribeLiveP95PeakBandwidthData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveP95PeakBandwidthData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveAuditData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveAuditData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePullToPushBandwidthData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePullToPushBandwidthData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePullToPushData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePullToPushData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveBandwidthData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveBandwidthData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveRecordData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveRecordData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveSnapshotData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveSnapshotData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveTrafficData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveTrafficData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveTranscodeData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveTranscodeData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveTimeShiftData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveTimeShiftData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLiveLogData' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLiveLogData',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteReferer' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteReferer',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeReferer' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeReferer',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeAuth' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeAuth',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateReferer' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateReferer',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateAuthKey' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateAuthKey',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteHTTPHeaderConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteHTTPHeaderConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'EnableHTTPHeaderConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'EnableHTTPHeaderConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeHTTPHeaderConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeHTTPHeaderConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateHTTPHeaderConfig' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateHTTPHeaderConfig',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateEncryptDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateEncryptDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateEncryptHLS' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateEncryptHLS',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GetHLSEncryptDataKey' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GetHLSEncryptDataKey',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeEncryptHLS' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeEncryptHLS',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => false,
            ],
            'DescribeLicenseDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLicenseDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeCertDRM' => [
                'method' => 'get',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeCertDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => true,
                'has_json' => false,
            ],
            'DescribeEncryptDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeEncryptDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => false,
            ],
            'BindEncryptDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'BindEncryptDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UnBindEncryptDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UnBindEncryptDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListBindEncryptDRM' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListBindEncryptDRM',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteIPAccessRule' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteIPAccessRule',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateIPAccessRule' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateIPAccessRule',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeIPAccessRule' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeIPAccessRule',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateCloudMixTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateCloudMixTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateCloudMixTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateCloudMixTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GetCloudMixTaskDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GetCloudMixTaskDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListCloudMixTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListCloudMixTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteCloudMixTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteCloudMixTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteSubtitleTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteSubtitleTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateSubtitleTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateSubtitleTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListVhostSubtitleTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListVhostSubtitleTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateSubtitleTranscodePreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateSubtitleTranscodePreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateLivePadPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateLivePadPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteLivePadPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteLivePadPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'StopLivePadStream' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'StopLivePadStream',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateLivePadPreset' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateLivePadPreset',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePadStreamList' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePadStreamList',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeLivePadPresetDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeLivePadPresetDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateCarouselTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateCarouselTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteCarouselTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteCarouselTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'UpdateCarouselTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'UpdateCarouselTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'GetCarouselDetail' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'GetCarouselDetail',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListCarouselTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListCarouselTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'CreateHighLightTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'CreateHighLightTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DeleteTaskByAccountID' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DeleteTaskByAccountID',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'DescribeHighLightTaskByAccountID' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'DescribeHighLightTaskByAccountID',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ],
            'ListHighLightTask' => [
                'method' => 'post',
                'url' => '/',
                'config' => [
                    'query' => [
                        'Action' => 'ListHighLightTask',
                        'Version' => '2023-01-01',
                    ]
                ],
                'has_query' => false,
                'has_json' => true,
            ]
        ];
    }
}