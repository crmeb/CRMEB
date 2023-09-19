<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\services\upload\extend\jdoss;


use crmeb\exceptions\UploadException;
use crmeb\services\upload\BaseClient;

/**
 * 京东云上传
 * Class Client
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/5/18
 * @package crmeb\services\upload\extend\jdoss
 */
class Client extends BaseClient
{
    /**
     * AK
     * @var
     */
    protected $accessKeyId;

    /**
     * SK
     * @var
     */
    protected $secretKey;

    /**
     * 桶名
     * @var string
     */
    protected $bucketName;

    /**
     * 地区
     * @var string
     */
    protected $region;

    /**
     * @var mixed|string
     */
    protected $uploadUrl;

    /**
     * @var string
     */
    protected $baseUrl = 's3.<REGION>.jdcloud-oss.com';

    //默认地域
    const DEFAULT_REGION = 'cn-north-1';

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->accessKeyId = $config['accessKey'] ?? '';
        $this->secretKey = $config['secretKey'] ?? '';
        $this->bucketName = $config['bucket'] ?? '';
        $this->region = $config['region'] ?? self::DEFAULT_REGION;
        $this->uploadUrl = $config['uploadUrl'] ?? '';
    }

    /**
     * 检测桶，不存在返回true
     * @param string $bucket
     * @param string $region
     * @return array|bool|\crmeb\services\upload\extend\cos\SimpleXMLElement
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/17
     */
    public function headBucket(string $bucket, string $region = '')
    {
        $url = $this->getRequestUrl($bucket, $region);

        $header = [
            'Host' => $url
        ];

        return $this->request('https://' . $url, 'head', [], $header);
    }


    /**
     * 获取桶列表
     * @return array|\crmeb\services\upload\extend\cos\SimpleXMLElement
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function listBuckets()
    {
        $url = $this->getRequestUrl();
        $header = [
            'Host' => $url,
            'Date' => gmdate('D, d M Y H:i:s \G\M\T')
        ];

        $res = $this->request('https://' . $url . '/', 'GET', [], $header);

        return $res;
    }

    public function createBucket($name, $region, $acl)
    {
        $url = $this->getRequestUrl($name, $region);
        $header = [
            'Host' => $url,
            'Date' => gmdate('D, d M Y H:i:s \G\M\T'),
            'name' => $name,
            'x-amz-acl' => $acl
        ];


        $res = $this->request('https://' . $url . '/', 'PUT', [], $header);

        return $res;
    }

    /**
     * 获取请求域名
     * @param string $bucket
     * @param string $region
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function getRequestUrl(string $bucket = '', string $region = self::DEFAULT_REGION)
    {
        if (!$this->accessKeyId) {
            throw new UploadException('请传入SecretId');
        }
        if (!$this->secretKey) {
            throw new UploadException('请传入SecretKey');
        }

        return ($bucket ? $bucket . '.' : '') . 's3.' . $region . '.jdcloud-oss.com';
    }

    /**
     * 发起请求
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $clientHeader
     * @param int $timeout
     * @return array|\crmeb\services\upload\extend\cos\SimpleXMLElement
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function request(string $url, string $method, array $data = [], array $clientHeader = [], int $timeout = 10)
    {
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = '';
        $signedHeaders = '';
        $payload = '';

        $urlAttr = pathinfo($url);
        if ($urlAttr['dirname'] !== 'https:') {
            $urlParse = parse_url($urlAttr['dirname'] ?? '');
            if (isset($urlParse['path'])) {
                $canonicalUri .= substr($urlParse['path'], 1) . '/';
            }
            if (isset($urlAttr['basename'])) {
                $canonicalUri .= $urlAttr['basename'];
            }
            if (!($pos = strripos($canonicalUri, '/')) || strlen($canonicalUri) - 1 !== $pos) {
                $canonicalUri .= '/';
            }
        }

        if (!empty($data['query'])) {
            $query = $payload = $data['query'];
            ksort($query);
            $queryAttr = [];
            foreach ($query as $key => $item) {
                $queryAttr[urlencode($key)] = urlencode($item);
            }
            if ($queryAttr) {
                $canonicalQueryString = implode('&', $queryAttr);
            }
        } elseif (!empty($data['body'])) {
            $payload = $data['body'];
        } elseif (!empty($data['json'])) {
            $payload = json_encode($data['json']);
        }

        if ($clientHeader) {
            $canonicalHeadersAtrr = $signedHeadersAttr = [];
            ksort($clientHeader);
            foreach ($clientHeader as $key => $item) {
                $canonicalHeadersAtrr[] = strtolower($key) . ':' . trim($item);
                $signedHeadersAttr[] = strtolower($key);
            }
            if ($canonicalHeadersAtrr) {
                $canonicalHeaders = implode("\n", $canonicalHeadersAtrr);
                $signedHeaders = implode(';', $signedHeadersAttr);
            }
        }

        $clientHeader['Authorization'] = $this->generateAwsSignatureV4(
            $data['region'] ?? self::DEFAULT_REGION,
            $method,
            $canonicalUri,
            $canonicalQueryString,
            $canonicalHeaders,
            $signedHeaders,
            $payload);

        return $this->requestClient($url, $method, $data, $clientHeader, $timeout);
    }

    /**
     * 生成签名
     * @param string $region
     * @param string $httpMethod
     * @param string $canonicalUri
     * @param string $canonicalQueryString
     * @param string $canonicalHeaders
     * @param string $signedHeaders
     * @param $payload
     * @param string $service
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function generateAwsSignatureV4(string $region, string $httpMethod, string $canonicalUri, string $canonicalQueryString, string $canonicalHeaders, string $signedHeaders, $payload, string $service = 's3')
    {
        $algorithm = 'AWS4-HMAC-SHA256';
        $t = new \DateTime('UTC');
        $amzDate = $t->format('Ymd\THis\Z');
        $dateStamp = $t->format('Ymd');

        $canonicalRequest = $httpMethod . "\n" . $canonicalUri . "\n" . $canonicalQueryString . "\n" . $canonicalHeaders . "\n" . $signedHeaders . "\n" . hash('sha256', $payload);
        $credentialScope = $dateStamp . '/' . $region . '/' . $service . '/aws4_request';
        dump(compact('region', 'httpMethod', 'canonicalUri', 'canonicalQueryString', 'canonicalHeaders', 'signedHeaders', 'payload', 'service'));
        $stringToSign = $algorithm . "\n" . $amzDate . "\n" . $credentialScope . "\n" . hash('sha256', $canonicalRequest);
        $signingKey = hash_hmac('sha256', 'aws4_request',
            hash_hmac('sha256', $service,
                hash_hmac('sha256', $region,
                    hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true),
                    true),
                true),
            true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return $algorithm . ' Credential=' . $this->accessKeyId . '/' . $credentialScope . ', SignedHeaders=' . $signedHeaders . ', Signature=' . $signature;
    }

    public function getRegion()
    {
        return [
            [
                'value' => 'cn-north-1',
                'label' => '华北-北京',
            ],
            [
                'value' => 'cn-south-1',
                'label' => '华南-广州',
            ],
            [
                'value' => 'cn-east-2',
                'label' => '华东-上海',
            ],
            [
                'value' => 'cn-east-1',
                'label' => '华东-宿迁',
            ]
        ];
    }
}
