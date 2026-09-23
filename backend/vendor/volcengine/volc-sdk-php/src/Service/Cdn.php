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

        #获取多个域名的省份和运营商的细分数据: https://www.volcengine.com/docs/6454/145577
        'DescribeDistrictIspData' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeDistrictIspData',
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

        #获取服务相关信息: https://www.volcengine.com/docs/6454/78999
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

        #批量获取 IP 归属信息: https://www.volcengine.com/docs/6454/106852
        'DescribeIPListInfo' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeIPListInfo',
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

        #上传证书: https://www.volcengine.com/docs/6454/125708
        'AddCdnCertificate' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'AddCdnCertificate',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #查询CDN证书列表: https://www.volcengine.com/docs/6454/125709
        'ListCertInfo' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'ListCertInfo',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #查询CDN有关联域名的证书列表: https://www.volcengine.com/docs/6454/125710
        'ListCdnCertInfo' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'ListCdnCertInfo',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取特定证书的域名关联信息: https://www.volcengine.com/docs/6454/125711
        'DescribeCertConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeCertConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #批量关联证书: https://www.volcengine.com/docs/6454/125712
        'BatchDeployCert' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'BatchDeployCert',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #删除托管在内容分发网络的证书: https://www.volcengine.com/docs/6454/597589
        'DeleteCdnCertificate' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DeleteCdnCertificate',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #查询计费结果数据: 
        'DescribeAccountingSummary' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeAccountingSummary',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取访问统计的细分数据: 
        'DescribeDistrictData' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeDistrictData',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取计费区域的细分数据: 
        'DescribeEdgeData' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeEdgeData',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取访问统计的汇总数据: 
        'DescribeDistrictSummary' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeDistrictSummary',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取计费区域的汇总数据: 
        'DescribeEdgeSummary' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeEdgeSummary',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取回源统计的细分数据: 
        'DescribeOriginData' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeOriginData',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取回源统计的汇总数据: 
        'DescribeOriginSummary' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeOriginSummary',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取独立访客的细分数据: 
        'DescribeUserData' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeUserData',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取访问数据的统计排名: 
        'DescribeDistrictRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeDistrictRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取计费区域的统计排名: 
        'DescribeEdgeRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeEdgeRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取回源数据的统计排名: 
        'DescribeOriginRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeOriginRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取访问状态码排名数据: 
        'DescribeEdgeStatusCodeRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeEdgeStatusCodeRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取回源状态码的统计排名: 
        'DescribeOriginStatusCodeRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeOriginStatusCodeRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取热门对象的统计排名: 
        'DescribeStatisticalRanking' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeStatisticalRanking',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #批量更新加速域名: 
        'BatchUpdateCdnConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'BatchUpdateCdnConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #上传证书新版: 
        'AddCertificate' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'AddCertificate',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #删除用量导出任务: 
        'DeleteUsageReport' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DeleteUsageReport',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #创建用量导出任务: 
        'CreateUsageReport' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'CreateUsageReport',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #获取用量导出任务列表: 
        'ListUsageReports' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'ListUsageReports',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #查询全局配置: 
        'DescribeSharedConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeSharedConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #查询全局配置列表: 
        'ListSharedConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'ListSharedConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #删除全局配置: 
        'DeleteSharedConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DeleteSharedConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #修改全局配置: 
        'UpdateSharedConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'UpdateSharedConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #新增全局配置: 
        'AddSharedConfig' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'AddSharedConfig',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #接入域名校验: 
        'CheckDomain' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'CheckDomain',
                  'Version' => '2021-03-01',
              ],
          ]
        ],

        #DNS校验信息生成: 
        'DescribeRetrieveInfo' => [
          'url' => '/',
          'method' => 'post',
          'config' => [
              'query' => [
                  'Action' => 'DescribeRetrieveInfo',
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
    public function useGet(): string
    {
        return 'get';
    }

    public function usePost(): string
    {
        return 'post';
    }

    public function requestWithRetry(string $api, array $configs, string $option='post'): string
    {
        try {
            $flag = false;
            if (empty($configs) && $option == $this->usePost()) {
                $configs = new \ArrayObject();
            }
            $data = ["json" => $configs];
            if ($option == 'get') {
                $this->apiList[$api]['method'] = 'get';
                $flag = true;
                $data = ["query" => $configs];
            }
            $response = $this->request($api, $data);
            if ($flag == true) {
                $this->apiList[$api]['method'] = 'get';
            }
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            $response = $this->request($api, $data);
            return $response->getBody()->getContents();
        }
    }

    public function addCdnDomain(array $data = []): string
    {
        return $this->requestWithRetry("AddCdnDomain", $data);
    }

    public function startCdnDomain(array $data = []): string
    {
        return $this->requestWithRetry("StartCdnDomain", $data);
    }

    public function stopCdnDomain(array $data = []): string
    {
        return $this->requestWithRetry("StopCdnDomain", $data);
    }

    public function deleteCdnDomain(array $data = []): string
    {
        return $this->requestWithRetry("DeleteCdnDomain", $data);
    }

    public function listCdnDomains(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("ListCdnDomains", $data, $option);
    }

    public function describeCdnConfig(array $data = []): string
    {
        return $this->requestWithRetry("DescribeCdnConfig", $data);
    }

    public function updateCdnConfig(array $data = []): string
    {
        return $this->requestWithRetry("UpdateCdnConfig", $data);
    }

    public function describeCdnData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeCdnData", $data, $option);
    }

    public function describeEdgeNrtDataSummary(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeEdgeNrtDataSummary", $data, $option);
    }

    public function describeCdnOriginData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeCdnOriginData", $data, $option);
    }

    public function describeOriginNrtDataSummary(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeOriginNrtDataSummary", $data, $option);
    }

    public function describeCdnDataDetail(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeCdnDataDetail", $data, $option);
    }

    public function describeDistrictIspData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeDistrictIspData", $data, $option);
    }

    public function describeEdgeStatisticalData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeEdgeStatisticalData", $data, $option);
    }

    public function describeEdgeTopNrtData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeEdgeTopNrtData", $data, $option);
    }

    public function describeOriginTopNrtData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeOriginTopNrtData", $data, $option);
    }

    public function describeEdgeTopStatusCode(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeEdgeTopStatusCode", $data, $option);
    }

    public function describeOriginTopStatusCode(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeOriginTopStatusCode", $data, $option);
    }

    public function describeEdgeTopStatisticalData(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeEdgeTopStatisticalData", $data, $option);
    }

    public function describeCdnRegionAndIsp(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeCdnRegionAndIsp", $data, $option);
    }

    public function describeCdnService(): string
    {
        $data = [];
        return $this->requestWithRetry("DescribeCdnService", $data);
    }

    public function describeAccountingData(array $data = []): string
    {
        return $this->requestWithRetry("DescribeAccountingData", $data);
    }

    public function submitRefreshTask(array $data = []): string
    {
        return $this->requestWithRetry("SubmitRefreshTask", $data);
    }

    public function submitPreloadTask(array $data = []): string
    {
        return $this->requestWithRetry("SubmitPreloadTask", $data);
    }

    public function describeContentTasks(array $data = []): string
    {
        return $this->requestWithRetry("DescribeContentTasks", $data);
    }

    public function describeContentQuota(string $option='post'): string
    {
        $data = [];
        return $this->requestWithRetry("DescribeContentQuota", $data, $option);
    }

    public function submitBlockTask(array $data = []): string
    {
        return $this->requestWithRetry("SubmitBlockTask", $data);
    }

    public function submitUnblockTask(array $data = []): string
    {
        return $this->requestWithRetry("SubmitUnblockTask", $data);
    }

    public function describeContentBlockTasks(array $data = []): string
    {
        return $this->requestWithRetry("DescribeContentBlockTasks", $data);
    }

    public function describeCdnAccessLog(array $data = [], string $option='post'): string
    {
        return $this->requestWithRetry("DescribeCdnAccessLog", $data, $option);
    }

    public function describeIPInfo(array $data = []): string
    {
        return $this->requestWithRetry("DescribeIPInfo", $data);
    }

    public function describeIPListInfo(array $data = []): string
    {
        return $this->requestWithRetry("DescribeIPListInfo", $data);
    }

    public function describeCdnUpperIp(array $data = []): string
    {
        return $this->requestWithRetry("DescribeCdnUpperIp", $data);
    }

    public function addResourceTags(array $data = []): string
    {
        return $this->requestWithRetry("AddResourceTags", $data);
    }

    public function updateResourceTags(array $data = []): string
    {
        return $this->requestWithRetry("UpdateResourceTags", $data);
    }

    public function listResourceTags(): string
    {
        $data = [];
        return $this->requestWithRetry("ListResourceTags", $data);
    }

    public function deleteResourceTags(array $data = []): string
    {
        return $this->requestWithRetry("DeleteResourceTags", $data);
    }

    public function addCdnCertificate(array $data = []): string
    {
        return $this->requestWithRetry("AddCdnCertificate", $data);
    }

    public function listCertInfo(array $data = []): string
    {
        return $this->requestWithRetry("ListCertInfo", $data);
    }

    public function listCdnCertInfo(array $data = []): string
    {
        return $this->requestWithRetry("ListCdnCertInfo", $data);
    }

    public function describeCertConfig(array $data = []): string
    {
        return $this->requestWithRetry("DescribeCertConfig", $data);
    }

    public function batchDeployCert(array $data = []): string
    {
        return $this->requestWithRetry("BatchDeployCert", $data);
    }

    public function deleteCdnCertificate(array $data = []): string
    {
        return $this->requestWithRetry("DeleteCdnCertificate", $data);
    }

    public function describeAccountingSummary(array $data = []): string
    {
        return $this->requestWithRetry("DescribeAccountingSummary", $data);
    }

    public function describeDistrictData(array $data = []): string
    {
        return $this->requestWithRetry("DescribeDistrictData", $data);
    }

    public function describeEdgeData(array $data = []): string
    {
        return $this->requestWithRetry("DescribeEdgeData", $data);
    }

    public function describeDistrictSummary(array $data = []): string
    {
        return $this->requestWithRetry("DescribeDistrictSummary", $data);
    }

    public function describeEdgeSummary(array $data = []): string
    {
        return $this->requestWithRetry("DescribeEdgeSummary", $data);
    }

    public function describeOriginData(array $data = []): string
    {
        return $this->requestWithRetry("DescribeOriginData", $data);
    }

    public function describeOriginSummary(array $data = []): string
    {
        return $this->requestWithRetry("DescribeOriginSummary", $data);
    }

    public function describeUserData(array $data = []): string
    {
        return $this->requestWithRetry("DescribeUserData", $data);
    }

    public function describeDistrictRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeDistrictRanking", $data);
    }

    public function describeEdgeRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeEdgeRanking", $data);
    }

    public function describeOriginRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeOriginRanking", $data);
    }

    public function describeEdgeStatusCodeRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeEdgeStatusCodeRanking", $data);
    }

    public function describeOriginStatusCodeRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeOriginStatusCodeRanking", $data);
    }

    public function describeStatisticalRanking(array $data = []): string
    {
        return $this->requestWithRetry("DescribeStatisticalRanking", $data);
    }

    public function batchUpdateCdnConfig(array $data = []): string
    {
        return $this->requestWithRetry("BatchUpdateCdnConfig", $data);
    }

    public function addCertificate(array $data = []): string
    {
        return $this->requestWithRetry("AddCertificate", $data);
    }

    public function deleteUsageReport(array $data = []): string
    {
        return $this->requestWithRetry("DeleteUsageReport", $data);
    }

    public function createUsageReport(array $data = []): string
    {
        return $this->requestWithRetry("CreateUsageReport", $data);
    }

    public function listUsageReports(array $data = []): string
    {
        return $this->requestWithRetry("ListUsageReports", $data);
    }

    public function describeSharedConfig(array $data = []): string
    {
        return $this->requestWithRetry("DescribeSharedConfig", $data);
    }

    public function listSharedConfig(array $data = []): string
    {
        return $this->requestWithRetry("ListSharedConfig", $data);
    }

    public function deleteSharedConfig(array $data = []): string
    {
        return $this->requestWithRetry("DeleteSharedConfig", $data);
    }

    public function updateSharedConfig(array $data = []): string
    {
        return $this->requestWithRetry("UpdateSharedConfig", $data);
    }

    public function addSharedConfig(array $data = []): string
    {
        return $this->requestWithRetry("AddSharedConfig", $data);
    }

    public function checkDomain(array $data = []): string
    {
        return $this->requestWithRetry("CheckDomain", $data);
    }

    public function describeRetrieveInfo(array $data = []): string
    {
        return $this->requestWithRetry("DescribeRetrieveInfo", $data);
    }

}
