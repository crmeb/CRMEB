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

namespace app\services\order;

use crmeb\utils\Str;
use app\services\BaseServices;
use crmeb\services\CacheService;
use app\dao\order\StoreOrderCartInfoDao;

/**
 * Class StoreOrderCartInfoServices
 * @package app\services\order
 * @method array getCartColunm(array $where, string $field, ?string $key) 获取购物车信息以数组返回
 * @method array getCartInfoList(array $where, array $field) 获取购物车详情列表
 * @method getOne(array $where, ?string $field = '*', array $with = []) 根据条件获取一条数据
 */
class StoreOrderCartInfoServices extends BaseServices
{
    /**
     * StorePinkServices constructor.
     * @param StorePinkDao $dao
     */
    public function __construct(StoreOrderCartInfoDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取指定订单下的商品详情
     * @param int $oid
     * @return array|mixed
     */
    public function getOrderCartInfo(int $oid)
    {
        $cartInfo = CacheService::get(md5('store_order_cart_info_' . $oid));
        if ($cartInfo) return $cartInfo;
        $cart_info = $this->dao->getColumn(['oid' => $oid], 'cart_info', 'cart_id');
        $info = [];
        foreach ($cart_info as $k => $v) {
            $_info = is_string($v) ? json_decode($v, true) : $v;
            if (!isset($_info['productInfo'])) $_info['productInfo'] = [];
            $info[$k]['cart_info'] = $_info;
            unset($_info);
        }
        CacheService::set(md5('store_order_cart_info_' . $oid), $info);
        return $info;
//        return CacheService::get(md5('store_order_cart_info_' . $oid), function () use ($oid) {
//            $cart_info = $this->dao->getColumn(['oid' => $oid], 'cart_info', 'cart_id');
//            $info = [];
//            foreach ($cart_info as $k => $v) {
//                $_info = is_string($v) ? json_decode($v, true) : $v;
//                if (!isset($_info['productInfo'])) $_info['productInfo'] = [];
//                $info[$k]['cart_info'] = $_info;
//                unset($_info);
//            }
//            return $info;
//        }) ?: [];
    }

    /**
     * 查找购物车里的所有商品标题
     * @param $cartId
     * @return bool|string
     */
    public function getCarIdByProductTitle($cartId, $goodsNum = false)
    {
        $title = '';
        $orderCart = $this->dao->getCartInfoList(['cart_id' => $cartId], ['cart_info']);
        foreach ($orderCart as $item) {
            if (isset($item['cart_info']['productInfo']['store_name'])) {
                if ($goodsNum && isset($item['cart_info']['cart_num'])) {
                    $title .= $item['cart_info']['productInfo']['store_name'] . ' * '.$item['cart_info']['cart_num'].' | ';
                }else{
                    $title .= $item['cart_info']['productInfo']['store_name'] . '|';
                }

            }
        }
        if ($title) {
            $title = substr($title, 0, strlen($title) - 1);
        }
        return $title;
    }

    /**
     * 获取打印订单的商品信息
     * @param array $cartId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCartInfoPrintProduct(array $cartId)
    {
        $cartInfo = $this->dao->getCartInfoList(['cart_id' => $cartId], ['cart_info']);
        $product = [];
        foreach ($cartInfo as $item) {
            $value = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
            $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
            $value['productInfo']['store_name'] = Str::substrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
            $product[] = $value;
        }
        return $product;
    }


    /**
     * 保存购物车info
     * @param $oid
     * @param array $cartInfo
     * @return int
     */
    public function setCartInfo($oid, array $cartInfo)
    {
        $group = [];
        foreach ($cartInfo as $cart) {
            $group[] = [
                'oid' => $oid,
                'cart_id' => $cart['id'],
                'product_id' => $cart['productInfo']['id'],
                'cart_info' => json_encode($cart),
                'unique' => md5($cart['id'] . '' . $oid)
            ];
        }
        return $this->dao->saveAll($group);
    }

    /**
     * 商品编号
     * @param $cartId
     * @return array
     */
    public function getCartIdsProduct($cartId)
    {
        return $this->dao->getColumn([['cart_id', 'in', $cartId]], 'product_id', 'oid');
    }
}
