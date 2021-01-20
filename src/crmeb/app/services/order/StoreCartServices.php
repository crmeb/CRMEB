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
declare (strict_types=1);

namespace app\services\order;

use app\services\BaseServices;
use app\dao\order\StoreCartDao;
use crmeb\jobs\ProductLogJob;
use crmeb\services\CacheService;
use crmeb\utils\Queue;
use think\exception\ValidateException;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrValueServices;

/**
 *
 * Class StoreCartServices
 * @package app\services\order
 * @method updateCartStatus($cartIds) 修改购物车状态
 * @method getUserCartNum(int $uid, string $type, int $numType) 购物车数量
 * @method deleteCartStatus(array $cartIds) 修改购物车状态
 * @method array productIdByCartNum(array $ids, int $uid)  根据商品id获取购物车数量
 * @method getCartList(array $where, ?int $page = 0, ?int $limit = 0, ?array $with = []) 获取用户购物车
 * @method getSum($where, $field) 求和
 * @method getProductTrend($time, $timeType, $str) 购物车趋势
 */
class StoreCartServices extends BaseServices
{

    /**
     * StoreCartServices constructor.
     * @param StoreCartDao $dao
     */
    public function __construct(StoreCartDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取某个用户下的购物车数量
     * @param array $unique
     * @param int $productId
     * @param int $uid
     * @return array
     */
    public function getUserCartNums(array $unique, int $productId, int $uid)
    {
        $where['is_pay'] = 0;
        $where['is_del'] = 0;
        $where['is_new'] = 0;
        $where['type'] = 'product';
        $where['product_id'] = $productId;
        $where['uid'] = $uid;
        return $this->dao->getUserCartNums($where, $unique);
    }

    /**
     * 获取用户下的购物车列表
     * @param $uid
     * @param string $cartIds
     * @param bool $new
     * @param int $status
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserProductCartListV1($uid, $cartIds = '', bool $new, $status = 0)
    {
        if ($new) {
            $cartIds = explode(',', $cartIds);
            $cartInfo = [];
            foreach ($cartIds as $key) {
                $info = CacheService::redisHandler()->get($key);
                if ($info) {
                    $cartInfo[] = $info;
                }
            }
            if (!$cartInfo) {
                throw new ValidateException('获取购物车信息失败');
            }
            foreach ($cartInfo as &$item) {
                $productInfo = $item['productInfo'];
                if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                    $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                    $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                    $item['truePrice'] = $productInfo['attrInfo']['price'];
                    $item['vip_truePrice'] = 0;
                } else {
                    $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                    $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                    $item['truePrice'] = $productInfo['productInfo']['price'];
                    $item['vip_truePrice'] = 0;
                }
            }
            return ['valid' => $cartInfo, 'invalid' => []];
        } else {
            return $this->getUserCartList($uid, $status, $cartIds);
        }
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getCartId($prefix)
    {
        $snowflake = new \Godruoyi\Snowflake\Snowflake();
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->id();
        }
        return $prefix . $id;
    }

    /**
     * 验证库存
     * @param int $uid
     * @param int $cartNum
     * @param string $unique
     * @param $productId
     * @param int $seckillId
     * @param int $bargainId
     * @param int $combinationId
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkProductStock(int $uid, int $cartNum, string $unique, $productId)
    {
        if ($cartNum < 1) $cartNum = 1;
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $productId, 'type' => 0], 'unique');
        }
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        $res = $attrValueServices->getOne(['unique' => $unique, 'type' => 0]);
        if (!$productServices->isValidProduct($productId)) {
            throw new ValidateException('该商品已下架或删除');
        }
        if (!($unique && $attrValueServices->getAttrvalueCount($productId, $unique, 0))) {
            throw new ValidateException('请选择有效的商品属性');
        }
        if ($productServices->getProductStock($productId, $unique) < $cartNum) {
            throw new ValidateException('该商品库存不足' . $cartNum);
        }

        return [$res, $unique, $cartNum];
    }

    /**
     * 添加购物车
     * @param int $uid 用户UID
     * @param int $product_id 商品ID
     * @param int $cart_num 商品数量
     * @param string $product_attr_unique 商品SKU
     * @param string $type 添加购物车类型
     * @param bool $new true = 立即购买，false = 加入购物车
     * @param int $combination_id 拼团商品ID
     * @param int $seckill_id 秒杀商品ID
     * @param int $bargain_id 砍价商品ID
     * @return mixed|string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCart(int $uid, int $product_id, int $cart_num = 1, string $product_attr_unique = '', string $type = 'product', bool $new = false)
    {
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        [$res, $product_attr_unique, $cart_num] = $this->checkProductStock($uid, $cart_num, $product_attr_unique, (int)$product_id);
        if ($new) {
            $key = $this->getCartId($uid);
            $info['id'] = $key;
            $info['type'] = $type;
            $info['product_id'] = $product_id;
            $info['product_attr_unique'] = $product_attr_unique;
            $info['cart_num'] = $cart_num;
            $productInfoField = ['id', 'image', 'cate_id', 'price', 'ot_price', 'vip_price', 'postage', 'give_integral', 'sales', 'stock', 'store_name', 'unit_name', 'is_show', 'is_del', 'is_postage', 'cost', 'is_sub', 'temp_id', 'is_vip'];
            $info['productInfo'] = $productServices->get($product_id, $productInfoField)->toArray();
            $info['productInfo']['attrInfo'] = $res->toArray();
            $info['truePrice'] = $info['productInfo']['attrInfo']['price'] ?? $info['productInfo']['price'] ?? 0;
            $info['vip_truePrice'] = 0;
            $info['trueStock'] = $info['productInfo']['attrInfo']['stock'];
            $info['costPrice'] = $info['productInfo']['attrInfo']['cost'];
            try {
                CacheService::redisHandler()->set($key, $info, 3600);
            } catch (\Throwable $e) {
                throw new ValidateException($e->getMessage());
            }
            return $key;
        } else {
            //加入购物车记录
            Queue::instance()->job(ProductLogJob::class)->data('cart', ['uid' => $uid, 'product_id' => $product_id, 'cart_num' => $cart_num])->push();
            $cart = $this->dao->getOne(['type' => $type, 'uid' => $uid, 'product_id' => $product_id, 'product_attr_unique' => $product_attr_unique, 'is_del' => 0, 'is_new' => 0, 'is_pay' => 0]);
            if ($cart) {
                $cart->cart_num = $cart_num + $cart->cart_num;
                $cart->add_time = time();
                $cart->save();
                return $cart->id;
            } else {
                $add_time = time();
                return $this->dao->save(compact('uid', 'product_id', 'cart_num', 'product_attr_unique', 'type', 'add_time'))->id;
            }
        }

    }

    /**移除购物车商品
     * @param int $uid
     * @param array $ids
     * @return StoreCartDao|bool
     */
    public function removeUserCart(int $uid, array $ids)
    {
        if (!$uid || !$ids) return false;
        return $this->dao->removeUserCart($uid, $ids);
    }

    /**购物车 修改商品数量
     * @param $id
     * @param $number
     * @param $uid
     * @return bool|\crmeb\basic\BaseModel
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function changeUserCartNum($id, $number, $uid)
    {
        if (!$id || !$number || !$uid) return false;
        $where = ['uid' => $uid, 'id' => $id];
        $carInfo = $this->dao->getOne($where, 'product_id,combination_id,seckill_id,bargain_id,product_attr_unique,cart_num');
        /** @var StoreProductServices $StoreProduct */
        $StoreProduct = app()->make(StoreProductServices::class);
        $stock = $StoreProduct->getProductStock($carInfo->product_id, $carInfo->product_attr_unique);
        if (!$stock) throw new ValidateException('暂无库存');
        if (!$number) throw new ValidateException('库存错误');
        if ($stock < $number) throw new ValidateException('库存不足' . $number);
        if ($carInfo->cart_num == $number) return true;
        return $this->dao->changeUserCartNum(['uid' => $uid, 'id' => $id], $number);
    }

