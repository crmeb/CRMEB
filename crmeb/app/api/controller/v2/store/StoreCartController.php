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

namespace app\api\controller\v2\store;


use app\services\order\StoreCartServices;
use app\Request;

class StoreCartController
{
    protected $services;

    public function __construct(StoreCartServices $services)
    {
        $this->services = $services;
    }

    /**
     * 购物车重选
     * @param Request $request
     * @return mixed
     */
    public function resetCart(Request $request)
    {
        list($id, $unique, $num, $product_id) = $request->postMore([
            ['id', 0],
            ['unique', ''],
            ['num', 1],
            ['product_id', 0]
        ], true);
        $this->services->resetCart($id, $request->uid(), $product_id, $unique, $num);
        return app('json')->success(100001);
    }

    /**
     * 获取用户购物车
     * @param Request $request
     * @return mixed
     */
    public function getCartList(Request $request)
    {
        $uid = (int)$request->uid();
        $data = $this->services->getCartList(['uid' => $uid, 'is_del' => 0, 'is_new' => 0, 'is_pay' => 0, 'combination_id' => 0, 'seckill_id' => 0, 'bargain_id' => 0], 0, 0, ['productInfo', 'attrInfo']);
        [$data, $valid, $invalid] = $this->services->handleCartList($uid, $data);
        return app('json')->success($data);
    }

    /**
     * 首页加入购物车
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCartNum(Request $request)
    {
        list($product_id, $num, $unique, $type) = $request->postMore([
            ['product_id', 0],
            ['num', 0],
            ['unique', ''],
            ['type', -1]
        ], true);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        if (!$product_id || !is_numeric($product_id)) return app('json')->fail(100100);
        $res = $cartService->setCartNum($request->uid(), $product_id, $num, $unique, $type);
        if ($res) return app('json')->success(100001);
        return app('json')->fail(100007);
    }
}
