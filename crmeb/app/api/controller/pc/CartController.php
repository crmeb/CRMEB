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

namespace app\api\controller\pc;


use app\Request;
use app\services\pc\CartServices;

class CartController
{
    protected $services;

    public function __construct(CartServices $services)
    {
        $this->services = $services;
    }

    /**
     * 获取用户购物车列表
     * @param Request $request
     * @return mixed
     */
    public function getCartList(Request $request)
    {
        $uid = $request->uid();
        $data = $this->services->getCartList((int)$uid);
        return app('json')->success($data);
    }
}
