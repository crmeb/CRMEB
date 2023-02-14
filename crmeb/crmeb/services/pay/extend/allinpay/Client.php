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
namespace crmeb\services\pay\extend\allinpay;

use crmeb\exceptions\ApiException;
use crmeb\services\HttpService;
use think\facade\Cache;
use think\facade\Log;

/**
 * Class Client
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2022/12/27
 * @package crmeb\services\pay\extend\allinpay
 */
class Client
{

    //生产地址
    const API_URL = 'https://vsp.allinpay.com/apiweb/';

    //测试接口地址
    const BETA_API_URL = 'https://syb-test.allinpay.com/apiweb/';

    //版本号
    const VERSION_NUM_11 = '11';

    //版本号
    const VERSION_NUM_12 = '12';


    protected $signType = 'MD5';

    /**
     * @var string
     */
    protected $cusid = '';

    /**
     * @var string
     */
    protected $appid = '';

    /**
     * @var string
     */
    protected $privateKey = '';

    /**
     * @var
     */
    protected $publicKey = '';

    /**
     * 回调地址
     * @var string
     */
    protected $notifyUrl = '';

    /**
     * 是否测试
     * @var bool
     */
    protected $isBeta = true;

    /**
     * debug模式
     * @var bool
     */
    protected $isDebug = true;

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->appid = $config['appid'] ?? '';
        $this->cusid = $config['cusid'] ?? '';
        $this->privateKey = $config['privateKey'] ?? '';
        $this->publicKey = $config['publicKey'] ?? '';
        $this->notifyUrl = $config['notifyUrl'] ?? '';
        $this->isBeta = $config['isBeta'] ?? true;
    }

    /**
     * 发送请求
     * @param string $url
     * @param array $options
     * @return mixed
     */
    public function send(string $url, array $options = [])
    {
        $data = $options['data'] ?? [];
        $header = $options['header'] ?? [];

        $data['cusid'] = $this->cusid;
        $data['appid'] = $this->appid;
        if (!isset($data['version'])) {
            $data['version'] = self::VERSION_NUM_11;
        }

        $data['signtype'] = $this->signType;
        $data['randomstr'] = uniqid();

        if (!empty($options['form'])) {
            $data['charset'] = 'UTF-8';
            $data['version'] = self::VERSION_NUM_12;
            $data['sign'] = $this->sign($data);
            return $data;
        }

        $data['sign'] = $this->sign($data);

        $response = $this->request($url, $data, 'post', $header);

        if ('SUCCESS' !== $response['retcode']) {
            throw new ApiException($response['retmsg']);
        }

        if (!empty($response['trxstatus']) && !in_array($response['trxstatus'], ['0000', '2008', '2000'])) {
            throw new ApiException($response['errmsg']);
        }

        if ($this->validSign($response)) {
            return $response;
        }

        throw new ApiException('创建订单成功验签失败');
    }

    /**
     * @param string $url
     * @param array $data
     * @param string $method
     * @param array $header
     * @param int $timeout
     * @return mixed
     */
    public function request(string $url, array $data = [], string $method = 'post', array $header = [], int $timeout = 10)
    {
        $headerData = [];
        if ($header) {
            foreach ($header as $key => $item) {
                $headerData[] = $key . ':' . $item;
            }
        }

        $content = HttpService::request($this->baseUrl($url), $method, $data, $headerData, $timeout);

        $respones = json_decode($content, true, 512, JSON_BIGINT_AS_STRING);

        $this->debugLog('API response decoded:', ['content' => $respones, 'header' => $header]);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ApiException('Failed to parse JSON: ' . json_last_error_msg());
        }

        return $respones;
    }

    /**
     * @param string $message
     * @param array $contents
     */
    protected function debugLog(string $message, array $contents = [])
    {
        $this->isDebug && Log::debug($message, $contents);
    }

    /**
     * @param string|null $url
     * @return string
     */
    protected function baseUrl(string $url = null)
    {
        $baseUrl = $this->isBeta ? self::BETA_API_URL : self::API_URL;
        if ($url) {
            $baseUrl .= $url;
        }

        return $baseUrl;
    }

    /**
     * @param array $data
     * @return string
     */
    public function sign(array $data)
    {

        $private_key = $this->privateKey;

        if ($this->signType === 'MD5') {
            $data['key'] = $private_key;
            ksort($data);

            $bufSignSrc = $this->toUrlParams($data);

            return md5($bufSignSrc);
        } else {
            ksort($data);

            $bufSignSrc = $this->toUrlParams($data);

            $private_key = chunk_split($private_key, 64, "\n");

            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($private_key) . "-----END RSA PRIVATE KEY-----";

            openssl_sign($bufSignSrc, $signature, $key);

            $sign = base64_encode($signature);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的

            return $sign;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public function toUrlParams(array $data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * @param array $data
     * @return false|int
     */
    public function validSign(array $data)
    {
        $sign = $data['sign'];
        unset($data['sign']);

        if ($this->signType === 'MD5') {
            $data['key'] = $this->privateKey;
            ksort($data);
            $bufSignSrc = $this->toUrlParams($data);
            return strtolower($sign) == strtolower(md5($bufSignSrc));
        } else {
            ksort($data);
            $bufSignSrc = $this->toUrlParams($data);
            $public_key = $this->publicKey;

            $public_key = chunk_split($public_key, 64, "\n");

            $key = "-----BEGIN PUBLIC KEY-----\n$public_key-----END PUBLIC KEY-----\n";

            return openssl_verify($bufSignSrc, base64_decode($sign), $key);
        }
    }

    /**
     * @param string $notifyUrl
     * @return $this
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/2/7
     */
    public function setNotifyUrl(string $notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        return $this;
    }
}
