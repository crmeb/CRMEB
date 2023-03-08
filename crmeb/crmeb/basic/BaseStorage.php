<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\basic;


/**
 * Class BaseStorage
 * @package crmeb\basic
 */
abstract class BaseStorage
{

    /**
     * 驱动名称
     * @var string
     */
    protected $name;

    /**
     * 驱动配置文件名
     * @var string
     */
    protected $configFile;

    /**
     * 错误信息
     * @var string
     */
    protected $error;

    /**
     * BaseStorage constructor.
     * @param string $name 驱动名
     * @param string $configFile 驱动配置名
     * @param array $config 其他配置
     */
    public function __construct(string $name, array $config = [], string $configFile = null)
    {
        $this->name = $name;
        $this->configFile = $configFile;
        $this->initialize($config);
    }


    /**
     * 设置错误信息
     * @param string|null $error
     * @return bool
     */
    protected function setError(?string $error = null)
    {
        $this->error = $error ?: '未知错误';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        $error = $this->error;
        $this->error = null;
        return $error;
    }

    /**
     * 初始化
     * @param array $config
     * @return mixed
     */
    abstract protected function initialize(array $config);

}
