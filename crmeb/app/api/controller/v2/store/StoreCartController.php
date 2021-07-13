<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\api\controller\v2\store;


use app\services\order\StoreCartServices;
use app\Request;
use app\services\product\product\StoreProductServices;
use app\services\user\UserLevelServices;

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
        return app('json')->successful('修改成功');
    }

    /**
     * 获取用户购物车
     * @param Request $request
     * @return mixed
     */
    public function getCartList(Request $request)
    {
        $uid = $request->uid();
        $data = $this->services->getCartList(['uid' => $uid, 'is_del' => 0, 'is_new' => 0, 'is_pay' => 0, 'combination_id' => 0, 'seckill_id' => 0, 'bargain_id' => 0], 0, 0, ['productInfo', 'attrInfo']);
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        foreach ($data as &$item) {
            $item['attrStatus'] = $item['attrInfo']['stock'] ? true : false;
            $item['productInfo']['attrInfo'] = $item['attrInfo'] ?? [];
            $item['productInfo']['attrInfo']['image'] = $item['attrInfo']['image'] ?? $item['productInfo']['image'] ?? '';
            $item['productInfo']['attrInfo']['suk'] = $item['attrInfo']['suk'] ?? '已失效';
            $productInfo = $item['productInfo'];
            if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                $item['truePrice'] = $productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid, true);
                $item['vip_truePrice'] = (float)$productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid);
            } else {
                $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                $item['truePrice'] = $productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid, true);
                $item['vip_truePrice'] = (float)$productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid);
            }
            unset($item['attrInfo']);
        }
        return app('json')->successful($data);
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
        if (!$product_id || !is_numeric($product_id)) return app('json')->fail('参数错误');
        $res = $cartService->setCartNum($request->uid(), $product_id, $num, $unique, $type);
        if ($res) return app('json')->successful('修改成功');
        return app('json')->fail('修改失败');
    }
}
