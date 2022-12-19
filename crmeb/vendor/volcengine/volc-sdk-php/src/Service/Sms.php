<?php


namespace Volc\Service;

use Volc\Base\V4Curl;

class Sms extends V4Curl
{

    protected function getConfig(string $region = '')
    {

        switch ($region){
            case 'ap-singapore-1':
                $host = 'https://sms.byteplusapi.com';
                $region = 'ap-singapore-1';
                break;
            default:
                $host = 'https://sms.volcengineapi.com';
                $region = 'cn-north-1';
        }
        return [
            'host' => $host,
            'config' => [
                'timeout' => 10.0,
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'v4_credentials' => [
                    'region' => $region,
                    'service' => 'volcSMS',
                ],
            ],
        ];
    }

    protected $apiList = [
        'SendSms' => [
            'url' => '/',
            'method' => 'post',
            'config' => [
                'query' => [
                    'Action' => 'SendSms',
                    'Version' => '2020-01-01',
                ],
            ]
        ],
    ];

    public function sendSms(array $query = [])
    {
        $response = $this->request('SendSms', $query);
        if ($response->getStatusCode() >= 500) {
            $response = $this->request('SendSms', $query);
        }
        return $response->getBody();
    }

}