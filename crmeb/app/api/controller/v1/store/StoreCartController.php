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
namespace app\api\controller\v1\store;

use app\Request;
use app\services\activity\combination\StorePinkServices;
use app\services\order\StoreCartServices;

/**
 * 购物车类
 * Class StoreCartController
 * @package app\api\controller\store
 */
class StoreCartController
{
    protected $services;

    public function __construct(StoreCartServices $services)
    {
        $this->services = $services;
    }

    /**
     * 购物车 列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function lst(Request $request)
    {
        [$status] = $request->postMore([
            ['status', 1],//购物车商品状态
        ], true);
        return app('json')->success($this->services->getUserCartList($request->uid(), $status));
    }

    /**
     * 购物车 添加
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(Request $request)
    {
        $where = $request->postMore([
            [['productId', 'd'], 0],//普通商品编号
            [['cartNum', 'd'], 1], //购物车数量
            ['uniqueId', ''],//属性唯一值
            [['new', 'd'], 0],// 1 加入购物车直接购买  0 加入购物车
            [['is_new', 'd'], 0],// 1 加入购物车直接购买  0 加入购物车
            [['combinationId', 'd'], 0],//拼团商品编号
            [['secKillId', 'd'], 0],//秒杀商品编号
            [['bargainId', 'd'], 0],//砍价商品编号
            [['advanceId', 'd'], 0],//预售商品编号
            [['pinkId', 'd'], 0],//拼团团队ID
        ]);
        if ($where['is_new'] || $where['new']) $new = true;
        else $new = false;
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        if (!$where['productId'] || !is_numeric($where['productId'])) return app('json')->fail(100100);
        $type = 0;
        if ($where['secKillId']) {
            $type = 1;
        } elseif ($where['bargainId']) {
            $type = 2;
        } elseif ($where['combinationId']) {
            $type = 3;
            if ($where['pinkId']) {
                /** @var StorePinkServices $pinkServices */
                $pinkServices = app()->make(StorePinkServices::class);
                if ($pinkServices->isPinkStatus($where['pinkId'])) return app('json')->fail(410315);
            }
        } elseif ($where['advanceId']) {
            $type = 6;
        }
        $res = $cartService->setCart($request->uid(), $where['productId'], $where['cartNum'], $where['uniqueId'], $type, $new, $where['combinationId'], $where['secKillId'], $where['bargainId'], $where['advanceId']);
        if (!$res) return app('json')->fail(100022);
        else  return app('json')->success(['cartId' => $res]);
    }

    /**
     * 购物车 删除商品
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        $where = $request->postMore([
            ['ids', ''],//购物车编号
        ]);
        $where['ids'] = is_array($where['ids']) ? $where['ids'] : explode(',', $where['ids']);
        if (!count($where['ids']))
            return app('json')->fail(100100);
        if ($this->services->removeUserCart((int)$request->uid(), $where['ids']))
            return app('json')->success(100002);
        return app('json')->fail(100008);
    }

    /**
     * 购物车 修改商品数量
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function num(Request $request)
    {
        $where = $request->postMore([
            ['id', 0],//购物车编号
            ['number', 0],//购物车编号
        ]);
        if (!$where['id'] || !$where['number'] || !is_numeric($where['id']) || !is_numeric($where['number'])) return app('json')->fail(100100);
        $res = $this->services->changeUserCartNum($where['id'], $where['number'], $request->uid());
        if ($res) return app('json')->success(100001);
        else return app('json')->fail(100007);
    }

    /**
     * 购物车 统计 数量 价格
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function count(Request $request)
    {
        [$numType] = $request->postMore([
            ['numType', true],//购物车编号
        ], true);
        $uid = (int)$request->uid();
        return app('json')->success($this->services->getUserCartCount($uid, $numType));
    }

    /**
     * 购物车重选
     * @param Request $request
     * @return mixed
     */
    public function reChange(Request $request)
    {
        [$cart_id, $product_id, $unique] = $request->postMore([
            ['cart_id', 0],
            ['product_id', 0],
            ['unique', '']
        ], true);
        $this->services->modifyCart($cart_id, $product_id, $unique);
        return app('json')->success(410225);
    }
}
