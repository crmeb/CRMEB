<?php

namespace crmeb\services\invoice;

use crmeb\basic\BaseStorage;
use crmeb\services\AccessTokenServeService;

abstract class BaseInvoice extends BaseStorage
{
    /**
     * access_token
     * @var null
     */
    protected $accessToken = NULL;

    /**
     * BaseInvoice constructor.
     * @param string $name
     * @param AccessTokenServeService $accessTokenServeService
     * @param string $configFile
     * @param array $config
     */
    public function __construct(string $name, AccessTokenServeService $accessTokenServeService, string $configFile, array $config = [])
    {
        $this->accessToken = $accessTokenServeService;
        $this->name = $name;
        $this->configFile = $configFile;
        $this->initialize($config);
    }

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config = [])
    {

    }
}