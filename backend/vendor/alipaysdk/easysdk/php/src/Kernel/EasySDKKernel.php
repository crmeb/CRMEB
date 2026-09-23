<?php


namespace Alipay\EasySDK\Kernel;

use Alipay\EasySDK\Kernel\Util\AES;
use Alipay\EasySDK\Kernel\Util\JsonUtil;
use Alipay\EasySDK\Kernel\Util\PageUtil;
use Alipay\EasySDK\Kernel\Util\SignContentExtractor;
use Alipay\EasySDK\Kernel\Util\Signer;
use AlibabaCloud\Tea\FileForm\FileForm;
use AlibabaCloud\Tea\FileForm\FileForm\FileField;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

class EasySDKKernel
{

    private $config;

    private $optionalTextParams;

    private $optionalBizParams;

    private $textParams;

    private $bizParams;


    public function __construct($config)
    {
        $this->config = $config;
    }

    public function injectTextParam($key, $value)
    {
        if ($key != null) {
            $this->optionalTextParams[$key] = $value;
        }
    }

    public function injectBizParam($key, $value)
    {
        if ($key != null) {
            $this->optionalBizParams[$key] = $value;
        }
    }

    /**
     * 获取时间戳，格式yyyy-MM-dd HH:mm:ss
     * @return false|string 当前时间戳
     */
    public function getTimestamp()
    {
        return date("Y-m-d H:i:s");
    }

    public function getConfig($key)
    {
        return $this->config->$key;
    }

    public function getSdkVersion()
    {
        return AlipayConstants::SDK_VERSION;
    }

    /**
     * 将业务参数和其他额外文本参数按www-form-urlencoded格式转换成HTTP Body中的字节数组，注意要做URL Encode
     *
     * @param $bizParams array 业务参数
     * @return false|string|null
     */
    public function toUrlEncodedRequestBody($bizParams)
    {
        $sortedMap = $this->getSortedMap(null, $bizParams, null);
        if (empty($sortedMap)) {
            return null;
        }
        return $this->buildQueryString($sortedMap);
    }

    /**
     * 解析网关响应内容，同时将API的接口名称和响应原文插入到响应数组的method和body字段中
     *
     * @param $response ResponseInterface HTTP响应
     * @param $method   string 调用的OpenAPI的接口名称
     * @return array    响应的结果
     */
    public function readAsJson($response, $method)
    {
        $responseBody = (string)$response->getBody();
        $map = [];
        $map[AlipayConstants::BODY_FIELD] = $responseBody;
        $map[AlipayConstants::METHOD_FIELD] = $method;
        return $map;
    }

    /**
     * 生成随机分界符，用于multipart格式的HTTP请求Body的多个字段间的分隔
     *
     * @return string 随机分界符
     */
    public function getRandomBoundary()
    {
        return date("Y-m-d H:i:s") . '';
    }

    /**
     * 将其他额外文本参数和文件参数按multipart/form-data格式转换成HTTP Body中
     * @param $textParams
     * @param $fileParams
     * @param $boundary
     * @return false|string
     */
    public function toMultipartRequestBody($textParams, $fileParams, $boundary)
    {
        $this->textParams = $textParams;
        if ($textParams != null && $this->optionalTextParams != null) {
            $this->textParams = array_merge($textParams, $this->optionalTextParams);
        } else if ($textParams == null) {
            $this->textParams = $this->optionalTextParams;
        }
        if (count($fileParams) > 0) {

            foreach ($fileParams as $key => $value) {
                $fileField = new FileField();
                $fileField->filename = $value;
                $fileField->contentType = 'multipart/form-data;charset=utf-8;boundary=' . $boundary;
                $fileField->content = new Stream(fopen($value, 'r'));
                $this->textParams[$key] = $fileField;
            }
        }
        $stream = FileForm::toFileForm($this->textParams, $boundary);

//        do {
//            $readLength = $stream->read(1024);
//        } while (0 != $readLength);
        return $stream;
    }

