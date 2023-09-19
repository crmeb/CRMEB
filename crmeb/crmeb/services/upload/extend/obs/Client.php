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

namespace crmeb\services\upload\extend\obs;


use crmeb\exceptions\UploadException;
use crmeb\services\upload\BaseClient;
use crmeb\services\upload\extend\cos\XML;

/**
 * 华为云上传
 * Class Client
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/5/18
 * @package crmeb\services\upload\extend\obs
 */
class Client extends BaseClient
{
    const HEADER_PREFIX = 'x-obs-';

    const INTEREST_HEADER_KEY_LIST = ['content-type', 'content-md5', 'date'];

    const ALTERNATIVE_DATE_HEADER = 'x-obs-date';

    const ALLOWED_RESOURCE_PARAMTER_NAMES = [
        'acl',
        'policy',
        'torrent',
        'logging',
        'location',
        'storageinfo',
        'quota',
        'storagepolicy',
        'requestpayment',
        'versions',
        'versioning',
        'versionid',
        'uploads',
        'uploadid',
        'partnumber',
        'website',
        'notification',
        'lifecycle',
        'deletebucket',
        'delete',
        'cors',
        'restore',
        'tagging',
        'response-content-type',
        'response-content-language',
        'response-expires',
        'response-cache-control',
        'response-content-disposition',
        'response-content-encoding',
        'x-image-process',

        'backtosource',
        'storageclass',
        'replication',
        'append',
        'position',
        'x-oss-process'
    ];

    //桶acl
    const OBS_ACL = [
        [
            'value' => 'public-read',
            'label' => '公共读(推荐)',
        ],
        [
            'value' => 'public-read-write',
            'label' => '公共读写',
        ],
    ];
    //默认acl
    const DEFAULT_OBS_ACL = 'public-read';

    protected $isCname = false;

    protected $pathStyle;

    /**
     * @var
     */
    protected $accessKeyId;

