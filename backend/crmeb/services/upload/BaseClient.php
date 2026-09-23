<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\upload;

/**
 * Class BaseClient
 * @package crmeb\services\upload
 */
abstract class BaseClient
{

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * BaseClient constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取配置
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

}