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
use think\exception\ValidateException;

/**
 * Class StoreOrderCartInfoServices
 * @package app\services\order
 * @method array getCartColunm(array $where, string $field, ?string $key) 获取购物车信息以数组返回
 * @method array getCartInfoList(array $where, array $field) 获取购物车详情列表
 * @method getSplitCartNum(array $cart_id)
 * @method getOne(array $where, ?string $field = '*', array $with = []) 根据条件获取一条数据
 */
class StoreOrderCartInfoServices extends BaseServices
{
    /**
     * StoreOrderCartInfoServices constructor.
     * @param StoreOrderCartInfoDao $dao
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
            //缩略图处理
            if (isset($_info['productInfo']['attrInfo'])) {
                $_info['productInfo']['attrInfo'] = get_thumb_water($_info['productInfo']['attrInfo']);
            }
            $_info['productInfo'] = get_thumb_water($_info['productInfo']);
            $info[$k]['cart_info'] = $_info;
            unset($_info);
        }
        CacheService::set(md5('store_order_cart_info_' . $oid), $info);
        return $info;
    }

    /**
     * 查找购物车里的所有商品标题
     * @param int $oid
     * @param $cartId
     * @param false $goodsNum
     * @return bool|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCarIdByProductTitle(int $oid, $cartId, $goodsNum = false)
    {
        $key = md5('store_order_cart_product_title_' . $oid . '_' . is_array($cartId) ? implode('_', $cartId) : $cartId);
        $title = CacheService::get($key);
        if (!$title) {
            $orderCart = $this->dao->getCartInfoList(['oid' => $oid, 'cart_id' => $cartId], ['cart_info']);
            foreach ($orderCart as $item) {
                if (isset($item['cart_info']['productInfo']['store_name'])) {
                    if ($goodsNum && isset($item['cart_info']['cart_num'])) {
                        $title .= $item['cart_info']['productInfo']['store_name'] . ' * ' . $item['cart_info']['cart_num'] . ' | ';
                    } else {
                        $title .= $item['cart_info']['productInfo']['store_name'] . '|';
                    }
                }
            }
            if ($title) {
                $title = substr($title, 0, strlen($title) - 1);
            }
            CacheService::set($key, $title);
        }
        return $title ? $title : '';
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
     * 获取产品返佣金额
     * @param array $cartId
     * @param bool $type true = 一级返佣, fasle = 二级返佣
     * @return string
     */
    public function getProductBrokerage(array $cartId, bool $type = true)
    {
        $cartInfo = $this->dao->getCartInfoList(['cart_id' => $cartId], ['cart_info']);
        $oneBrokerage = '0';//一级返佣金额
        $twoBrokerage = '0';//二级返佣金额
        $sumProductPrice = '0';//非指定返佣商品总金额
        foreach ($cartInfo as $value) {
            $cartNum = $value['cart_info']['cart_num'] ?? 0;
            if (isset($value['cart_info']['productInfo'])) {
                $productInfo = $value['cart_info']['productInfo'];
                //指定返佣金额
                if (isset($productInfo['is_sub']) && $productInfo['is_sub'] == 1) {
                    $oneBrokerage = bcadd($oneBrokerage, bcmul($cartNum, $productInfo['attrInfo']['brokerage'] ?? 0, 2), 2);
                    $twoBrokerage = bcadd($twoBrokerage, bcmul($cartNum, $productInfo['attrInfo']['brokerage_two'] ?? 0, 2), 2);
                } else {
                    //比例返佣
                    if (isset($productInfo['attrInfo'])) {
                        $sumProductPrice = bcadd($sumProductPrice, bcmul($cartNum, $productInfo['attrInfo']['price'] ?? 0, 2), 2);
                    } else {
                        $sumProductPrice = bcadd($sumProductPrice, bcmul($cartNum, $productInfo['price'] ?? 0, 2), 2);
                    }
                }
            }
        }
        if ($type) {
            //获取后台一级返佣比例
            $storeBrokerageRatio = sys_config('store_brokerage_ratio');
            //一级返佣比例 小于等于零时直接返回 不返佣
            if ($storeBrokerageRatio <= 0) {
                return $oneBrokerage;
            }
            //计算获取一级返佣比例
            $brokerageRatio = bcdiv($storeBrokerageRatio, 100, 4);
            $brokeragePrice = bcmul($sumProductPrice, $brokerageRatio, 2);
            //固定返佣 + 比例返佣 = 一级总返佣金额
            return bcadd($oneBrokerage, $brokeragePrice, 2);
        } else {
            //获取二级返佣比例
            $storeBrokerageTwo = sys_config('store_brokerage_two');
            //二级返佣比例小于等于0 直接返回
            if ($storeBrokerageTwo <= 0) {
                return $twoBrokerage;
            }
            //计算获取二级返佣比例
            $brokerageRatio = bcdiv($storeBrokerageTwo, 100, 4);
            $brokeragePrice = bcmul($sumProductPrice, $brokerageRatio, 2);
            //固定返佣 + 比例返佣 = 二级总返佣金额
            return bcadd($twoBrokerage, $brokeragePrice, 2);
        }
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
                'cart_num' => $cart['cart_num'],
                'surplus_num' => $cart['cart_num'],
                'unique' => md5($cart['id'] . '' . $oid)
            ];
        }
        return $this->dao->saveAll($group);
    }

    /**
     * 订单创建成功之后计算订单（实际优惠、积分、佣金、上级、上上级）
     * @param $oid
     * @param array $cartInfo
     * @return bool
     */
    public function updateCartInfo($oid, array $cartInfo)
    {
        foreach ($cartInfo as $cart) {
            $group = [
                'cart_info' => json_encode($cart)
            ];
            $this->dao->update(['oid' => $oid, 'cart_id' => $cart['id']], $group);
        }
        return true;
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

    /**
     * 获取某个订单还可以拆分商品 split_status 0：未拆分1：部分拆分2：拆分完成
     * @param int $oid
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getSplitCartList(int $oid, string $field = '*', string $key = 'cart_id')
    {
        $cartInfo = $this->dao->getColumn([['oid', '=', $oid], ['split_status', 'IN', [0, 1]]], $field, $key);
        foreach ($cartInfo as &$item) {
            if ($field == 'cart_info') {
                $item = is_string($item) ? json_decode($item, true) : $item;
            } else {
                if (isset($item['cart_info'])) $item['cart_info'] = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
                if (isset($item['cart_num']) && !$item['cart_num']) {//兼容之前老数据
                    $item['cart_num'] = $item['cart_info']['cart_num'] ?? 0;
                }
            }
        }
        return $cartInfo;
    }

    /**
     * 检测这些商品是否还可以拆分
     * @param int $oid
     * @param array $cart_data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkCartIdsIsSplit(int $oid, array $cart_data)
    {
        if (!$cart_data) return false;
        $ids = array_unique(array_column($cart_data, 'cart_id'));
        if ($this->dao->getCartInfoList(['oid' => $oid, 'cart_id' => $ids, 'split_status' => 2], ['cart_id'])) {
            throw new ValidateException('您选择的商品已经拆分完成，请刷新或稍后重新选择');
        }
        $cartInfo = $this->getSplitCartList($oid, 'surplus_num,cart_info,cart_num', 'cart_id');
        if (!$cartInfo) {
            throw new ValidateException('该订单已发货完成');
        }
        foreach ($cart_data as $cart) {
            $surplus_num = $cartInfo[$cart['cart_id']]['surplus_num'] ?? 0;
            if (!$surplus_num) {//兼容之前老数据
                $_info = $cartInfo[$cart['cart_id']]['cart_info'];
                $surplus_num = $_info['cart_num'] ?? 0;
            }
            if ($cart['cart_num'] > $surplus_num) {
                throw new ValidateException('您选择商品拆分数量大于购买数量');
            }
        }
        return true;
    }
}