    /**
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
    protected $baseUrl = 'obs.cn-north-1.myhuaweicloud.com';

    protected $type = 'hw';

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->accessKeyId = $config['accessKey'] ?? '';
        $this->secretKey = $config['secretKey'] ?? '';
        $this->bucketName = $config['bucket'] ?? '';
        $this->region = $config['region'] ?? 'ap-chengdu';
        $this->uploadUrl = $config['uploadUrl'] ?? '';
        $this->type = $config['type'] ?? 'hw';
    }

    /**
     * 检测
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function checkOptions()
    {
        if (!$this->bucketName) {
            throw new UploadException('请传入桶名');
        }
        if (!$this->region) {
            throw new UploadException('请传入所属地域');
        }
        if (!$this->accessKeyId) {
            throw new UploadException('请传入SecretId');
        }
        if (!$this->secretKey) {
            throw new UploadException('请传入SecretKey');
        }

        return $this;
    }

    /**
     * 上传图片
     * @param string $key
     * @param $body
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function putObject(string $key, $body, string $contentType = 'image/jpeg')
    {
        $header = [
            'Host' => $this->getRequestUrl($this->bucketName, $this->region),
            'Content-Type' => $contentType,
            'Content-Length' => strlen($body),
        ];

        $res = $this->checkOptions()->request('https://' . $header['Host'] . '/' . $key, 'PUT', [
            'bucket' => $this->bucketName,
            'body' => $body
        ], $header);

        return $this->response($res);
    }

    /**
     * 删除上传对象
     * @param string $key
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function deleteObject(string $key)
    {
        $header = [
            'Host' => $this->getRequestUrl($this->bucketName, $this->region),
        ];

        $res = $this->request('https://' . $header['Host'] . '/' . $key, 'DELETE', [
            'bucket' => $this->bucketName
        ], $header);

        return $this->response($res);
    }

    /**
     * 获取桶
     * @return false|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    public function listBuckets()
    {
        $header = [
            'Host' => $this->getRequestUrl('', $this->region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/', 'GET', [], []);
        return $this->response($res);
    }

    public function headBucket(string $bucket, string $region)
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/', 'HEAD', [], []);
        return $this->response($res);
    }

    /**
     * 设置桶的策略
     * @param string $bucket
     * @param string $region
     * @param array $data
     * @return mixed
     *
     * @date 2023/06/08
     * @author yyw
     */
    public function putPolicy(string $bucket, string $region, array $data)
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
            "Content-Type" => "application/json"
        ];
        $res = $this->request('https://' . $header['Host'] . '/?policy', 'PUT', [
            'bucket' => $bucket,
            'json' => $data
        ], $header);

        return $this->response($res);
    }

    /**
     * 创建桶
     * @param string $bucket
     * @param string $region
     * @param string $acl
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function createBucket(string $bucket, string $region, string $acl = self::DEFAULT_OBS_ACL)
    {
        $header = [
            'x-obs-acl' => $acl,
            'Host' => $this->getRequestUrl($bucket, $region),
            "Content-Type" => "application/xml"
        ];
        $xml = "<CreateBucketConfiguration><Location>{$region}</Location></CreateBucketConfiguration>";
        $res = $this->request('https://' . $header['Host'] . '/', 'PUT', [
            'bucket' => $bucket,
            'body' => $xml
        ], $header);

        return $this->response($res);
    }

    /**
     * 删除桶
     * @param string $bucket
     * @param string $region
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function deleteBucket(string $bucket, string $region)
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/', 'DELETE', [
            'bucket' => $bucket
        ], $header);

        return $this->response($res);
    }

    /**
     * 获取桶的自定义域名
     * @param string $bucket
     * @param string $region
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function getBucketDomain(string $bucket, string $region)
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/?customdomain', 'GET', [
            'bucket' => $bucket
        ], $header);

        return $this->response($res);
    }

    /**
     * 设置桶的自定义域名
     * @param string $bucket
     * @param string $region
     * @param array $data
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function putBucketDomain(string $bucket, string $region, array $data = [])
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/?customdomain=' . $data['domainname'], 'PUT', [
            'bucket' => $bucket
        ], $header);

        return $this->response($res);
    }

    /**
     * 设置跨域
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    public function putBucketCors(string $bucket, string $region, array $data = [])
    {
        $xml = $this->xmlBuild($data, 'CORSConfiguration', 'CORSRule');
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
            'Content-Type' => 'application/xml',
            'Content-Length' => strlen($xml),
            'Content-MD5' => base64_encode(md5($xml, true))
        ];
        $res = $this->request('https://' . $header['Host'] . '/?cors', 'PUT', [
            'bucket' => $bucket,
            'body' => $xml
        ], $header);

        return $this->response($res);
    }

    /**
     * 删除跨域
     * @param string $bucket
     * @param string $region
     * @return mixed
     *
     * @date 2023/06/08
     * @author yyw
     */
    public function deleteBucketCors(string $bucket, string $region)
    {
        $header = [
            'Host' => $this->getRequestUrl($bucket, $region),
        ];
        $res = $this->request('https://' . $header['Host'] . '/?cors', 'DELETE', [
            'bucket' => $bucket,
        ], $header);

        return $this->response($res);
    }

    /**
     * @param $res
     * @return mixed
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/18
     */
    protected function response($res)
    {
        if (!empty($res['Code']) && !empty($res['Message'])) {
            throw new UploadException($res['Message']);
        }
        return $res;
    }

    /**
     * 获取请求域名
     * @param string $bucket
     * @param string $region
     * @return string
     *
     * @date 2023/06/08
     * @author yyw
     */
    protected function getRequestUrl(string $bucket = '', string $region = '')
    {
        if ($this->type == 'hw') {
            $url = '.myhuaweicloud.com';  // 华为
        } else {
            $url = '.ctyun.cn';  // 天翼
        }
        if ($bucket) {
            return $bucket . '.obs.' . $region . $url;
        } else {
            return 'obs.' . $region . $url;
        }
    }


    /**
     * 地域名称
     * @return \string[][]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/17
     */
    public function getRegion()
    {
        return [
            [
                'value' => 'cn-north-1',
                'label' => '华北-北京一',
            ],
//            [
//                'value' => 'cn-north-4',
//                'label' => '华北-北京四',
//            ],
            [
                'value' => 'cn-north-9',
                'label' => '华北-乌兰察布一',
            ],
            [
                'value' => 'cn-east-2',
                'label' => '华东-上海二',
            ],
            [
                'value' => 'cn-east-3',
                'label' => '华东-上海一',
            ],
            [
                'value' => 'cn-south-1',
                'label' => '华南-广州',
            ],
            [
                'value' => 'ap-southeast-1',
                'label' => '中国-香港',
            ],
            [
                'value' => 'cn-south-4',
                'label' => '华南-广州-友好用户环境',
            ],
            [
                'value' => 'cn-southwest-2',
                'label' => '西南-贵阳一',
            ],
            [
                'value' => 'la-north-2',
                'label' => '拉美-墨西哥城二',
            ],
            [
                'value' => 'na-mexico-1',
                'label' => '拉美-墨西哥城一',
            ],
            [
                'value' => 'sa-brazil-1',
                'label' => '拉美-圣保罗一',
            ],
            [
                'value' => 'la-south-2',
                'label' => '拉美-圣地亚哥',
            ],
            [
                'value' => 'tr-west-1',
                'label' => '土耳其-伊斯坦布尔',
            ],
            [
                'value' => 'ap-southeast-2',
                'label' => '亚太-曼谷',
            ],
            [
                'value' => 'ap-southeast-3',
                'label' => '亚太-新加坡',
            ],
            [
                'value' => 'af-south-1',
                'label' => '非洲-约翰内斯堡',
            ]
        ];
    }

    /**
     * 设置桶名
     * @param string $bucketName
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    public function setBucketName(string $bucketName)
    {
        $this->bucketName = $bucketName;
        return $this;
    }


    /**
     * 获取签名
     * @param array $result
     * @return array
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/17
     */
    protected function getSign(array $result)
    {
        $result['headers']['Date'] = gmdate('D, d M Y H:i:s \G\M\T');
        $canonicalstring = $this->makeCanonicalstring($result['method'], $result['headers'], $result['pathArgs'], $result['dnsParam'], $result['uriParam']);

        $result['cannonicalRequest'] = $canonicalstring;

        $signature = base64_encode(hash_hmac('sha1', $canonicalstring, $this->secretKey, true));

        $authorization = 'OBS ' . $this->accessKeyId . ':' . $signature;

        $result['headers']['Authorization'] = $authorization;

        return $result;
    }

    /**
     * 处理签名数据
     * @param $method
     * @param $headers
     * @param $pathArgs
     * @param $bucketName
     * @param $objectKey
     * @param null $expires
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/17
     */
    public function makeCanonicalstring($method, $headers, $pathArgs, $bucketName, $objectKey, $expires = null)
    {
        $buffer = [];
        $buffer[] = $method;
        $buffer[] = "\n";
        $interestHeaders = [];

        foreach ($headers as $key => $value) {
            $key = strtolower($key);
            if (in_array($key, self::INTEREST_HEADER_KEY_LIST) || strpos($key, self::HEADER_PREFIX) === 0) {
                $interestHeaders[$key] = $value;
            }
        }

        if (array_key_exists(self::ALTERNATIVE_DATE_HEADER, $interestHeaders)) {
            $interestHeaders['date'] = '';
        }

        if ($expires !== null) {
            $interestHeaders['date'] = strval($expires);
        }

        if (!array_key_exists('content-type', $interestHeaders)) {
            $interestHeaders['content-type'] = '';
        }

        if (!array_key_exists('content-md5', $interestHeaders)) {
            $interestHeaders['content-md5'] = '';
        }

        ksort($interestHeaders);

        foreach ($interestHeaders as $key => $value) {
            if (strpos($key, self::HEADER_PREFIX) === 0) {
                $buffer[] = $key . ':' . $value;
            } else {
                $buffer[] = $value;
            }
            $buffer[] = "\n";
        }

        $uri = '';

        $bucketName = $this->isCname ? $headers['Host'] : $bucketName;

        if ($bucketName) {
            $uri .= '/';
            $uri .= $bucketName;
            if (!$this->pathStyle) {
                $uri .= '/';
            }
        }

        if ($objectKey) {
            if (!($pos = strripos($uri, '/')) || strlen($uri) - 1 !== $pos) {
                $uri .= '/';
            }
            $uri .= $objectKey;
        }

        $buffer[] = $uri === '' ? '/' : $uri;


        if (!empty($pathArgs)) {
            ksort($pathArgs);
            $_pathArgs = [];
            foreach ($pathArgs as $key => $value) {
                if (in_array(strtolower($key), self::ALLOWED_RESOURCE_PARAMTER_NAMES) || strpos($key, self::HEADER_PREFIX) === 0) {
                    $_pathArgs[] = $value === null || $value === '' ? $key : $key . '=' . urldecode($value);
                }
            }
            if (!empty($_pathArgs)) {
                $buffer[] = '?';
                $buffer[] = implode('&', $_pathArgs);
            }
        }

        return implode('', $buffer);
    }

    /**
     * 发起请求
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $clientHeader
     * @param int $timeout
     * @return false|string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/16
     */
    public function request(string $url, string $method, array $data = [], array $clientHeader = [], int $timeout = 10)
    {
        $method = strtoupper($method);
        $urlAttr = pathinfo($url);
        $urlParse = parse_url($urlAttr['dirname'] ?? '');

        $uriParam = '';
        if ($urlAttr['dirname'] !== 'https:') {
            if (isset($urlParse['path'])) {
                $uriParam .= substr($urlParse['path'], 1) . '/';
            }
            if (isset($urlAttr['basename'])) {
                $uriParam .= $urlAttr['basename'];
            }
        }

        $result = $this->getSign([
            'method' => $method,
            'headers' => $clientHeader,
            'pathArgs' => '',
            'dnsParam' => $data['bucket'] ?? '',
            'uriParam' => $uriParam,
        ]);

        return $this->requestClient($url, $method, $data, $result['headers'], $timeout);
    }


    /**
     * 组合成xml
     * @param array $data
     * @param string $root
     * @param string $itemKey
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2022/10/17
     */
    protected function xmlBuild(array $xmlAttr, string $root = 'xml', string $itemKey = 'item')
    {
        $xml = '<' . $root . '>';
        $xml .= '<' . $itemKey . '>';

        foreach ($xmlAttr as $kk => $vv) {
            if (is_array($vv)) {
                foreach ($vv as $v) {
                    $xml .= '<' . $kk . '>' . $v . '</' . $kk . '>';
                }
            } else {
                $xml .= '<' . $kk . '>' . $vv . '</' . $kk . '>';
            }
        }
        $xml .= '</' . $itemKey . '>';
        $xml .= '</' . $root . '>';

        return $xml;
    }

}
