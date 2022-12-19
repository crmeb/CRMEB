<?php

namespace Volc\Service\Vod;


use Exception;

const ActionApplyUpload = "vod:ApplyUploadInfo";
const ActionCommitUpload = "vod:CommitUploadInfo";
const Statement = "Statement";


class VodOption
{
    public static $apiList = [
        // **********************************************************************
        // 播放
        // **********************************************************************
        'GetPlayInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetPlayInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetAllPlayInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetAllPlayInfo',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'GetPrivateDrmPlayAuth' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetPrivateDrmPlayAuth',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetHlsDecryptionKey' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetHlsDecryptionKey',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetPlayInfoWithLiveTimeShiftScene' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetPlayInfoWithLiveTimeShiftScene',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        // **********************************************************************
        // 上传
        // **********************************************************************
        'UploadMediaByUrl' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UploadMediaByUrl',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'QueryUploadTaskInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'QueryUploadTaskInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'ApplyUploadInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ApplyUploadInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'CommitUploadInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'timeout' => 8.0,
                'query' => [
                    'Action' => 'CommitUploadInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        // **********************************************************************
        // 媒资
        // **********************************************************************
        'UpdateMediaInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateMediaInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'UpdateMediaPublishStatus' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateMediaPublishStatus',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetMediaInfos' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetMediaInfos',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetRecommendedPoster' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetRecommendedPoster',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DeleteMedia' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteMedia',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DeleteTranscodes' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteTranscodes',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetMediaList' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetMediaList',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetSubtitleInfoList' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetSubtitleInfoList',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'UpdateSubtitleStatus' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateSubtitleStatus',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'UpdateSubtitleInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateSubtitleInfo',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetAuditFramesForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetAuditFramesForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'GetMLFramesForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetMLFramesForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'GetBetterFramesForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetBetterFramesForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'GetAudioInfoForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetAudioInfoForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'GetAutomaticSpeechRecognitionForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetAutomaticSpeechRecognitionForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'GetAudioEventDetectionForAudit' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetAudioEventDetectionForAudit',
                    'Version' => '2021-11-01',
                ],
            ]
        ],
        'CreateVideoClassification' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'CreateVideoClassification',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'UpdateVideoClassification' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateVideoClassification',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'DeleteVideoClassification' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteVideoClassification',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'ListVideoClassifications' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListVideoClassifications',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'ListSnapshots' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListSnapshots',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        // **********************************************************************
        // 转码
        // **********************************************************************
        'StartWorkflow' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StartWorkflow',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'RetrieveTranscodeResult' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'RetrieveTranscodeResult',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'GetWorkflowExecution' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetWorkflowExecution',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        // **********************************************************************
        // 视频编辑
        // **********************************************************************
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
        'GetDirectEditProgress' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetDirectEditProgress',
                    'Version' => '2018-01-01',
                ],
            ]
        ],
        // **********************************************************************
        // 空间管理
        // **********************************************************************
        'CreateSpace' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'CreateSpace',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'ListSpace' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListSpace',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'GetSpaceDetail' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetSpaceDetail',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'UpdateSpace' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateSpace',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'UpdateSpaceUploadConfig' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateSpaceUploadConfig',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'DescribeVodSpaceStorageData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceStorageData',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        // **********************************************************************
        // 分发加速管理
        // **********************************************************************
        'ListDomain' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListDomain',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'CreateCdnRefreshTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'CreateCdnRefreshTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'CreateCdnPreloadTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'CreateCdnPreloadTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
        'ListCdnTasks' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnTasks',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'ListCdnAccessLog' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnAccessLog',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'ListCdnTopAccessUrl' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnTopAccessUrl',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'DescribeVodDomainBandwidthData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodDomainBandwidthData',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'DescribeVodDomainTrafficData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodDomainTrafficData',
                    'Version' => '2020-08-01',
                ],
            ]
        ],
        'ListCdnUsageData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnUsageData',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'ListCdnStatusData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnStatusData',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'DescribeIpInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeIpInfo',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'ListCdnPvData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnPvData',
                    'Version' => '2022-01-01',
                ],
            ]
        ],

        // **********************************************************************
        // 回调管理
        // **********************************************************************
        'AddCallbackSubscription' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'AddCallbackSubscription',
                    'Version' => '2021-12-01',
                ],
            ]
        ],
        'SetCallbackEvent' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SetCallbackEvent',
                    'Version' => '2022-01-01',
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
                    'host' => 'https://vod.volcengineapi.com',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'vod',
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