<?php

namespace Volc\Service\Vms;

use ArrayObject;
use Exception;
use Volc\Base\V4Curl;

const DefaultVersion = "2022-01-01";

class Vms extends V4Curl
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
                            'service' => 'vms',
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
        'QueryCallRecordMsg' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryCallRecordMsg',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QueryAudioRecordFileUrl' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryAudioRecordFileUrl',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QueryAudioRecordToTextFileUrl' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryAudioRecordToTextFileUrl',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QueryCanCall' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryCanCall',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'NumberPoolList' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'NumberPoolList',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'NumberList' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'NumberList',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'CreateNumberPool' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateNumberPool',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateNumberPool' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateNumberPool',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'EnableOrDisableNumber' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'EnableOrDisableNumber',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'SelectNumber' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SelectNumber',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'SelectNumberAndBindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SelectNumberAndBindAXB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UnbindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QuerySubscription' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QuerySubscription',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QuerySubscriptionForList' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QuerySubscriptionForList',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpgradeAXToAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpgradeAXToAXB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXN',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXN',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UnbindAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXN',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'Click2Call' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Click2Call',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'Click2CallLite' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Click2CallLite',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'SelectNumberAndBindAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SelectNumberAndBindAXN',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXNE' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXNE',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UnbindAXNE' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXNE',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXNE' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXNE',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXBForAXNE' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXBForAXNE',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXYB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXYB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindYBForAXYB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindYBForAXYB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXYB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXYB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UnbindAXYB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXYB',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BindAXG' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXG',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UnbindAXG' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXG',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXG' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXG',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'CreateAXGGroup' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateAXGGroup',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateAXGGroup' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXGGroup',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'DeleteAXGGroup' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'DeleteAXGGroup',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'RouteAAuth' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'RouteAAuth',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'CreateTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CreateTask',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'BatchAppend' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BatchAppend',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'PauseTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'PauseTask',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'ResumeTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'ResumeTask',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'StopTask' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'StopTask',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'UpdateTask' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateTask',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'SingleBatchAppend' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SingleBatchAppend',
                    'Version' => DefaultVersion,
                ],
            ]
        ],


        'SingleInfo' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SingleInfo',
                    'Version' => DefaultVersion,
                ],
            ]
        ],


        'SingleCancel' => [
            'url' => '/',
            'method' => 'get',
            'config' => [
                'query' => [
                    'Action' => 'SingleCancel',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'FetchResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'FetchResource',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'OpenCreateTts' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OpenCreateTts',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'OpenDeleteResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OpenDeleteResource',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'GetResourceUploadUrl' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'GetResourceUploadUrl',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'CommitResourceUpload' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'CommitResourceUpload',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QueryOpenGetResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryOpenGetResource',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'OpenUpdateResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'OpenUpdateResource',
                    'Version' => DefaultVersion,
                ],
            ]
        ],

        'QueryUsableResource' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QueryUsableResource',
                    'Version' => DefaultVersion,
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

    public function QueryCallRecordMsg(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QueryCallRecordMsg", ['form_params' => $data]);
    }

    public function QueryAudioRecordFileUrl(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QueryAudioRecordFileUrl", ['form_params' => $data]);
    }

    public function QueryAudioRecordToTextFileUrl(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QueryAudioRecordToTextFileUrl", ['form_params' => $data]);
    }

    public function QueryCanCall(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QueryCanCall", ['form_params' => $data]);
    }

    public function NumberPoolList(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("NumberPoolList", ['form_params' => $data]);
    }

    public function NumberList(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("NumberList", ['query' => $data]);
    }

    public function CreateNumberPool(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("CreateNumberPool", ['form_params' => $data]);
    }

    public function UpdateNumberPool(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateNumberPool", ['form_params' => $data]);
    }

    public function EnableOrDisableNumber(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("EnableOrDisableNumber", ['form_params' => $data]);
    }

    public function SelectNumber(array $data = []): string
    {
        if (empty($data)) {
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("SelectNumber", ['query' => $data]);
    }

    public function BindAXB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXB", ['form_params' => $data]);
    }

    public function SelectNumberAndBindAXB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("SelectNumberAndBindAXB", ['form_params' => $data]);
    }

    public function UnbindAXB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UnbindAXB", ['form_params' => $data]);
    }

    public function QuerySubscription(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QuerySubscription", ['form_params' => $data]);
    }

    public function QuerySubscriptionForList(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("QuerySubscriptionForList", ['form_params' => $data]);
    }

    public function UpgradeAXToAXB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpgradeAXToAXB", ['form_params' => $data]);
    }

    public function UpdateAXB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXB", ['form_params' => $data]);
    }

    public function BindAXN(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXN", ['form_params' => $data]);
    }

    public function UpdateAXN(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXN", ['form_params' => $data]);
    }

    public function UnbindAXN(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UnbindAXN", ['form_params' => $data]);
    }

    public function Click2Call(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("Click2Call", ['form_params' => $data]);
    }

    public function Click2CallLite(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("Click2CallLite", ['form_params' => $data]);
    }

    public function SelectNumberAndBindAXN(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("SelectNumberAndBindAXN", ['form_params' => $data]);
    }

    public function BindAXNE(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXNE", ['form_params' => $data]);
    }

    public function UnbindAXNE(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UnbindAXNE", ['form_params' => $data]);
    }

    public function UpdateAXNE(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXNE", ['form_params' => $data]);
    }

    public function BindAXBForAXNE(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXBForAXNE", ['form_params' => $data]);
    }

    public function BindAXYB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXYB", ['form_params' => $data]);
    }

    public function BindYBForAXYB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindYBForAXYB", ['form_params' => $data]);
    }

    public function UpdateAXYB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXYB", ['form_params' => $data]);
    }

    public function UnbindAXYB(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UnbindAXYB", ['form_params' => $data]);
    }

    public function BindAXG(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("BindAXG", ['json' => $data]);
    }

    public function UnbindAXG(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UnbindAXG", ['json' => $data]);
    }

    public function UpdateAXG(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXG", ['json' => $data]);
    }

    public function CreateAXGGroup(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("CreateAXGGroup", ['json' => $data]);
    }

    public function UpdateAXGGroup(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("UpdateAXGGroup", ['json' => $data]);
    }

    public function DeleteAXGGroup(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("DeleteAXGGroup", ['json' => $data]);
    }

    public function RouteAAuth(array $data = []): string
    {
        if (empty($data)){
            $data = new ArrayObject();
        }
        return $this->requestWithRetry("RouteAAuth", ['form_params' => $data]);
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

    public function QueryUsableResource(int $type): string
    {
        return $this->requestWithRetry("QueryUsableResource", ['query' => ['Type' => $type]]);
    }

    public function OpenUpdateResource(string $resourceKey, string $name): string
    {
        return $this->requestWithRetry("OpenUpdateResource", ['query' => ['ResourceKey' => $resourceKey, 'Name' => $name]]);
    }
}