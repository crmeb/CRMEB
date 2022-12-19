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

namespace app\kefuapi\controller;


use crmeb\basic\BaseController;

/**
 * Class AuthController
 * @package app\kefuapi\controller
 */
abstract class AuthController extends BaseController
{

    /**
     * @var int
     */
    protected $kefuId;

    /**
     * @var array
     */
    protected $kefuInfo;

    /**
     * 初始化
     */
    protected function initialize()
    {
        $this->kefuId = $this->request->kefuId();
        $this->kefuInfo = $this->request->kefuInfo();
    }
}
