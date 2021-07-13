<?php

namespace OSS\Model;


/**
 * Class LifecycleRule
 * @package OSS\Model
 *
 * @link http://help.aliyun.com/document_detail/oss/api-reference/bucket/PutBucketLifecycle.html
 */
class LifecycleRule
{
    /**
     * 得到规则ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id 规则ID
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * 得到文件前缀
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 设置文件前缀
     *
     * @param string $prefix 文件前缀
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Lifecycle规则的状态
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 设置Lifecycle规则状态
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     *
     * @return LifecycleAction[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param LifecycleAction[] $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }


    /**
     * LifecycleRule constructor.
     *
     * @param string $id 规则ID
     * @param string $prefix 文件前缀
     * @param string $status 规则状态，可选[self::LIFECYCLE_STATUS_ENABLED, self::LIFECYCLE_STATUS_DISABLED]
     * @param LifecycleAction[] $actions
     */
    public function __construct($id, $prefix, $status, $actions)
    {
        $this->id = $id;
        $this->prefix = $prefix;
        $this->status = $status;
        $this->actions = $actions;
    }

    /**
     * @param \SimpleXMLElement $xmlRule
     */
    public function appendToXml(&$xmlRule)
    {
        $xmlRule->addChild('ID', $this->id);
        $xmlRule->addChild('Prefix', $this->prefix);
        $xmlRule->addChild('Status', $this->status);
        foreach ($this->actions as $action) {
            $action->appendToXml($xmlRule);
        }
    }

    private $id;
    private $prefix;
    private $status;
    private $actions = array();

    const LIFECYCLE_STATUS_ENABLED = 'Enabled';
    const LIFECYCLE_STATUS_DISABLED = 'Disabled';
}