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
declare(strict_types=1);

namespace modules\channel\wechat_mini\controller;

use modules\channel\wechat_mini\service\RoutineSchemeServices;

/**
 * 小程序外链跳转
 */
class SchemeController
{
    /**
     * @var RoutineSchemeServices
     */
    protected $services;

    /**
     * @param RoutineSchemeServices $services
     */
    public function __construct(RoutineSchemeServices $services)
    {
        $this->services = $services;
    }

    /**
     * 按外链 id 跳转到小程序 URL Scheme
     * @param mixed $id
     * @return \think\Response
     */
    public function redirect($id)
    {
        return response($this->services->redirectHtml($id));
    }
}