    /**
     * 修改购物车状态
     * @param int $productId
     * @param int $type 1 商品下架
     */
    public function changeStatus(int $productId)
    {
        $this->dao->update($productId, ['status' => 0], 'product_id');
    }

    /**
     * 获取购物车列表
     * @param int $uid
     * @param int $status
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCartList(int $uid, int $status, string $cartIds = '')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getCartList(['uid' => $uid, 'status' => $status, 'id' => $cartIds], $page, $limit, ['productInfo', 'attrInfo']);
        foreach ($list as &$item) {
            $item['attrStatus'] = $item['attrInfo']['stock'] ? true : false;
            $item['productInfo']['attrInfo'] = $item['attrInfo'] ?? [];
            $item['productInfo']['attrInfo']['image'] = $item['attrInfo']['image'] ?? $item['productInfo']['image'];
            $item['productInfo']['attrInfo']['suk'] = $item['attrInfo']['suk'] ?? '已失效';
            $productInfo = $item['productInfo'];
            if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                $item['truePrice'] = (float)$productInfo['attrInfo']['price'] ?? 0;
                $item['vip_truePrice'] = (float)$productInfo['attrInfo']['price'] ?? 0;
            } else {
                $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                $item['truePrice'] = $item['productInfo']['price'] ?? 0;
                $item['vip_truePrice'] = (float)$item['productInfo']['price'] ?? 0;
            }
            unset($item['attrInfo']);
        }
        if ($status == 1) {
            return ['valid' => $list, 'invalid' => []];
        } else {
            return ['valid' => [], 'invalid' => $list];
        }
    }

    /**
     * 购物车重选
     * @param int $cart_id
     * @param int $product_id
     * @param string $unique
     */
    public function modifyCart(int $cart_id, int $product_id, string $unique)
    {
        /** @var StoreProductAttrValueServices $attrService */
        $attrService = app()->make(StoreProductAttrValueServices::class);
        $stock = $attrService->value(['product_id' => $product_id, 'unique' => $unique, 'type' => 0], 'stock');
        if ($stock > 0) {
            $this->dao->update($cart_id, ['product_attr_unique' => $unique, 'cart_num' => 1]);
        } else {
            throw new ValidateException('选择的规格库存不足');
        }
    }

