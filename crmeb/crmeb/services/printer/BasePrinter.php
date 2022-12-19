<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\printer;

use crmeb\basic\BaseStorage;

/**
 * Class BasePrinter
 * @package crmeb\basic
 */
abstract class BasePrinter extends BaseStorage
{

    /**
     * token句柄
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * 打印内容
     * @var string
     */
    protected $printerContent;

    /**
     * BasePrinter constructor.
     * @param string $name
     * @param AccessToken $accessToken
     * @param string $configFile
     */
    public function __construct(string $name, AccessToken $accessToken, string $configFile)
    {
        parent::__construct($name, [], $configFile);
        $this->accessToken = $accessToken;
    }

    /**
     * 开始打印
     * @param array|null $systemConfig
     * @return mixed
     */
    abstract public function startPrinter();

    /**
     * 设置打印内容
     * @param array $config
     * @return mixed
     */
    abstract public function setPrinterContent(array $config);

}
