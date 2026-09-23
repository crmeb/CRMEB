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

namespace crmeb\services\logistics;

use crmeb\basic\BaseStorage;

abstract class BaseExpress extends BaseStorage
{
    /**
     * access_token
     * @var null
     */
    protected $accessToken = NULL;

    /**
     * BaseExpress constructor.
     * @param string $name 驱动名称
     * @param array $config 驱动配置
     * @param string $configFile 配置文件标识
     */
    public function __construct(string $name, array $config = [], string $configFile = '')
    {
        parent::__construct($name, $config, $configFile);
    }

    /**
     * 初始化
     * 各驱动在此方法中根据自身配置初始化 accessToken
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {

    }
}