    /**
     * 生成页面类请求所需URL或Form表单
     * @param $method
     * @param $systemParams
     * @param $bizParams
     * @param $textParams
     * @param $sign
     * @return string
     * @throws \Exception
     */
    public function generatePage($method, $systemParams, $bizParams, $textParams, $sign)
    {
        if ($method == AlipayConstants::GET) {
            //采集并排序所有参数
            $sortedMap = $this->getSortedMap($systemParams, $bizParams, $textParams);
            $sortedMap[AlipayConstants::SIGN_FIELD] = $sign;
            return $this->getGatewayServerUrl() . '?' . $this->buildQueryString($sortedMap);
        } elseif ($method == AlipayConstants::POST) {
            //采集并排序所有参数
            $sortedMap = $this->getSortedMap($systemParams, $this->bizParams, $this->textParams);
            $sortedMap[AlipayConstants::SIGN_FIELD] = $sign;
            $pageUtil = new PageUtil();
            return $pageUtil->buildForm($this->getGatewayServerUrl(), $sortedMap);
        } else {
            throw new \Exception("不支持" . $method);
        }
    }

    /**
     *  获取商户应用公钥证书序列号，从证书模式运行时环境对象中直接读取
     *
     * @return mixed 商户应用公钥证书序列号
     */
    public function getMerchantCertSN()
    {
        return $this->config->merchantCertSN;
    }

    /**
     * 从响应Map中提取支付宝公钥证书序列号
     *
     * @param array $respMap string 响应Map
     * @return mixed   支付宝公钥证书序列号
     */
    public function getAlipayCertSN(array $respMap)
    {
        if (!empty($this->config->merchantCertSN)) {
            $body = json_decode($respMap[AlipayConstants::BODY_FIELD]);
            $alipayCertSN = $body->alipay_cert_sn;
            return $alipayCertSN;
        }
    }

    /**
     * 获取支付宝根证书序列号，从证书模式运行时环境对象中直接读取
     *
     * @return mixed 支付宝根证书序列号
     */
    public function getAlipayRootCertSN()
    {
        return $this->config->alipayRootCertSN;
    }

    /**
     * 是否是证书模式
     * @return mixed true：是；false：不是
     */
    public function isCertMode()
    {
        return $this->config->merchantCertSN;
    }

    public function extractAlipayPublicKey($alipayCertSN)
    {
        // PHP 版本只存储一个版本支付宝公钥
        return $this->config->alipayPublicKey;
    }

    /**
     * 验证签名
     *
     * @param $respMap  string 响应内容，可以从中提取出sign和body
     * @param $alipayPublicKey string 支付宝公钥
     * @return bool  true：验签通过；false：验签不通过
     * @throws \Exception
     */
    public function verify($respMap, $alipayPublicKey)
    {
        $resp = json_decode($respMap[AlipayConstants::BODY_FIELD], true);
        $sign = $resp[AlipayConstants::SIGN_FIELD];
        $signContentExtractor = new SignContentExtractor();
        $content = $signContentExtractor->getSignSourceData($respMap[AlipayConstants::BODY_FIELD],
            $respMap[AlipayConstants::METHOD_FIELD]);
        $signer = new Signer();
        return $signer->verify($content, $sign, $alipayPublicKey);
    }

    /**
     * 计算签名，注意要去除key或value为null的键值对
     *
     * @param $systemParams   array 系统参数集合
     * @param $bizParams      array 业务参数集合
     * @param $textParams     array 其他额外文本参数集合
     * @param $privateKey     string 私钥
     * @return string 签名值的Base64串
     */
    public function sign($systemParams, $bizParams, $textParams, $privateKey)
    {
        $sortedMap = $this->getSortedMap($systemParams, $bizParams, $textParams);
        $data = $this->getSignContent($sortedMap);
        $sign = new Signer();
        return $sign->sign($data, $privateKey);
    }

    /**
     * AES加密
     * @param $content
     * @param $encryptKey
     * @return string
     * @throws \Exception
     */
    public function aesEncrypt($content, $encryptKey)
    {
        $aes = new AES();
        return $aes->aesEncrypt($content, $encryptKey);
    }

    /**
     * AES解密
     * @param $content
     * @param $encryptKey
     * @return false|string
     * @throws \Exception
     */
    public function aesDecrypt($content, $encryptKey)
    {
        $aes = new AES();
        return $aes->aesDecrypt($content, $encryptKey);
    }

    /**
     * 生成sdkExecute类请求所需URL
     *
     * @param $systemParams
     * @param $bizParams
     * @param $textParams
     * @param $sign
     * @return string
     */
    public function generateOrderString($systemParams, $bizParams, $textParams, $sign)
    {
        //采集并排序所有参数
        $sortedMap = $this->getSortedMap($systemParams, $bizParams, $textParams);
        $sortedMap[AlipayConstants::SIGN_FIELD] = $sign;
        return http_build_query($sortedMap);
    }

