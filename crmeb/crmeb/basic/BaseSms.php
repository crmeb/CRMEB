<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\basic;

use think\facade\Config;

/**
 * Class BaseSms
 * @package crmeb\basic
 */
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
