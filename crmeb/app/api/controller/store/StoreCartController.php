<?php
namespace app\api\controller\store;

use app\models\store\StoreBargainUserHelp;
use app\models\store\StoreCart;
use app\Request;
use crmeb\services\UtilService;

/**
 * 购物车类
 * Class StoreCartController
 * @package app\api\controller\store
 */
class StoreCartController
{

    /**
     * 购物车 列表
     * @param Request $request
     * @return mixed
     */
    public function lst(Request $request)
    {
        return app('json')->successful(StoreCart::getUserProductCartList($request->uid()));
    }

    /**
     * 购物车 添加
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request)
    {
        list($productId, $cartNum, $uniqueId, $combinationId, $secKillId, $bargainId, $new) = UtilService::postMore([
            ['productId',0],//普通产品编号
            ['cartNum',1], //购物车数量
            ['uniqueId',''],//属性唯一值
            ['combinationId',0],//拼团产品编号
            ['secKillId',0],//秒杀产品编号
            ['bargainId',0],//砍价产品编号
            ['new',1], // 1 加入购物车直接购买  0 加入购物车
        ], $request, true);
        if (!$productId || !is_numeric($productId)) return app('json')->fail('参数错误');
        if ($bargainId && StoreBargainUserHelp::getSurplusPrice($bargainId, $request->uid())) return app('json')->fail('请先砍价');
        $res = StoreCart::setCart($request->uid(), $productId, $cartNum, $uniqueId, 'product', $new, $combinationId, $secKillId, $bargainId);
        if (!$res) return app('json')->fail(StoreCart::getErrorInfo());
        else  return app('json')->successful('ok', ['cartId' => $res->id]);
    }

    /**
     * 购物车 删除产品
     * @param Request $request
     * @return mixed
     */
    public function del(Request $request)
    {
        list($ids) = UtilService::postMore([
            ['ids',[]],//购物车编号
        ], $request, true);
        if (!count($ids))
            return app('json')->fail('参数错误!');
        if(StoreCart::removeUserCart($request->uid(), $ids))
            return app('json')->successful();
        return app('json')->fail('清除失败！');
    }

    /**
     * 购物车 修改产品数量
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function num(Request $request)
    {
        list($id, $number) = UtilService::postMore([
            ['id',0],//购物车编号
            ['number',0],//购物车编号
        ], $request, true);
        if (!$id || !$number || !is_numeric($id) || !is_numeric($number)) return app('json')->fail('参数错误!');
        $res = StoreCart::changeUserCartNum($id, $number, $request->uid());
        if ($res)  return app('json')->successful();
        else return app('json')->fail(StoreCart::getErrorInfo('修改失败'));
    }

    /**
     * 购物车 获取数量
     * @param Request $request
     * @return mixed
     */
    public function count(Request $request)
    {
        list($numType) = UtilService::postMore([
            ['numType',true],//购物车编号
        ], $request, true);
        if(!(int)$numType) $numType = false;
        return  app('json')->success('ok', ['count'=>StoreCart::getUserCartNum($request->uid(), 'product', $numType)]);
    }

}