    /**
     * 重选购物车
     * @param $id
     * @param $uid
     * @param $productId
     * @param $unique
     * @param $num
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function resetCart($id, $uid, $productId, $unique, $num)
    {
        $res = $this->dao->getOne(['uid' => $uid, 'product_id' => $productId, 'product_attr_unique' => $unique]);
        if ($res) {
            $res->cart_num = $res->cart_num + $num;
            $res->save();
            $this->dao->delete($id);
        } else {
            $this->dao->update($id, ['product_attr_unique' => $unique, 'cart_num' => $num]);
        }
    }

    /**
     * 首页加入购物车
     * @param $uid
     * @param $productId
     * @param $num
     * @param $unique
     * @param $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCartNum($uid, $productId, $num, $unique, $type)
    {
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);

        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $productId, 'type' => 0], 'unique');
        }
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);

        if (!$productServices->isValidProduct((int)$productId)) {
            throw new ValidateException('该商品已下架或删除');
        }
        if (!($unique && $attrValueServices->getAttrvalueCount($productId, $unique, 0))) {
            throw new ValidateException('请选择有效的商品属性');
        }
        if ($productServices->getProductStock((int)$productId, $unique) < $num) {
            throw new ValidateException('该商品库存不足' . $num);
        }

        $cart = $this->dao->getOne(['type' => 'product', 'uid' => $uid, 'product_id' => $productId, 'product_attr_unique' => $unique]);
        if ($cart) {
            if ($type == -1) {
                $cart->cart_num = $num;
            } elseif ($type == 0) {
                $cart->cart_num = $cart->cart_num - $num;
            } elseif ($type == 1) {
                $cart->cart_num = $cart->cart_num + $num;
            }
            if ($cart->cart_num === 0) {
                return $this->dao->delete($cart->id);
            } else {
                $cart->add_time = time();
                $cart->save();
                return $cart->id;
            }
        } else {
            $data = [
                'uid' => $uid,
                'product_id' => $productId,
                'cart_num' => $num,
                'product_attr_unique' => $unique,
                'type' => 'product',
                'add_time' => time()
            ];
            return $this->dao->save($data)->id;
        }
    }
}
