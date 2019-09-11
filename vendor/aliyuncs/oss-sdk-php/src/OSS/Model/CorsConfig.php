<?php

namespace OSS\Model;


use OSS\Core\OssException;

/**
 * Class CorsConfig
 * @package OSS\Model
 *
 * @link http://help.aliyun.com/document_detail/oss/api-reference/cors/PutBucketcors.html
 */
class CorsConfig implements XmlConfig
{
    /**
     * CorsConfig constructor.
     */
    public function __construct()
    {
        $this->rules = array();
    }

    /**
     * 得到CorsRule列表
     *
     * @return CorsRule[]
     */
    public function getRules()
    {
        return $this->rules;
    }


    /**
     * 添加一条CorsRule
     *
     * @param CorsRule $rule
     * @throws OssException
     */
    public function addRule($rule)
    {
        if (count($this->rules) >= self::OSS_MAX_RULES) {
            throw new OssException("num of rules in the config exceeds self::OSS_MAX_RULES: " . strval(self::OSS_MAX_RULES));
        }
        $this->rules[] = $rule;
    }

    /**
     * 从xml数据中解析出CorsConfig
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $xml = simplexml_load_string($strXml);
        if (!isset($xml->CORSRule)) return;
        foreach ($xml->CORSRule as $rule) {
            $corsRule = new CorsRule();
            foreach ($rule as $key => $value) {
                if ($key === self::OSS_CORS_ALLOWED_HEADER) {
                    $corsRule->addAllowedHeader(strval($value));
                } elseif ($key === self::OSS_CORS_ALLOWED_METHOD) {
                    $corsRule->addAllowedMethod(strval($value));
                } elseif ($key === self::OSS_CORS_ALLOWED_ORIGIN) {
                    $corsRule->addAllowedOrigin(strval($value));
                } elseif ($key === self::OSS_CORS_EXPOSE_HEADER) {
                    $corsRule->addExposeHeader(strval($value));
                } elseif ($key === self::OSS_CORS_MAX_AGE_SECONDS) {
                    $corsRule->setMaxAgeSeconds(strval($value));
                }
            }
            $this->addRule($corsRule);
        }
        return;
    }

    /**
     * 生成xml字符串
     *
     * @return string
     */
    public function serializeToXml()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><CORSConfiguration></CORSConfiguration>');
        foreach ($this->rules as $rule) {
            $xmlRule = $xml->addChild('CORSRule');
            $rule->appendToXml($xmlRule);
        }
        return $xml->asXML();
    }

    public function __toString()
    {
        return $this->serializeToXml();
    }

    const OSS_CORS_ALLOWED_ORIGIN = 'AllowedOrigin';
    const OSS_CORS_ALLOWED_METHOD = 'AllowedMethod';
    const OSS_CORS_ALLOWED_HEADER = 'AllowedHeader';
    const OSS_CORS_EXPOSE_HEADER = 'ExposeHeader';
    const OSS_CORS_MAX_AGE_SECONDS = 'MaxAgeSeconds';
    const OSS_MAX_RULES = 10;

    /**
     * orsRule列表
     *
     * @var CorsRule[]
     */
    private $rules = array();
}