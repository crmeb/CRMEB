<?php

namespace OSS\Model;

use OSS\Core\OssException;


/**
 * Class BucketLifecycleConfig
 * @package OSS\Model
 * @link http://help.aliyun.com/document_detail/oss/api-reference/bucket/PutBucketLifecycle.html
 */
class LifecycleConfig implements XmlConfig
{
    /**
     * 从xml数据中解析出LifecycleConfig
     *
     * @param string $strXml
     * @throws OssException
     * @return null
     */
    public function parseFromXml($strXml)
    {
        $this->rules = array();
        $xml = simplexml_load_string($strXml);
        if (!isset($xml->Rule)) return;
        $this->rules = array();
        foreach ($xml->Rule as $rule) {
            $id = strval($rule->ID);
            $prefix = strval($rule->Prefix);
            $status = strval($rule->Status);
            $actions = array();
            foreach ($rule as $key => $value) {
                if ($key === 'ID' || $key === 'Prefix' || $key === 'Status') continue;
                $action = $key;
                $timeSpec = null;
                $timeValue = null;
                foreach ($value as $timeSpecKey => $timeValueValue) {
                    $timeSpec = $timeSpecKey;
                    $timeValue = strval($timeValueValue);
                }
                $actions[] = new LifecycleAction($action, $timeSpec, $timeValue);
            }
            $this->rules[] = new LifecycleRule($id, $prefix, $status, $actions);
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

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><LifecycleConfiguration></LifecycleConfiguration>');
        foreach ($this->rules as $rule) {
            $xmlRule = $xml->addChild('Rule');
            $rule->appendToXml($xmlRule);
        }
        return $xml->asXML();
    }

    /**
     *
     * 添加LifecycleRule
     *
     * @param LifecycleRule $lifecycleRule
     * @throws OssException
     */
    public function addRule($lifecycleRule)
    {
        if (!isset($lifecycleRule)) {
            throw new OssException("lifecycleRule is null");
        }
        $this->rules[] = $lifecycleRule;
    }

    /**
     *  将配置转换成字符串，便于用户查看
     *
     * @return string
     */
    public function __toString()
    {
        return $this->serializeToXml();
    }

    /**
     * 得到所有的生命周期规则
     *
     * @return LifecycleRule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @var LifecycleRule[]
     */
    private $rules;
}


