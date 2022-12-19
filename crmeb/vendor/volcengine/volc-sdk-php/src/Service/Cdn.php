<?php

namespace Volc\Service;

use Volc\Base\V4Curl;

class Cdn extends V4Curl
{

    protected $apiList = [
#添加加速域名: https://www.volcengine.com/docs/6454/97340
        'AddCdnDomain' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'AddCdnDomain',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#上线加速域名: https://www.volcengine.com/docs/6454/74667
        'StartCdnDomain' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StartCdnDomain',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#下线加速域名: https://www.volcengine.com/docs/6454/75129
        'StopCdnDomain' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'StopCdnDomain',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#删除加速域名: https://www.volcengine.com/docs/6454/75130
        'DeleteCdnDomain' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteCdnDomain',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取域名列表: https://www.volcengine.com/docs/6454/75269
        'ListCdnDomains' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListCdnDomains',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取域名配置详情: https://www.volcengine.com/docs/6454/80320
        'DescribeCdnConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnConfig',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#修改加速域名配置: https://www.volcengine.com/docs/6454/97266
        'UpdateCdnConfig' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateCdnConfig',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取访问统计的细分数据: https://www.volcengine.com/docs/6454/70442
        'DescribeCdnData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取访问统计的汇总数据: https://www.volcengine.com/docs/6454/96132
        'DescribeEdgeNrtDataSummary' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeEdgeNrtDataSummary',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取回源统计的细分数据: https://www.volcengine.com/docs/6454/70443
        'DescribeCdnOriginData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnOriginData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取回源统计的汇总数据: https://www.volcengine.com/docs/6454/96133
        'DescribeOriginNrtDataSummary' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeOriginNrtDataSummary',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取省份运营商的细分数据: https://www.volcengine.com/docs/6454/75159
        'DescribeCdnDataDetail' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnDataDetail',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取独立访客的细分数据: https://www.volcengine.com/docs/6454/79321
        'DescribeEdgeStatisticalData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeEdgeStatisticalData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取访问统计的排行数据: https://www.volcengine.com/docs/6454/96145
        'DescribeEdgeTopNrtData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeEdgeTopNrtData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取回源数据的统计排序: https://www.volcengine.com/docs/6454/104892
        'DescribeOriginTopNrtData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeOriginTopNrtData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取访问状态码的统计排序: https://www.volcengine.com/docs/6454/104888
        'DescribeEdgeTopStatusCode' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeEdgeTopStatusCode',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取回源状态码的统计排序: https://www.volcengine.com/docs/6454/104891
        'DescribeOriginTopStatusCode' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeOriginTopStatusCode',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取热点及访客排行数据: https://www.volcengine.com/docs/6454/79322
        'DescribeEdgeTopStatisticalData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeEdgeTopStatisticalData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取区域和 ISP 列表: https://www.volcengine.com/docs/6454/70445
        'DescribeCdnRegionAndIsp' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnRegionAndIsp',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#（Deprecated）查询域名排行数据: https://www.volcengine.com/docs/6454/70447?type=preview
        'DescribeCdnDomainTopData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnDomainTopData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#查询账号计费方式: https://www.volcengine.com/docs/6454/78999
        'DescribeCdnService' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnService',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取计费指标的细分数据: https://www.volcengine.com/docs/6454/96167
        'DescribeAccountingData' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeAccountingData',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#提交刷新任务: https://www.volcengine.com/docs/6454/70438
        'SubmitRefreshTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitRefreshTask',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#提交预热任务: https://www.volcengine.com/docs/6454/70436
        'SubmitPreloadTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitPreloadTask',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取刷新预热任务信息: https://www.volcengine.com/docs/6454/70437
        'DescribeContentTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeContentTasks',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取刷新预热配额信息: https://www.volcengine.com/docs/6454/70439
        'DescribeContentQuota' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeContentQuota',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#提交封禁任务: https://www.volcengine.com/docs/6454/79890
        'SubmitBlockTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitBlockTask',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#提交解封任务: https://www.volcengine.com/docs/6454/79893
        'SubmitUnblockTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SubmitUnblockTask',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取封禁解封任务信息: https://www.volcengine.com/docs/6454/79906
        'DescribeContentBlockTasks' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeContentBlockTasks',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取访问日志下载链接: https://www.volcengine.com/docs/6454/70446
        'DescribeCdnAccessLog' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnAccessLog',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取 IP 归属信息: https://www.volcengine.com/docs/6454/75233
        'DescribeIPInfo' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeIPInfo',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#获取回源节点 IP 列表: https://www.volcengine.com/docs/6454/75273
        'DescribeCdnUpperIp' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DescribeCdnUpperIp',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#添加资源标签: https://www.volcengine.com/docs/6454/80308
        'AddResourceTags' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'AddResourceTags',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#更新资源标签: https://www.volcengine.com/docs/6454/80313
        'UpdateResourceTags' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateResourceTags',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#查询标签清单: https://www.volcengine.com/docs/6454/80315
        'ListResourceTags' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListResourceTags',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
#删除资源标签: https://www.volcengine.com/docs/6454/80316
        'DeleteResourceTags' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteResourceTags',
                    'Version' => '2021-03-01',
                ],
            ]
        ],
    ];

    public function __construct()
    {
        $this->region = func_get_arg(0);
        parent::__construct($this->region);
    }

    protected function getConfig(string $region = 'cn-north-1')
    {
        return [
            'host' => "https://cdn.volcengineapi.com",
            'config' => [
                'timeout' => 60 * 5,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => $region,
                    'service' => 'CDN',
                ],
            ],
        ];
    }

    public function requestWithRetry(string $api, array $configs): string
    {
        try {
            $response = $this->request($api, $configs);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            $response = $this->request($api, $configs);
            return $response->getBody()->getContents();
        }
    }

    public function addCdnDomain(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("AddCdnDomain", ['json' => $data]);
    }

    public function startCdnDomain(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("StartCdnDomain", ['json' => $data]);
    }

    public function stopCdnDomain(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("StopCdnDomain", ['json' => $data]);
    }

    public function deleteCdnDomain(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DeleteCdnDomain", ['json' => $data]);
    }

    public function listCdnDomains(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("ListCdnDomains", ['json' => $data]);
    }

    public function describeCdnConfig(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnConfig", ['json' => $data]);
    }

    public function updateCdnConfig(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("UpdateCdnConfig", ['json' => $data]);
    }

    public function describeCdnData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnData", ['json' => $data]);
    }

    public function describeEdgeNrtDataSummary(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeEdgeNrtDataSummary", ['json' => $data]);
    }

    public function describeCdnOriginData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnOriginData", ['json' => $data]);
    }

    public function describeOriginNrtDataSummary(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeOriginNrtDataSummary", ['json' => $data]);
    }

    public function describeCdnDataDetail(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnDataDetail", ['json' => $data]);
    }

    public function describeEdgeStatisticalData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeEdgeStatisticalData", ['json' => $data]);
    }

    public function describeEdgeTopNrtData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeEdgeTopNrtData", ['json' => $data]);
    }

    public function describeOriginTopNrtData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeOriginTopNrtData", ['json' => $data]);
    }

    public function describeEdgeTopStatusCode(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeEdgeTopStatusCode", ['json' => $data]);
    }

    public function describeOriginTopStatusCode(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeOriginTopStatusCode", ['json' => $data]);
    }

    public function describeEdgeTopStatisticalData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeEdgeTopStatisticalData", ['json' => $data]);
    }

    public function describeCdnRegionAndIsp(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnRegionAndIsp", ['json' => $data]);
    }

    public function describeCdnDomainTopData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnDomainTopData", ['json' => $data]);
    }

    public function describeCdnService(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnService", ['json' => $data]);
    }

    public function describeAccountingData(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeAccountingData", ['json' => $data]);
    }

    public function submitRefreshTask(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("SubmitRefreshTask", ['json' => $data]);
    }

    public function submitPreloadTask(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("SubmitPreloadTask", ['json' => $data]);
    }

    public function describeContentTasks(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeContentTasks", ['json' => $data]);
    }

    public function describeContentQuota(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeContentQuota", ['json' => $data]);
    }

    public function submitBlockTask(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("SubmitBlockTask", ['json' => $data]);
    }

    public function submitUnblockTask(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("SubmitUnblockTask", ['json' => $data]);
    }

    public function describeContentBlockTasks(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeContentBlockTasks", ['json' => $data]);
    }

    public function describeCdnAccessLog(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnAccessLog", ['json' => $data]);
    }

    public function describeIPInfo(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeIPInfo", ['json' => $data]);
    }

    public function describeCdnUpperIp(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DescribeCdnUpperIp", ['json' => $data]);
    }

    public function addResourceTags(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("AddResourceTags", ['json' => $data]);
    }

    public function updateResourceTags(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("UpdateResourceTags", ['json' => $data]);
    }

    public function listResourceTags(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("ListResourceTags", ['json' => $data]);
    }

    public function deleteResourceTags(array $data = []): string
    {
        if (empty($data)) {
            $data = new \ArrayObject();
        }
        return $this->requestWithRetry("DeleteResourceTags", ['json' => $data]);
    }
}