    public function sortMap($randomMap)
    {
        return $randomMap;
    }


    /**
     *  从响应Map中提取返回值对象的Map，并将响应原文插入到body字段中
     *
     * @param $respMap  string 响应内容
     * @return mixed
     */
    public function toRespModel($respMap)
    {
        $body = $respMap[AlipayConstants::BODY_FIELD];
        $methodName = $respMap[AlipayConstants::METHOD_FIELD];
        $responseNodeName = str_replace(".", "_", $methodName) . AlipayConstants::RESPONSE_SUFFIX;

        $model = json_decode($body, true);
        if (strpos($body, AlipayConstants::ERROR_RESPONSE)) {
            $result = $model[AlipayConstants::ERROR_RESPONSE];
            $result[AlipayConstants::BODY_FIELD] = $body;
        } else {
            $result = $model[$responseNodeName];
            $result[AlipayConstants::BODY_FIELD] = $body;
        }
        return $result;
    }

    public function verifyParams($parameters, $publicKey)
    {
        $sign = new Signer();
        return $sign->verifyParams($parameters, $publicKey);
    }

    /**
     * 字符串拼接
     *
     * @param $a
     * @param $b
     * @return string 字符串a和b拼接后的字符串
     */
    public function concatStr($a, $b)
    {
        return $a . $b;
    }


    private function buildQueryString(array $sortedMap)
    {
        $requestUrl = null;
        foreach ($sortedMap as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($this->characet($sysParamValue, AlipayConstants::DEFAULT_CHARSET)) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        return $requestUrl;

    }

    private function getSortedMap($systemParams, $bizParams, $textParams)
    {
        $this->textParams = $textParams;
        $this->bizParams = $bizParams;
        if ($textParams != null && $this->optionalTextParams != null) {
            $this->textParams = array_merge($textParams, $this->optionalTextParams);
        } else if ($textParams == null) {
            $this->textParams = $this->optionalTextParams;
        }
        if ($bizParams != null && $this->optionalBizParams != null) {
            $this->bizParams = array_merge($bizParams, $this->optionalBizParams);
        } else if ($bizParams == null) {
            $this->bizParams = $this->optionalBizParams;
        }
        $json = new JsonUtil();
        if ($this->bizParams != null) {
            $bizParams = $json->toJsonString($this->bizParams);
        }
        $sortedMap = $systemParams;
        if (!empty($bizParams)) {
            $sortedMap[AlipayConstants::BIZ_CONTENT_FIELD] = json_encode($bizParams, JSON_UNESCAPED_UNICODE);
        }
        if (!empty($this->textParams)) {
            if (!empty($sortedMap)) {
                $sortedMap = array_merge($sortedMap, $this->textParams);
            } else {
                $sortedMap = $this->textParams;
            }
        }
        if ($this->getConfig(AlipayConstants::NOTIFY_URL_CONFIG_KEY) != null) {
            $sortedMap[AlipayConstants::NOTIFY_URL_FIELD] = $this->getConfig(AlipayConstants::NOTIFY_URL_CONFIG_KEY);
        }
        return $sortedMap;
    }

    /**
     * 获取签名字符串
     *
     * @param $params
     * @return string
     */
    private function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, AlipayConstants::DEFAULT_CHARSET);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

    private function setNotifyUrl($params)
    {
        if ($this->config(AlipayConstants::NOTIFY_URL_CONFIG_KEY) != null && $params(AlipayConstants::NOTIFY_URL_CONFIG_KEY) == null) {
            $params[AlipayConstants::NOTIFY_URL_CONFIG_KEY] = $this->config(AlipayConstants::NOTIFY_URL_CONFIG_KEY);
        }
    }

    private function getGatewayServerUrl()
    {
        return $this->getConfig(AlipayConstants::PROTOCOL_CONFIG_KEY) . '://' . $this->getConfig(AlipayConstants::HOST_CONFIG_KEY) . '/gateway.do';
    }

    /**
     * 校验$value是否非空
     *
     * @param $value
     * @return bool if not set ,return true;if is null , return true;
     */
    function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
        return false;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = AlipayConstants::DEFAULT_CHARSET;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

}