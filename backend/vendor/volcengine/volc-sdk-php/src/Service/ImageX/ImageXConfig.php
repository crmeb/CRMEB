<?php

namespace Volc\Service\ImageX;

class ImageXConfig
{
    public static $region = [
        'cn-north-1' => [
            'host' => 'https://imagex.volcengineapi.com',
            'config' => [
                'timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'cn-north-1',
                    'service' => 'ImageX',
                ],
            ],
        ],
        'ap-singapore-1' => [
            'host' => 'https://imagex-ap-singapore-1.volcengineapi.com',
            'config' => [
                'timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'ap-singapore-1',
                    'service' => 'ImageX',
                ],
            ],
        ],
        'us-east-1' => [
            'host' => 'https://imagex-us-east-1.volcengineapi.com',
            'config' => [
                'timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'us-east-1',
                    'service' => 'ImageX',
                ],
            ],
        ],
    ];

    public static $apiList = [
        'GetImageServiceSubscription' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageServiceSubscription',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageService' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageService',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageService' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageService',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetAllImageServices' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetAllImageServices',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteImageService' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteImageService',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageAuthKey' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageAuthKey',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageAuthKey' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageAuthKey',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageObjectAccess' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageObjectAccess',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageMirrorConf' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageMirrorConf',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DelDomain' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DelDomain',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetServiceDomains' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetServiceDomains',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetDomainConfig' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetDomainConfig',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'SetDefaultDomain' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'SetDefaultDomain',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateResponseHeader' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateResponseHeader',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateRefer' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateRefer',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateHttps' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateHttps',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetResponseHeaderValidateKeys' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetResponseHeaderValidateKeys',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageTemplate' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageTemplate',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteImageTemplate' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteImageTemplate',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageTemplate' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageTemplate',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetAllImageTemplates' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetAllImageTemplates',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetTemplatesFromBin' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetTemplatesFromBin',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateTemplatesFromBin' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateTemplatesFromBin',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteTemplatesFromBin' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteTemplatesFromBin',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'ApplyImageUpload' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'ApplyImageUpload',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CommitImageUpload' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CommitImageUpload',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageUploadFile' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageUploadFile',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageUploadFiles' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageUploadFiles',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteImageUploadFiles' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteImageUploadFiles',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageFileKey' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageFileKey',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'PreviewImageUploadFile' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'PreviewImageUploadFile',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageContentTask' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageContentTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageContentTaskDetail' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageContentTaskDetail',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageContentBlockList' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageContentBlockList',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageUpdateFiles' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageUpdateFiles',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'FetchImageUrl' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'FetchImageUrl',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetUrlFetchTask' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetUrlFetchTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateServiceName' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateServiceName',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageStorageTTL' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageStorageTTL',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageOCRV2' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageOCRV2',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageQuality' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageQuality',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageEraseModels' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageEraseModels',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageEnhanceResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageEnhanceResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageBgFillResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageBgFillResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageDuplicateDetection' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageDuplicateDetection',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetDedupTaskStatus' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetDedupTaskStatus',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetDenoisingImage' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetDenoisingImage',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetSegmentImage' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetSegmentImage',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageComicResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageComicResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageSuperResolutionResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageSuperResolutionResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageSmartCropResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageSmartCropResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetLicensePlateDetection' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetLicensePlateDetection',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImagePSDetection' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImagePSDetection',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetPrivateImageType' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetPrivateImageType',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageHmEmbed' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageHmEmbed',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageHmExtract' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageHmExtract',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageEraseResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageEraseResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageStyleResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageStyleResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageAuditTask' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageAuditTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteImageAuditTask' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteImageAuditTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DeleteImageAuditResult' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DeleteImageAuditResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateAuditImageStatus' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAuditImageStatus',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageAuditTaskStatus' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageAuditTaskStatus',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageAuditTasks' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageAuditTasks',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetImageAuditResult' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetImageAuditResult',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'UpdateImageAuditTask' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageAuditTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'CreateImageRetryAuditTask' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'CreateImageRetryAuditTask',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'GetAuditEntrysCount' => [
            'method' => 'get',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'GetAuditEntrysCount',
                    'Version' => '2018-08-01',
                ],
            ],
        ],

        'UpdateImageDomainIPAuth' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'UpdateImageDomainIPAuth',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
        'DescribeImageVolcCdnAccessLog' => [
            'method' => 'post',
            'url' => '/',
            'config' => [
                'query' => [
                    'Action' => 'DescribeImageVolcCdnAccessLog',
                    'Version' => '2018-08-01',
                ],
            ],
        ],
    ];
}

require_once "configgen.php";