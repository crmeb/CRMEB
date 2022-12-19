<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\sms\storage;


use crmeb\services\sms\BaseSms;
use crmeb\services\HttpService;

/**
 * 腾讯云短信
 * Class Tencent
 * @package crmeb\services\sms\storage
 */
class Tencent extends BaseSms
{

    //接口请求地址
    const API_URL = 'https://sms.tencentcloudapi.com';

    /**
     * 发送模板id
     * @var array
     */
    protected $templates = [];

    /**
     * @var string[]
     */
    protected $header = ['Content-Type:application/json'];

    /**
     * @var string
     */
    protected $secretId = '';

    /**
     * @var string
     */
    protected $secretKey = '';

    /**
     * 短信SDKid
     * @var string
     */
    protected $smsSdkAppId = '';

    /**
     * 短信签名
     * @var string
     */
    protected $signName = '';

    /**
     * @var string
     */
    protected $region = "ap-guangzhou";

    /**
     * 版本号
     * @var string
     */
    protected $version = "2021-01-11";

    /**
     * 加密方式
     * @var string
     */
    protected $algorithm = 'TC3-HMAC-SHA256';

    /**
     * 产品名称
     * @var string
     */
    protected $service = 'sms';

    /**
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {
        parent::initialize($config);
        $this->smsSdkAppId = $config['tencent_sms_app_id'] ?? '';
        $this->secretId = $config['tencent_sms_secret_id'] ?? '';
        $this->secretKey = $config['tencent_sms_secret_key'] ?? '';
        $this->signName = $config['tencent_sms_sign_name'] ?? '';
        $this->region = $config['tencent_sms_region'] ?? '';
    }

    /**
     * @param string $phone
     * @param string $templateId
     * @param array $data
     * @return mixed|void
     */
    public function send(string $phone, string $templateId, array $data)
    {
        $body = json_encode([
            'PhoneNumberSet' => [$phone],
            'SmsSdkAppId' => $this->smsSdkAppId,
            'SignName' => $this->signName,
            'TemplateId' => $templateId,
            'TemplateParamSet' => $data,
        ]);
        $res = HttpService::request(self::API_URL, 'post', $body, $this->getHeader($body));
        $res = json_decode($res, true);
        if (!empty($res['Response']['Error'])) {
            return $this->setError($res['Response']['Message']);
        }
        if ($res['Response']['SendStatusSet'][0]['Code'] != 'Ok') {
            return $this->setError($res['Response']['SendStatusSet'][0]['Message']);
        }

        return $res;
    }

    /**
     * 获取请求header
     * @param string $boby
     * @return string[]
     */
    protected function getHeader(string $boby)
    {
        $host = str_replace(['https://', 'http://'], '', self::API_URL);
        $header = [
            'Content-Type:application/json; charset=utf-8',
            'Host:' . $host,
            'X-TC-Action:SendSms'
        ];
        $httpRequestMethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:application/json; charset=utf-8\n" . "host:" . $host . "\n";
        $signedHeaders = 'content-type;host';
        $hashedRequestPayload = hash("SHA256", $boby);
        $canonicalRequest = $httpRequestMethod . "\n"
            . $canonicalUri . "\n"
            . $canonicalQueryString . "\n"
            . $canonicalHeaders . "\n"
            . $signedHeaders . "\n"
            . $hashedRequestPayload;

        $service = $this->service;
        $timestamp = time();
        $date = gmdate("Y-m-d", $timestamp);
        $credentialScope = $date . "/" . $service . "/tc3_request";

        $hashedCanonicalRequest = hash("SHA256", $canonicalRequest);
        $stringToSign = $this->algorithm . "\n"
            . $timestamp . "\n"
            . $credentialScope . "\n"
            . $hashedCanonicalRequest;

        $secretDate = hash_hmac("SHA256", $date, "TC3" . $this->secretKey, true);
        $secretService = hash_hmac("SHA256", $service, $secretDate, true);
        $secretSigning = hash_hmac("SHA256", "tc3_request", $secretService, true);
        $signature = hash_hmac("SHA256", $stringToSign, $secretSigning);
        $authorization = $this->algorithm
            . " Credential=" . $this->secretId . "/" . $credentialScope
            . ", SignedHeaders=content-type;host, Signature=" . $signature;

        array_push($header, 'Authorization:' . $authorization, 'X-TC-Timestamp:' . $timestamp, 'X-TC-Version:' . $this->version,
            'X-TC-Region:' . $this->region);

        return $header;
    }

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function modify(string $sign = null, string $phone = '', string $code = '')
    {
        // TODO: Implement modify() method.
    }

    public function info()
    {
        // TODO: Implement info() method.
    }

    public function temps(int $page, int $limit, int $type)
    {
        // TODO: Implement temps() method.
    }

    public function apply(string $title, string $content, int $type)
    {
        // TODO: Implement apply() method.
    }

    public function applys(int $tempType, int $page, int $limit)
    {
        // TODO: Implement applys() method.
    }

    public function record($record_id)
    {
        // TODO: Implement record() method.
    }
}
