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

namespace crmeb\services\sms;

use crmeb\basic\BaseStorage;
use crmeb\services\yihaotong\AccessTokenServeService;

abstract class BaseSms extends BaseStorage
{
    /**
     * access_token 服务
     * @var AccessTokenServeService|null
     */
    protected $accessToken = null;

    /**
     * BaseSms constructor.
     * @param string $name 驱动名称
     * @param AccessTokenServeService $accessTokenServeService access_token服务
     * @param string $configFile 配置文件名
     * @param array $config 配置数组
     */
    public function __construct(string $name, AccessTokenServeService $accessTokenServeService, string $configFile, array $config = [])
    {
        parent::__construct($name, $config, $configFile);
        $this->accessToken = $accessTokenServeService;
    }

    /**
     * 初始化
     * @param array $config
     */
    protected function initialize(array $config = [])
    {
    }
}