<?php

namespace Volc\Service;

use ArrayObject;
use Exception;
use Volc\Base\V4Curl;

class Notify extends V4Curl
{
    /**
     * @throws Exception
     */
    protected function getConfig(string $region): array
    {
        switch ($region){
            case 'cn-north-1':
                $config = [
                    'host' => 'https://cloud-vms.volcengineapi.com',
                    'config' => [
                        'timeout' => 10.0,
                        'headers' => [
                            'Accept' => 'application/json'
                        ],
                        'v4_credentials' => [
                            'region' => 'cn-north-1',
                            'service' => 'volc_voice_notify',
                        ],
                    ],
                ];
                break;
            default:
                throw new Exception(sprintf("This region not support now, %s", $region));
        }
        return $config;
    }

    protected $apiList = [
        'CreateTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'BatchAppend' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BatchAppend',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'PauseTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'PauseTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'ResumeTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ResumeTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'StopTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'StopTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'UpdateTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateTask',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'SingleBatchAppend' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SingleBatchAppend',
                    'Version' => '2021-01-01',
                ],
            ]
        ],


        'SingleInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SingleInfo',
                    'Version' => '2021-01-01',
                ],
            ]
        ],


        'SingleCancel' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SingleCancel',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'FetchResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FetchResource',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'OpenCreateTts' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OpenCreateTts',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'OpenDeleteResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OpenDeleteResource',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'GetResourceUploadUrl' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetResourceUploadUrl',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'CommitResourceUpload' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CommitResourceUpload',
                    'Version' => '2021-01-01',
                ],
            ]
        ],

        'QueryOpenGetResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryOpenGetResource',
                    'Version' => '2021-01-01',
                ],
            ]
        ],
    ];

    protected function requestWithRetry(string $api, array $configs): string
    {
        try {
            $response = $this->request($api, $configs);
            return (string)$response->getBody();
        }
        catch (Exception $e)
        {
            $response = $this->request($api, $configs);
            return (string)$response->getBody();
        }
    }


    public function CreateTask(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("CreateTask", ['json' => $data]);
    }


    public function BatchAppend(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BatchAppend", ['json' => $data]);
    }


    public function  PauseTask(string $taskId): string
    {
        return $this->requestWithRetry("PauseTask", ['query' => ['TaskOpenId' => $taskId]]);
    }

    public function ResumeTask(string $taskId): string
    {
        return $this->requestWithRetry("ResumeTask", ['query' => ['TaskOpenId' => $taskId]]);
    }

    public function StopTask(string $taskId) : string
    {
        return $this->requestWithRetry("StopTask", ['query' => ['TaskOpenId' => $taskId]]);
    }

    public function UpdateTask(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateTask", ['json' => $data]);
    }

    public function SingleBatchAppend(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("SingleBatchAppend", ['json' => $data]);
    }

    public function SingleInfo(string $singleOpenId): string
    {
        return $this->requestWithRetry("SingleInfo", ['query' => ["SingleOpenId" => $singleOpenId]]);
    }

    public function SingleCancel(string $singleOpenId): string
    {
        return $this->requestWithRetry("SingleCancel", ['query' => ["SingleOpenId" => $singleOpenId]]);
    }

    public function FetchResource(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("FetchResource", ['json' => $data]);
    }

    public function  CreateTtsResource(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("OpenCreateTts", ['json' => $data]);
    }

    public function DeleteResourceByKey(string $resourceKey): string
    {
        return $this->requestWithRetry("OpenDeleteResource", ['query' => ['ResourceKey' => $resourceKey], 'json' => []]);
    }

    public function GetResourceUploadUrl(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("GetResourceUploadUrl", ['json' => $data]);
    }

    public function CommitResourceUpload(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("CommitResourceUpload", ['json' => $data]);
    }

    public function QueryResource(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QueryOpenGetResource", ['json' => $data]);
    }
}