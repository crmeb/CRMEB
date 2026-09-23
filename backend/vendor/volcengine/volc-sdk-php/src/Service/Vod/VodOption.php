<?php

namespace Volc\Service\Vod;


use Exception;

const ActionApplyUpload = "vod:ApplyUploadInfo";
const ActionCommitUpload = "vod:CommitUploadInfo";
const Statement = "Statement";

const ResourceSpaceNameTRN = "trn:vod:*:*:space/%s";


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
        'CreateHlsDecryptionKey' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateHlsDecryptionKey',
                    'Version' => '2023-07-01',
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
        'DescribeDrmDataKey' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeDrmDataKey',
                    'Version' => '2023-07-01',
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
        // 文件操作
        // **********************************************************************
        'SubmitMoveObjectTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SubmitMoveObjectTask',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'QueryMoveObjectTaskInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'QueryMoveObjectTaskInfo',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        // **********************************************************************
        // 上传
        // **********************************************************************
        'UploadMediaByUrl' => [
            'url' => '/',
            'method' => 'post',
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
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'CommitUploadInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'timeout' => 30.0,
                'query' => [
                    'Action' => 'CommitUploadInfo',
                    'Version' => '2022-01-01',
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
                    'Version' => '2022-12-01',
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
        'UpdateMediaStorageClass' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateMediaStorageClass',
                    'Version' => '2022-12-01',
                ],
            ]
        ],
        'GetInnerAuditURLs' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetInnerAuditURLs',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetAdAuditResultByVid' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetAdAuditResultByVid',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetMediaInfos' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetMediaInfos',
                    'Version' => '2022-12-01',
                ],
            ]
        ],
        'GetMediaInfos20230701' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetMediaInfos',
                    'Version' => '2023-07-01',
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
        'DeleteMediaTosFile' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteMediaTosFile',
                    'Version' => '2022-12-01',
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
        'DeleteMaterial' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteMaterial',
                    'Version' => '2023-07-01',
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
        'ExtractMediaMetaTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ExtractMediaMetaTask',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'SubmitBlockObjectTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitBlockObjectTasks',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListBlockObjectTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListBlockObjectTasks',
                    'Version' => '2023-07-01',
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
        'GetWorkflowExecutionResult' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetWorkflowExecutionResult',
                    'Version' => '2022-12-01',
                ],
            ]
        ],
        'GetTaskTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetTaskTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'CreateTaskTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateTaskTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateTaskTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateTaskTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListTaskTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListTaskTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DeleteTaskTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteTaskTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetWorkflowTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetWorkflowTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'CreateWorkflowTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateWorkflowTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateWorkflowTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateWorkflowTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListWorkflowTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListWorkflowTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DeleteWorkflowTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteWorkflowTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetWatermarkTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetWatermarkTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'CreateWatermarkTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateWatermarkTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateWatermarkTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateWatermarkTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListWatermarkTemplate' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListWatermarkTemplate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DeleteWatermarkTemplate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteWatermarkTemplate',
                    'Version' => '2023-07-01',
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
        'SubmitDirectEditTaskSync' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitDirectEditTaskSync',
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
        'CancelDirectEditTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CancelDirectEditTask',
                    'Version' => '2018-01-01',
                ],
            ]
        ],
        // **********************************************************************
        // 空间管理
        // **********************************************************************
        'DeleteSpace' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteSpace',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
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
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeUploadSpaceConfig' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeUploadSpaceConfig',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateUploadSpaceConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateUploadSpaceConfig',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        // **********************************************************************
        // 分发加速管理
        // **********************************************************************
        'DeleteDomain' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DeleteDomain',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'StopDomain' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'StopDomain',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'StartDomain' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'StartDomain',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateDomainPlayRule' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDomainPlayRule',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'RemoveDomainFromScheduler' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'RemoveDomainFromScheduler',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'AddDomainToScheduler' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'AddDomainToScheduler',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListDomain' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListDomain',
                    'Version' => '2023-01-01',
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
        'ListCdnTopAccess' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnTopAccess',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodDomainBandwidthData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodDomainBandwidthData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodDomainTrafficData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodDomainTrafficData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'ListCdnUsageData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnUsageData',
                    'Version' => '2022-12-01',
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
        'SubmitBlockTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitBlockTasks',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'ListFileMetaInfosByFileNames' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListFileMetaInfosByFileNames',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetFileInfos' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'GetFileInfos',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'UpdateFileStorageClass' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateFileStorageClass',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'GetContentBlockTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetContentBlockTasks',
                    'Version' => '2022-01-01',
                ],
            ]
        ],
        'CreateDomain' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateDomain',
                    'Version' => '2023-02-01',
                ],
            ]
        ],

        'UpdateDomainExpire' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDomainExpire',
                    'Version' => '2023-02-01',
                ],
            ]
        ],

        'UpdateDomainAuthConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDomainAuthConfig',
                    'Version' => '2023-02-01',
                ],
            ]
        ],

        'AddOrUpdateCertificate' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'AddOrUpdateCertificate',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        'UpdateDomainUrlAuthConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDomainUrlAuthConfig',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        'DescribeDomainConfig' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeDomainConfig',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        'UpdateDomainConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateDomainConfig',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        'DescribeDomainVerifyContent' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeDomainVerifyContent',
                    'Version' => '2023-07-01',
                ],
            ]
        ],

        'VerifyDomainOwner' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'VerifyDomainOwner',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeCdnEdgeIp' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnEdgeIp',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeCdnRegionAndIsp' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnRegionAndIsp',
                    'Version' => '2023-07-01',
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
        // **********************************************************************
        // 计量计费
        // **********************************************************************
        'DescribeVodSpaceTranscodeData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceTranscodeData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceAIStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceAIStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceSubtitleStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceSubtitleStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceDetectStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceDetectStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSnapshotData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSnapshotData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceWorkflowDetailData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceWorkflowDetailData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceEditDetailData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceEditDetailData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodEnhanceImageData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodEnhanceImageData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodSpaceEditStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodSpaceEditStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodPlayedStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodPlayedStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodMostPlayedStatisData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodMostPlayedStatisData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodRealtimeMediaData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodRealtimeMediaData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodRealtimeMediaDetailData' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodRealtimeMediaDetailData',
                    'Version' => '2023-07-01',
                ],
            ]
        ],
        'DescribeVodVidTrafficFileLog' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'DescribeVodVidTrafficFileLog',
                    'Version' => '2023-07-01',
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
                    'scheme' => 'https',
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
            case 'ap-southeast-1':
                $config = [
                    'host' => 'https://vod.ap-southeast-1.volcengineapi.com',
                    'scheme' => 'https',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'v4_credentials' => [
                            'region' => 'ap-southeast-1',
                            'service' => 'vod',
                        ],
                    ],
                ];
                break;
            default:
                $config = [
                    'host' => sprintf('https://vod.%s.volcengineapi.com',$region),
                    'scheme' => 'https',
                    'config' => [
                        'timeout' => 5.0,
                        'headers' => [
                            'Accept' => 'application/json',
                        ],
                        'v4_credentials' => [
                            'region' => $region,
                            'service' => 'vod',
                        ],
                    ],
                ];
        }
        return $config;
    }
}