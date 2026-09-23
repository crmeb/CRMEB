<?php

namespace Volc\Service;

use Volc\Base\V4Curl;

class Billing extends V4Curl
{
    protected function getConfig(string $region = '')
    {
        return [
            'host' => 'https://billing.volcengineapi.com',
            'config' => [
                'timeout' => 5.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => 'cn-north-1',
                    'service' => 'billing',
                ],
            ],
        ];
    }

    public function listBill(array $query = [])
    {        
        $response = $this->request('ListBill', $query);
        return $response->getBody();
    }

    public function listBillDetail(array $query = [])
    {
        $response = $this->request('ListBillDetail', $query);
        return $response->getBody();
    }

    public function listBillOverviewByProd(array $query = [])
    {
        $response = $this->request('ListBillOverviewByProd', $query);
        return $response->getBody();
    }

    protected $apiList = [
        'ListBill' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListBill',
                    'Version' => '2022-01-01',
                ],
            ],
        ],
        'ListBillDetail' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListBillDetail',
                    'Version' => '2022-01-01',
                ],
            ],
        ],
        'ListBillOverviewByProd' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'ListBillOverviewByProd',
                    'Version' => '2022-01-01',
                ],
            ],
        ],
    ];
}
