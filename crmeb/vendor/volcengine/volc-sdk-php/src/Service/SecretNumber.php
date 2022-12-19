<?php

namespace Volc\Service;

use ArrayObject;
use Exception;
use Volc\Base\V4Curl;

class SecretNumber extends V4Curl
{

    /**
     * @throws Exception
     */
    protected function getConfig(string $region): array
    {
        // TODO: Implement getConfig() method.
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
                            'service' => 'volc_secret_number',
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
        'BindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXB',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'SelectNumberAndBindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SelectNumberAndBindAXB',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'UnbindAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXB',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'QuerySubscription' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QuerySubscription',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'QuerySubscriptionForList' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'QuerySubscriptionForList',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'UpgradeAXToAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpgradeAXToAXB',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'UpdateAXB' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXB',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'BindAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'BindAXN',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'UpdateAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UpdateAXN',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'UnbindAXN' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'UnbindAXN',
                    'Version' => '2020-09-01',
                ],
            ]
        ],

        'Click2Call' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Click2Call',
                    'Version' => '2021-09-01',
                ],
            ]
        ],

        'Click2CallLite' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'Click2CallLite',
                    'Version' => '2021-09-01',
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
}