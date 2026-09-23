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
namespace app\api\controller\v1\store;

use app\Request;
use app\services\order\StoreCartServices;

/**
 * 购物车类
 * Class StoreCartController
 * @package app\api\controller\store
 */
class StoreCartController
{
    /**
     * @var StoreCartServices
     */
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
        // is_new 字段已删除，不再作为查询条件
        if (!empty($where['new'])) $new = true;
        else $new = false;
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        if (!$where['productId'] || !is_numeric($where['productId'])) return app('json')->fail('参数错误');
        $type = 0;
        if ($where['secKillId']) {
            $type = 1;
        } elseif ($where['bargainId']) {
            $type = 2;
        } elseif ($where['combinationId']) {
            $type = 3;
            if ($where['pinkId']) {
                //通过事件代理校验拼团状态，避免 app 层直接引用 modules
                $results = event('ActivityDataProxy', ['mode' => 'combination', 'sub_service' => 'pink', 'action' => 'isPinkStatus', 'args' => [$where['pinkId']]]);
                if (proxy_event_result($results, false)) return app('json')->fail('拼团已到期');
            }
        } elseif ($where['advanceId']) {
            $type = 6;
        }
        if ($type == 0) $cartService->checkVipGoodsBuy($request->user(), $where['productId']);
        $res = $cartService->setCart($request->uid(), $where['productId'], $where['cartNum'], $where['uniqueId'], $type, $new, $where['combinationId'], $where['secKillId'], $where['bargainId'], $where['advanceId']);
        if (!$res) return app('json')->fail('添加失败');
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
            return app('json')->fail('参数错误');
        if ($this->services->removeUserCart((int)$request->uid(), $where['ids']))
            return app('json')->success('删除成功');
        return app('json')->fail('删除失败');
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
        if (!$where['id'] || !is_numeric($where['id'])) return app('json')->fail('参数错误');
        if (!$where['number'] || !is_numeric($where['number'])) return app('json')->fail('修改失败');
        $res = $this->services->changeUserCartNum($where['id'], $where['number'], $request->uid());
        if ($res) return app('json')->success('修改成功');
        else return app('json')->fail('修改失败');
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
        return app('json')->success('重选成功');
    }

    /**
     * 购物车重选(v2)
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
        return app('json')->success('修改成功');
    }

    /**
     * 获取用户购物车(v2)
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
     * 首页加入购物车(v2)
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
            ['num', 1],
            ['unique', ''],
            ['type', -1]
        ], true);
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        if (!$product_id || !is_numeric($product_id)) return app('json')->fail('参数错误');
        $res = $cartService->setCartNum($request->uid(), $product_id, $num, $unique, $type);
        if ($res) return app('json')->success('加入购物车成功!');
        return app('json')->fail('加入购物车失败!');
    }
}
