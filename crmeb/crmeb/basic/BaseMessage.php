<?php

namespace crmeb\basic;

use think\facade\Config;

abstract class BaseMessage extends BaseStorage
{
    /**
     * 模板id
     * @var array
     */
    protected $templateIds = [];

    /**
     * openId
     * @var string
     */
    protected $openId;

    /**
     * 跳转链接
     * @var string
     */
    protected $toUrl;

    /**
     * 颜色
     * @var string
     */
    protected $color;

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {
        $this->templateIds = Config::get($this->configFile . '.stores.' . $this->name . '.template_id', []);
    }

    /**
     * 是否记录日志
     * @return mixed
     */
    public function isLog()
    {
        $isLog = Config::get($this->configFile . 'isLog', false);
        return Config::get($this->configFile . '.stores.' . $this->name . '.isLog', $isLog);
    }

    /**
     * 获取模板id
     * @return array
     */
    public function getTemplateId()
    {
        return $this->templateIds;
    }

    /**
     * openid
     * @param string $openId
     * @return $this
     */
    public function to(string $openId)
    {
        $this->openId = $openId;
        return $this;
    }

    /**
     * 跳转路径
     * @param string $url
     * @return $this
     */
    public function url(string $url)
    {
        $this->toUrl = $url;
        return $this;
    }

    /**
     * 设置背景颜色
     * @param string $color
     * @return $this
     */
    public function setColor(string $color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * 提取模板code
     * @param string $templateId
     * @return null
     */
    protected function getTemplateCode(string $templateId)
    {
        return $this->templateIds[$templateId] ?? null;
    }

    /**
     * 恢复默认值
     */
    protected function clear()
    {
        $this->openId = null;
        $this->toUrl = null;
        $this->color = null;
    }

    /**
     * 发送消息
     * @param string $templateId
     * @param array $data
     * @return mixed
     */
    abstract public function send(string $templateId, array $data = []);

    /**
     * 添加模板
     * @param string $shortId
     * @return mixed
     */
    abstract public function add(string $shortId);

    /**
     * 删除模板
     * @param string $templateId
     * @return mixed
     */
    abstract public function delete(string $templateId);

    /**
     * 获取所有模板
     * @return mixed
     */
    abstract public function list();

}