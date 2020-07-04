<?php

namespace crmeb\basic;

use think\facade\Config;

abstract class BaseSms extends BaseStorage
{
    /**
     * 模板id
     * @var array
     */
    protected $templateIds = [];

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
     * 获取模板id
     * @return array
     */
    public function getTemplateId()
    {
        return $this->templateIds;
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
     * 发送短信
     * @param string $phone
     * @param string $templateId
     * @param array $data
     * @return mixed
     */
    abstract public function send(string $phone, string $templateId, array $data = []);

}