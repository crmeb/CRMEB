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
declare (strict_types=1);

namespace app\services\order;


use crmeb\basic\BaseService;
use app\dao\order\StoreCartDao;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;
use app\jobs\ProductLogJob;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\product\ProductTypeRegistry;
use crmeb\utils\SnowflakeIdGenerator;
use app\services\product\CenterProductServices;

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
class StoreCartServices extends BaseService
{
    /**
     * @var StoreCartDao
     */
    protected $dao;

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
        // is_new 字段已删除
        $where['product_id'] = $productId;
        $where['uid'] = $uid;
        return $this->dao->getUserCartNums($where, $unique);
    }

    /**
     * 获取用户下的购物车列表
     * @param $uid
     * @param string $cartIds
     * @param bool $new
     * @param array $addr
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserProductCartListV1($uid, $cartIds = '', bool $new = false, $addr = [], int $shipping_type = 1, $is_gift = 0)
    {
        if ($new) {
            $cartIds = explode(',', $cartIds);
            $cartInfo = [];
            foreach ($cartIds as $key) {
                $info = CacheService::get($key);
                if ($info) {
                    $cartInfo[] = $info;
                }
            }
        } else {
            $cartInfo = $this->dao->getCartList(['uid' => $uid, 'status' => 1, 'id' => $cartIds], 0, 0, ['productInfo', 'attrInfo']);
        }
        if (!$cartInfo) {
            throw new ApiException('获取购物车信息失败');
        }
        if ($is_gift == 1) {
            $addr = [];
            $shipping_type = 0;
        }
        [$cartInfo, $valid, $invalid] = $this->handleCartList($uid, $cartInfo, $addr, $shipping_type);
        $seckillIds = array_unique(array_column($cartInfo, 'seckill_id'));
        $bargainIds = array_unique(array_column($cartInfo, 'bargain_id'));
        $combinationId = array_unique(array_column($cartInfo, 'combination_id'));
        $advanceId = array_unique(array_column($cartInfo, 'advance_id'));
        $deduction = ['seckill_id' => $seckillIds[0] ?? 0, 'bargain_id' => $bargainIds[0] ?? 0, 'combination_id' => $combinationId[0] ?? 0, 'advance_id' => $advanceId[0] ?? 0];
        return ['cartInfo' => $cartInfo, 'valid' => $valid, 'invalid' => $invalid, 'deduction' => $deduction];
    }

    /**
     * 按当前商品和 SKU 数据重建普通商品的立即购买快照。
     *
     * 立即购买模式的购物车项只保存在缓存中，不能再次从数据库购物车表查询。
     * 因此下单前需要使用缓存中的商品ID、SKU和数量重新读取商品及 SKU，
     * 再经过统一购物车处理流程计算当前用户价格和库存状态。
     *
     * @param int $uid 用户ID
     * @param array $cachedCartInfo 确认页缓存中的购物车商品
     * @param array $addr 收货地址
     * @param int $shipping_type 配送方式：1快递，2自提，3上门
     * @param int $is_gift 是否礼物订单
     * @return array 返回 cartInfo、valid、invalid、deduction
     * @throws ApiException 商品或 SKU 不存在时抛出
     */
    public function rebuildNormalCheckoutCart(
        int $uid,
        array $cachedCartInfo,
        array $addr = [],
        int $shipping_type = 1,
        int $is_gift = 0
    ): array {
        if (empty($cachedCartInfo)) {
            throw new ApiException('获取购物车信息失败');
        }

        $cartInfo = [];
        foreach ($cachedCartInfo as $cachedItem) {
            $productId = (int)($cachedItem['product_id'] ?? $cachedItem['productInfo']['id'] ?? 0);
            $skuUnique = (string)($cachedItem['product_attr_unique']
                ?? $cachedItem['productInfo']['attrInfo']['unique']
                ?? '');
            $cartNum = (int)($cachedItem['cart_num'] ?? 0);
            if ($productId <= 0 || $cartNum <= 0) {
                throw new ApiException('订单商品信息有误，请返回确认页重新提交');
            }

            // 普通商品下单前重新读取商品与 SKU，不能继续使用确认页里的旧价格和库存。
            [$attrInfo, $skuUnique, $ignoredBargainPriceMin, $cartNum, $productInfo]
                = $this->checkProductStock($uid, $cartNum, $skuUnique, 0, $productId);
            if (is_object($productInfo)) {
                $productInfo = $productInfo->toArray();
            }
            $productInfo = is_array($productInfo) ? $productInfo : [];
            if (($productInfo['product_type'] ?? 'normal') !== 'normal') {
                throw new ApiException('商品类型已变化，请返回确认页重新提交');
            }

            if (is_object($attrInfo)) {
                $attrInfo = $attrInfo->toArray();
            }
            $attrInfo = is_array($attrInfo) ? $attrInfo : [];
            $productInfo['attrInfo'] = $attrInfo;

            $cartInfo[] = [
                'id'                  => $cachedItem['id'] ?? '',
                'type'                => 0,
                'seckill_id'          => 0,
                'bargain_id'          => 0,
                'combination_id'      => 0,
                'advance_id'          => 0,
                'product_id'          => $productId,
                'product_attr_unique' => $skuUnique,
                'cart_num'            => $cartNum,
                'productInfo'         => $productInfo,
                'attrInfo'            => $attrInfo,
                'sum_price'           => $attrInfo['price'] ?? $productInfo['price'] ?? 0,
                'truePrice'           => $attrInfo['price'] ?? $productInfo['price'] ?? 0,
                'vip_truePrice'       => 0,
                'trueStock'           => $attrInfo['stock'] ?? $productInfo['stock'] ?? 0,
                'costPrice'           => $attrInfo['cost'] ?? $productInfo['cost'] ?? 0,
            ];
        }

        if ($is_gift == 1) {
            $addr = [];
            $shipping_type = 0;
        }
        [$cartInfo, $valid, $invalid] = $this->handleCartList($uid, $cartInfo, $addr, $shipping_type);

        $seckillIds = array_unique(array_column($cartInfo, 'seckill_id'));
        $bargainIds = array_unique(array_column($cartInfo, 'bargain_id'));
        $combinationIds = array_unique(array_column($cartInfo, 'combination_id'));
        $advanceIds = array_unique(array_column($cartInfo, 'advance_id'));
        $deduction = [
            'seckill_id'     => $seckillIds[0] ?? 0,
            'bargain_id'     => $bargainIds[0] ?? 0,
            'combination_id' => $combinationIds[0] ?? 0,
            'advance_id'     => $advanceIds[0] ?? 0,
        ];

        return ['cartInfo' => $cartInfo, 'valid' => $valid, 'invalid' => $invalid, 'deduction' => $deduction];
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getCartId($prefix)
    {
        return SnowflakeIdGenerator::generate((string)$prefix);
    }

    /**
     * 验证库存（加购/立即购买时调用）
     * 交易模式库存校验通过 TradeModeRegistry 按旧版数字 type 解析处理器，各交易模式 Handler 自行实现
     * @param int $uid 用户ID
     * @param int $cartNum 购买数量
     * @param string $unique SKU唯一码
     * @param int $type 旧版数字交易类型：0=普通、1=秒杀、2=砍价、3=拼团、6=预售
     * @param int $productId 中台商品ID
     * @param int $seckillId 秒杀活动ID
     * @param int $bargainId 砍价活动ID
     * @param int $combinationId 拼团活动ID
     * @param int $advanceId 预售活动ID
     * @return array 返回 [attrInfo, unique, bargainPriceMin, cartNum, productInfo]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkProductStock(int $uid, int $cartNum, string $unique, int $type = 0, $productId = 0, int $seckillId = 0, int $bargainId = 0, int $combinationId = 0, int $advanceId = 0)
    {
        // 通过注册中心按旧版数字 type 解析交易模式处理器，避免中台直接引用扩展层服务
        /** @var TradeModeRegistry $registry */
        $registry = app()->make(TradeModeRegistry::class);
        $handler = $registry->getByLegacyType($type);
        // 同一请求只会携带一种活动的ID，其余参数为0，直接取非0值作为活动ID
        $activityId = $seckillId ?: ($bargainId ?: ($combinationId ?: ($advanceId ?: 0)));
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        // 活动类交易模式（秒杀/砍价/拼团等）：交由各交易模式 Handler 校验活动状态、限购与库存
        $checkResult = $handler->checkCartStock($uid, $cartNum, $unique, (int)$activityId, (int)$productId);
        if (!empty($checkResult)) {
            [$attrInfo, $unique, $productInfo, $activityInfo] = $checkResult;
            if (is_object($productInfo)) {
                $productInfo = $productInfo->toArray();
            }
            $productInfo = is_array($productInfo) ? $productInfo : [];
            // 活动商品 productInfo 来自活动表（如 eb_store_seckill），缺失 delivery_methods 配送方式字段，
            // 从中台商品表补齐，供购物车合单 confirm 聚合配送能力（express_shipping/store_pickup）
            if (empty($productInfo['delivery_methods']) && $productId) {
                try {
                    $centerProduct = $productServices->isValidProduct((int)$productId, 'delivery_methods');
                    if ($centerProduct) {
                        $deliveryMethods = $centerProduct['delivery_methods'] ?? [];
                        if (is_string($deliveryMethods)) {
                            $deliveryMethods = json_decode($deliveryMethods, true) ?: [];
                        }
                        $productInfo['delivery_methods'] = is_array($deliveryMethods) ? $deliveryMethods : ['express'];
                    }
                } catch (\Throwable $e) {
                    // 中台商品读取失败时兜底默认快递，不影响主流程
                    $productInfo['delivery_methods'] = ['express'];
                }
            }
            return [$attrInfo, $unique, $activityInfo['bargain_price_min'] ?? 0, $cartNum, $productInfo];
        }
        $productInfo = $productServices->isValidProduct($productId);
        if (!$productInfo) {
            throw new ApiException('该商品已下架或删除');
        }
        // 通过 ProductTypeHandler 校验 SKU
        $skuInfo = $this->getProductSkuInfo((int)$productId, $unique);
        if (empty($skuInfo)) {
            throw new ApiException('请选择有效的商品属性');
        }
        $unique = $skuInfo['unique'];
        $nowStock = (int)($skuInfo['stock'] ?? 0);//现有库存
        // 普通商品 attrInfo 从 SKU 信息构建
        $attrInfo = [
            'suk'   => $skuInfo['suk'] ?? '默认',
            'stock'  => $nowStock,
            'price'  => $skuInfo['price'] ?? 0,
            'ot_price' => $skuInfo['ot_price'] ?? 0,
            'cost'   => $skuInfo['cost'] ?? 0,
            'vip_price' => $skuInfo['vip_price'] ?? 0,
            'brokerage' => $skuInfo['brokerage'] ?? 0,
            'brokerage_two' => $skuInfo['brokerage_two'] ?? 0,
            'bar_code' => $skuInfo['bar_code'] ?? '',
            'image' => $skuInfo['image'] ?? '',
        ];
        if ($cartNum > $nowStock) {
            throw new ApiException('该商品库存不足{:num}', ['num' => $cartNum]);
        }
        // 优惠券商品有效性校验：由优惠券产品类型 Handler 自行处理，此处不再依赖旧规格表
        $stockNum = $this->dao->value(['product_id' => $productId, 'product_attr_unique' => $unique, 'uid' => $uid, 'status' => 1], 'cart_num') ?: 0;
        if ($nowStock < ($cartNum + $stockNum)) {
            $surplusStock = $nowStock - $cartNum;//剩余库存
            if ($surplusStock < $stockNum) {
                $this->dao->update(['product_id' => $productId, 'product_attr_unique' => $unique, 'uid' => $uid, 'status' => 1], ['cart_num' => $surplusStock]);
            }
        }
        return [$attrInfo, $unique, 0, $cartNum, $productInfo];
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
    public function setCart(int $uid, int $product_id, int $cart_num = 1, string $product_attr_unique = '', int $type = 0, bool $new = true, int $combination_id = 0, int $seckill_id = 0, int $bargain_id = 0, int $advance_id = 0)
    {
        if ($cart_num < 1) $cart_num = 1;
        // 自定义表单商品需先填写表单再下单，购物车无法承载表单数据，禁止加入购物车（立即购买 new=true 不受限）
        if (!$new && $this->hasCustomForm($product_id)) {
            throw new ApiException('该商品需填写自定义表单，请直接购买');
        }
        // direct 加购模式的产品类型（如网盘商品）只能直接购买，禁止加入购物车（立即购买 new=true 不受限）
        if (!$new && $this->isDirectCartMode($product_id)) {
            throw new ApiException('该商品不支持加入购物车，请直接购买');
        }
        if ($type == 0) {
            //检查限购
            $this->checkLimit($uid, $product_id, $cart_num, $new);
        }
        //检测库存限量
        [$attrInfo, $product_attr_unique, $bargainPriceMin, $cart_num, $productInfo] = $this->checkProductStock($uid, $cart_num, $product_attr_unique, $type, $product_id, $seckill_id, $bargain_id, $combination_id, $advance_id);
        if ($new) {
            // 购物车缓存 key 走中台雪花单号生成（同命名空间无需 use）
            $key = app()->make(OrderCenterServices::class)->getNewOrderId((string)$uid);
            $info['id'] = $key;
            $info['type'] = $type;
            $info['seckill_id'] = $seckill_id;
            $info['bargain_id'] = $bargain_id;
            $info['combination_id'] = $combination_id;
            $info['advance_id'] = $advance_id;
            $info['product_id'] = $product_id;
            $info['product_attr_unique'] = $product_attr_unique;
            $info['cart_num'] = $cart_num;
            $info['productInfo'] = $productInfo ? (is_object($productInfo) ? $productInfo->toArray() : $productInfo) : [];
            $info['productInfo']['attrInfo'] = is_object($attrInfo) ? $attrInfo->toArray() : (array)$attrInfo;
            $info['attrInfo'] = is_object($attrInfo) ? $attrInfo->toArray() : (array)$attrInfo;
            $info['sum_price'] = $info['productInfo']['attrInfo']['price'];
            //砍价
            if ($bargain_id) {
                $info['truePrice'] = $bargainPriceMin;
                $info['productInfo']['attrInfo']['price'] = $bargainPriceMin;
            } else {
                $info['truePrice'] = $info['productInfo']['attrInfo']['price'] ?? $info['productInfo']['price'] ?? 0;
            }
            //拼团砍价秒杀不参与会员价
            if ($bargain_id || $combination_id || $seckill_id || $advance_id) {
                $info['truePrice'] = $info['productInfo']['attrInfo']['price'] ?? 0;
                $info['vip_truePrice'] = 0;
            }
            $info['trueStock'] = $info['productInfo']['attrInfo']['stock'];
            $info['costPrice'] = $info['productInfo']['attrInfo']['cost'];

            $productType = $info['productInfo']['product_type'] ?? 'normal';
            try {
                $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
                $handler = $registry->get($productType);
                $extend = $handler->getDetailExtend($product_id);
                if (!empty($extend['custom_form'])) {
                    $info['productInfo']['custom_form'] = json_encode($extend['custom_form']);
                }
            } catch (\Throwable $e) {
            }

            try {
                CacheService::set($key, $info, 3600);
            } catch (\Throwable $e) {
                throw new ApiException($e->getMessage());
            }
            return $key;
        } else {//加入购物车记录
            ProductLogJob::dispatch(['cart', ['uid' => $uid, 'product_id' => $product_id, 'cart_num' => $cart_num]]);
            $cart = $this->dao->getOne(['type' => $type, 'uid' => $uid, 'product_id' => $product_id, 'product_attr_unique' => $product_attr_unique, 'is_del' => 0, 'is_new' => 0, 'is_pay' => 0, 'status' => 1]);

            // 购物车事件-加入购物车
            event('UserCart', ['add', [
                'uid' => $uid,
                'product_id' => $product_id,
                'sku_id' => $product_attr_unique,
                'cart_num' => $cart_num,
                'add_time' => time(),
            ]]);

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

    /**
     * 判断商品是否配置了自定义表单
     * 自定义表单需要在下单环节填写，购物车无法携带表单数据
     * @param int $productId 商品ID
     * @return bool
     */
    protected function hasCustomForm(int $productId): bool
    {
        try {
            $productType = app()->make(CenterProductServices::class)->getProductType($productId);
            $handler = app()->make(ProductTypeRegistry::class)->get($productType);
            $extend = $handler->getDetailExtend($productId);
            return !empty($extend['custom_form']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 判断商品所属产品类型是否为 direct 加购模式
     * direct 模式（网盘等）只能直接下单走单品直购链路，不支持加入购物车
     * @param int $productId 商品ID
     * @return bool
     */
    protected function isDirectCartMode(int $productId): bool
    {
        try {
            $productType = app()->make(CenterProductServices::class)->getProductType($productId);
            $handler = app()->make(ProductTypeRegistry::class)->get($productType);
            return ($handler->getCartStrategy()['cart_mode'] ?? 'cart') === 'direct';
        } catch (\Throwable $e) {
            return false;
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

        //购物车修改数量检查限购
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        $limitInfo = $productServices->get($carInfo->product_id, ['is_limit', 'limit_type', 'limit_num', 'min_qty']);
        if ($number < $limitInfo['min_qty']) {
            throw new ApiException('不能小于起购数量');
        }
        if ($limitInfo['is_limit']) {
            $num = $this->dao->sum([['uid', '=', $uid], ['product_id', '=', $carInfo->product_id], ['id', '<>', $id]], 'cart_num') + $number;
            if ($limitInfo['limit_type'] == 1 && $num > $limitInfo['limit_num']) {
                throw new ApiException('单次购买数量不能大于 {:limit} 件', ['limit' => $limitInfo['limit_num']]);
            } else if ($limitInfo['limit_type'] == 2) {
                // 累计限购：已购数量统计需基于订单中台 extra_info 聚合，见 getUserPaidProductNum
                $orderPayNum = $this->getUserPaidProductNum($uid, (int)$carInfo->product_id);
                $orderRefundNum = 0;
                $orderNum = $orderPayNum - $orderRefundNum;
                if (($num + $orderNum) > $limitInfo['limit_num']) {
                    throw new ApiException('该商品限购 {:limit} 件，您已经购买 {:pay_num} 件', ['limit' => $limitInfo['limit_num'], 'pay_num' => $orderNum]);
                }
            }
        }

        $stock = $productServices->getProductStock($carInfo->product_id, $carInfo->product_attr_unique);
        if (!$stock) throw new ApiException('暂无库存');
        if ($stock < $number) throw new ApiException('该商品库存不足{:num}', ['num' => $number]);
        if ($carInfo->cart_num == $number) return true;
        return $this->dao->changeUserCartNum(['uid' => $uid, 'id' => $id], (int)$number);
    }

    /**
     * 修改购物车状态
     * @param int $productId
     * @param int $status 0 商品下架
     */
    public function changeStatus(int $productId, $status = 0)
    {
        $this->dao->update($productId, ['status' => $status], 'product_id');
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
        [$list, $valid, $invalid] = $this->handleCartList($uid, $list);
        $seckillIds = array_unique(array_column($list, 'seckill_id'));
        $bargainIds = array_unique(array_column($list, 'bargain_id'));
        $combinationId = array_unique(array_column($list, 'combination_id'));
        $discountId = array_unique(array_column($list, 'discount_id'));
        $deduction = ['seckill_id' => $seckillIds[0] ?? 0, 'bargain_id' => $bargainIds[0] ?? 0, 'combination_id' => $combinationId[0] ?? 0, 'discount_id' => $discountId[0] ?? 0];
        if ($status == 1) {
            return ['valid' => $list, 'invalid' => [], 'deduction' => $deduction];
        } else {
            return ['valid' => [], 'invalid' => $list, 'deduction' => $deduction];
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
        $skuInfo = $this->getProductSkuInfo($product_id, $unique);
        if (empty($skuInfo)) {
            throw new ApiException('选择的规格不存在');
        }
        $stock = (int)($skuInfo['stock'] ?? 0);
        if ($stock > 0) {
            $this->dao->update($cart_id, ['product_attr_unique' => $skuInfo['unique'], 'cart_num' => 1]);
        } else {
            throw new ApiException('选择的规格库存不足');
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
        if ($type == 1) {
            //检查限购
            $this->checkLimit($uid, $productId, $num, 0);
        }

        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);

        if (!$productServices->isValidProduct((int)$productId, 'id')) {
            throw new ApiException('该商品已下架或删除');
        }

        // 通过 ProductTypeHandler 获取默认 SKU 并校验有效性
        $skuInfo = $this->getProductSkuInfo((int)$productId, $unique);
        if (empty($skuInfo)) {
            throw new ApiException('请选择有效的商品属性');
        }
        $unique = $skuInfo['unique'];

        if ($productServices->getProductStock((int)$productId, $unique) < $num) {
            throw new ApiException('该商品库存不足{:num}', ['num' => $num]);
        }

        $cart = $this->dao->getOne(['uid' => $uid, 'product_id' => $productId, 'product_attr_unique' => $unique]);
        $min_qty = $productServices->value(['id' => $productId], 'min_qty');
        if ($cart) {
            if ($type == -1) {
                $cart->cart_num = $num;
            } elseif ($type == 0) {
                $cart->cart_num = $cart->cart_num - $num;
                if ($cart->cart_num < $min_qty) {
                    return $this->dao->delete($cart->id);
                }
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
                'cart_num' => $num > $min_qty ? $num : $min_qty,
                'product_attr_unique' => $unique,
                'type' => 0,
                'add_time' => time()
            ];
            return $this->dao->save($data)->id;
        }
    }

    /**
     * 通过 ProductTypeHandler 获取商品 SKU 信息
     * @param int $productId 商品中台ID
     * @param string $unique 规格唯一码，空则取默认 SKU
     * @return array|null
     */
    protected function getProductSkuInfo(int $productId, string $unique): ?array
    {
        try {
            /** @var CenterProductServices $productServices */
            $productServices = app()->make(CenterProductServices::class);
            $product = $productServices->isValidProduct($productId, 'product_type');
            $typeKey = $product ? ($product['product_type'] ?? 'normal') : 'normal';

            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($typeKey);
            $skuInfo = $handler->getSkuInfo($productId, $unique);
            if (!empty($skuInfo)) {
                return $skuInfo;
            }
        } catch (\Throwable $e) {
            // 异常时返回 null，由调用方处理
        }
        return null;
    }

    /**
     * 获取用户购物车数量  ids 统计金额
     * @param int $uid
     * @param string $numType
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserCartCount(int $uid, string $numType)
    {
        $count = 0;
        $ids = [];
        $sum_price = 0;
        $cartList = $this->dao->getUserCartList($uid, '*', ['productInfo', 'attrInfo']);
        if ($cartList) {
            /** @var CenterProductServices $productServices */
            $productServices = app()->make(CenterProductServices::class);
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $vipStatus = $memberCardService->isOpenMemberCard('vip_price', false);
            /** @var UserServices $user */
            $user = app()->make(UserServices::class);
            $userInfo = $user->getUserInfo($uid);
            $discount = 100;
            if (sys_config('member_func_status', 1)) {
                /** @var SystemUserLevelServices $systemLevel */
                $systemLevel = app()->make(SystemUserLevelServices::class);
                $discount = $systemLevel->value(['id' => $userInfo['level'], 'is_del' => 0, 'is_show' => 1], 'discount') ?: 100;
            }
            foreach ($cartList as &$item) {
                $productInfo = $item['productInfo'];
                if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                    $policy = app()->make(ProductTypeRegistry::class)->get((string)($productInfo['product_type'] ?? 'normal'))->getPricingPolicy();
                    [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, ($policy['member_discount'] ?? true) ? $discount : 100, ($policy['vip_discount'] ?? true) ? $productInfo['attrInfo']['vip_price'] ?? 0 : 0, ($policy['vip_discount'] ?? true) ? $productInfo['is_vip'] ?? 0 : 0, true);
                    $item['truePrice'] = $truePrice;
                    $item['price_type'] = $type;
                } else {
                    [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, $discount, $item['productInfo']['vip_price'] ?? 0, $item['productInfo']['is_vip'] ?? 0, true);
                    $item['truePrice'] = $truePrice;
                    $item['price_type'] = $type;
                }
                $sum_price = bcadd((string)$sum_price, (string)bcmul((string)$item['cart_num'], (string)$item['truePrice'], 4), 2);
            }
            $ids = array_column($cartList, 'id');
            if ($numType) {
                $count = count($cartList);
            } else {
                $count = array_sum(array_column($cartList, 'cart_num'));
            }
        }
        return compact('count', 'ids', 'sum_price');
    }

    /**
     * 处理购物车数据
     * @param int $uid
     * @param array $cartList
     * @param array $addr
     * @param int $shipping_type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/16
     */
    public function handleCartList(int $uid, array $cartList, array $addr = [], int $shipping_type = 1)
    {
        if (!$cartList) return [$cartList, [], []];
        $tempIds = [];
        $userInfo = [];
        $discount = 100;
        if ($uid) {
            /** @var UserServices $user */
            $user = app()->make(UserServices::class);
            $userInfo = $user->getUserInfo($uid);
            //用户等级是否开启
            if (sys_config('member_func_status', 1)) {
                /** @var SystemUserLevelServices $systemLevel */
                $systemLevel = app()->make(SystemUserLevelServices::class);
                $discount = $systemLevel->value(['id' => $userInfo['level'], 'is_del' => 0, 'is_show' => 1], 'discount') ?: 100;
            }
        }

        //付费会员是否开启，用户是否是付费会员，两个都满足，订单计算金额才会按照付费会员计算。
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $vipStatus = $memberCardService->isOpenMemberCard('vip_price', false) && $userInfo['is_money_level'] > 0;

        //不送达运费模板
        if ($shipping_type == 1 && $addr) {
            $cityId = (int)($addr['city_id'] ?? 0);
            if ($cityId) {
                foreach ($cartList as $item) {
                    // 中台商品无 temp_id（运费走 freight_config），仅收集存在模板ID的商品
                    $tempId = $item['productInfo']['temp_id'] ?? 0;
                    if ($tempId) {
                        $tempIds[] = $tempId;
                    }
                }
                $tempIds = array_unique($tempIds);
                // 直接调用 app/ 层运费模板不送达检查
                /** @var \app\services\shipping\ShippingTemplatesNoDeliveryServices $noDeliveryServices */
                $noDeliveryServices = app()->make(\app\services\shipping\ShippingTemplatesNoDeliveryServices::class);
                // 获取开启了不送达的模板
                /** @var \app\services\shipping\ShippingTemplatesServices $shippingServices */
                $shippingServices = app()->make(\app\services\shipping\ShippingTemplatesServices::class);
                $noDeliveryTempIds = $shippingServices->getColumn([['id', 'in', $tempIds], ['no_delivery', '=', 1]], 'id');
                $tempIds = !empty($noDeliveryTempIds) ? $noDeliveryServices->isNoDelivery(array_unique($noDeliveryTempIds), $cityId) : [];
            }
        }

        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        $valid = $invalid = [];
        foreach ($cartList as &$item) {
            if ($item['type'] == 0) $item['min_qty'] = $item['productInfo']['min_qty'];
            // 中台商品主表使用 name 字段存储商品名称，兼容前端期望的 store_name
            if (empty($item['productInfo']['store_name'] ?? '')) {
                $item['productInfo']['store_name'] = $item['productInfo']['name'] ?? '';
            }
            $item['productInfo']['express_delivery'] = false;
            $item['productInfo']['store_mention'] = false;
            if (isset($item['productInfo']['logistics'])) {
                if (in_array(1, explode(',', $item['productInfo']['logistics']))) {
                    $item['productInfo']['express_delivery'] = true;
                }
                if (in_array(2, explode(',', $item['productInfo']['logistics']))) {
                    $item['productInfo']['store_mention'] = true;
                }
            }
            // 从 delivery_methods 聚合配送能力（与单品直购 confirm 语义一致）
            if (empty($item['productInfo']['delivery_methods'])) {
                $dm = $item['productInfo']['delivery_methods'] ?? [];
                if (is_string($dm)) {
                    $dm = json_decode($dm, true) ?: [];
                }
                if (!is_array($dm)) {
                    // 兼容旧版 logistics 字段的历史数据回填
                    $dm = [];
                    if (!empty($item['productInfo']['express_delivery'])) $dm[] = 'express';
                    if (!empty($item['productInfo']['store_mention'])) $dm[] = 'pickup';
                }
                $item['productInfo']['delivery_methods'] = array_values(array_unique(array_filter($dm)));
            }
            $item['productInfo']['express_shipping'] = in_array('express', (array)($item['productInfo']['delivery_methods'] ?? []), true);
            $item['productInfo']['store_pickup']     = in_array('pickup',  (array)($item['productInfo']['delivery_methods'] ?? []), true);
            $item['productInfo']['door_service']    = in_array('door_service', (array)($item['productInfo']['delivery_methods'] ?? []), true);
            // 从 freight_config.door_service 节点读取上门服务费用，供确认页聚合
            // 新格式：door_service.type=free 时强制 0，type=fixed 时读 postage
            if (!isset($item['productInfo']['door_service_postage'])) {
                $fc = $item['productInfo']['freight_config'] ?? null;
                if (is_string($fc) && $fc !== '') {
                    $decodedFc = json_decode($fc, true);
                    $fc = is_array($decodedFc) ? $decodedFc : [];
                }
                $doorPostage = 0.00;
                if (is_array($fc) && isset($fc['door_service'])) {
                    $doorConfig = $fc['door_service'];
                    // type=free 或缺失时按 0 处理，type=fixed 时读 postage
                    if (($doorConfig['type'] ?? 'free') === 'fixed' && isset($doorConfig['postage'])) {
                        $doorPostage = (float)$doorConfig['postage'];
                    }
                }
                $item['productInfo']['door_service_postage'] = $doorPostage;
            }
            // 优先从产品类型 Handler 读取真实 SKU 信息（attrInfo 关联临时指向中台商品表，无 suk/product_id 等 SKU 字段）
            $skuAttr = [];
            try {
                $skuInfo = app()->make(ProductTypeRegistry::class)
                    ->get((string)($item['productInfo']['product_type'] ?? 'normal'))
                    ->getSkuInfo((int)($item['product_id'] ?? 0), (string)($item['product_attr_unique'] ?? ''));
                if ($skuInfo) {
                    $skuAttr = $skuInfo;
                    $skuAttr['product_id'] = (int)($item['product_id'] ?? 0);
                }
            } catch (\Throwable $e) {
                // SKU 读取失败时降级使用关联数据
            }
            if ($skuAttr) {
                $item['productInfo']['attrInfo'] = $skuAttr;
            } elseif (isset($item['attrInfo']) && $item['attrInfo'] && (!isset($item['productInfo']['attrInfo']) || !$item['productInfo']['attrInfo'])) {
                $item['productInfo']['attrInfo'] = $item['attrInfo'] ?? [];
            }
            $item['attrStatus'] = isset($item['productInfo']['attrInfo']['stock']) && $item['productInfo']['attrInfo']['stock'];
            $item['productInfo']['attrInfo']['image'] = $item['productInfo']['attrInfo']['image'] ?? $item['productInfo']['image'] ?? '';
            $item['productInfo']['attrInfo']['suk'] = $item['productInfo']['attrInfo']['suk'] ?? '默认';
            if (isset($item['productInfo']['attrInfo'])) {
                $item['productInfo']['attrInfo'] = get_thumb_water($item['productInfo']['attrInfo']);
            }
            $item['productInfo'] = get_thumb_water($item['productInfo']);
            $productInfo = $item['productInfo'];
            // 普通实物商品库存不足时归入无效商品，避免确认页仍展示并在创建订单时才失败。
            $productTypeKey = (string)($productInfo['product_type'] ?? 'normal');
            $typeHandler = $registry->get($productTypeKey);
            $capabilities = method_exists($typeHandler, 'getCapabilities')
                ? $typeHandler->getCapabilities() : [];
            $checkStock = ($capabilities['inventory']['check_stock'] ?? true) === true;
            $availableStock = (int)($productInfo['attrInfo']['stock'] ?? $productInfo['stock'] ?? 0);
            $cartNum = (int)($item['cart_num'] ?? 0);
            if ($checkStock && ($cartNum <= 0 || $availableStock < $cartNum)) {
                $item['is_valid'] = 0;
                $item['invalid_reason'] = '库存不足';
                $invalid[] = $item;
                unset($item['attrInfo']);
                continue;
            }
            $item['vip_truePrice'] = 0;
            $is_activity = $item['seckill_id'] || $item['bargain_id'] || $item['combination_id'] || $item['advance_id'];
            if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                $item['truePrice'] = $productInfo['attrInfo']['price'] ?? 0;
                $item['sum_price'] = $productInfo['attrInfo']['price'] ?? 0;
                if (!$is_activity) {
                    $policy = app()->make(ProductTypeRegistry::class)->get((string)($productInfo['product_type'] ?? 'normal'))->getPricingPolicy();
                    [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, ($policy['member_discount'] ?? true) ? $discount : 100, ($policy['vip_discount'] ?? true) ? $productInfo['attrInfo']['vip_price'] ?? 0 : 0, ($policy['vip_discount'] ?? true) ? $productInfo['is_vip'] ?? 0 : 0, true);
                    $item['truePrice'] = $truePrice;
                    $item['vip_truePrice'] = $vip_truePrice;
                    $item['price_type'] = $type;
                } else {
                    $item['price_type'] = 'activity';
                }
            } else {
                $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                $item['truePrice'] = $item['productInfo']['price'] ?? 0;
                $item['sum_price'] = $item['productInfo']['price'] ?? 0;
                if (!$is_activity) {
                    [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, $discount, $item['productInfo']['vip_price'] ?? 0, $item['productInfo']['is_vip'] ?? 0, true);
                    $item['truePrice'] = $truePrice;
                    $item['vip_truePrice'] = $vip_truePrice;
                    $item['price_type'] = $type;
                } else {
                    $item['price_type'] = 'activity';
                }
            }
            if (isset($item['status']) && $item['status'] == 0) {
                $item['is_valid'] = 0;
                $invalid[] = $item;
            } else {
                switch ($shipping_type) {
                    case 1:
                        //不送达（中台商品无 temp_id 时跳过不送达校验）
                        if (in_array($item['productInfo']['temp_id'] ?? 0, $tempIds) || (isset($item['productInfo']['logistics']) && $item['productInfo']['logistics'] !== '' && $item['productInfo']['logistics'] !== '0' && !in_array(1, explode(',', $item['productInfo']['logistics'])))) {
                            $item['is_valid'] = 0;
                            $invalid[] = $item;
                        } else {
                            $item['is_valid'] = 1;
                            $valid[] = $item;
                        }
                        break;
                    case 2:
                        //不支持到店自提
                        if (isset($item['productInfo']['logistics']) && $item['productInfo']['logistics'] && $item['productInfo']['logistics'] !== '0' && !in_array(2, explode(',', $item['productInfo']['logistics']))) {
                            $item['is_valid'] = 0;
                            $invalid[] = $item;
                        } else {
                            $item['is_valid'] = 1;
                            $valid[] = $item;
                        }
                        break;
                    default:
                        $item['is_valid'] = 1;
                        $valid[] = $item;
                        break;
                }
            }
            unset($item['attrInfo']);
        }
        return [$cartList, $valid, $invalid];
    }

    /**
     * 检查限购
     * @param $uid
     * @param $product_id
     * @param $num
     * @param $new
     * @return bool
     * @throws \ReflectionException
     */
    public function checkLimit($uid, $product_id, $num, $new)
    {
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);

        $limitInfo = $productServices->get($product_id, ['is_limit', 'limit_type', 'limit_num']);
        if (!$limitInfo) throw new ApiException('商品不存在');
        $limitInfo = $limitInfo->toArray();
        if (!$limitInfo['is_limit']) return true;
        if ($limitInfo['limit_type'] == 1) {
            $cartNum = 0;
            if (!$new) {
                $cartNum = $this->dao->sum(['uid' => $uid, 'product_id' => $product_id], 'cart_num');
            }
            if (($num + $cartNum) > $limitInfo['limit_num']) {
                throw new ApiException('单次购买数量不能大于 {:limit} 件', ['limit' => $limitInfo['limit_num']]);
            }
        } else if ($limitInfo['limit_type'] == 2) {
            $cartNum = $this->dao->sum(['uid' => $uid, 'product_id' => $product_id], 'cart_num');
            // 累计限购：已购数量统计需基于订单中台 extra_info 聚合，见 getUserPaidProductNum
            $orderPayNum = $this->getUserPaidProductNum((int)$uid, (int)$product_id);
            $orderRefundNum = 0;
            $orderNum = $orderPayNum - $orderRefundNum;
            if (($num + $orderNum + $cartNum) > $limitInfo['limit_num']) {
                throw new ApiException('该商品限购 {:limit} 件，您已经购买 {:pay_num} 件', ['limit' => $limitInfo['limit_num'], 'pay_num' => $orderNum]);
            }
        }
        return true;
    }

    /**
     * 获取用户某商品的累计已购数量（已支付 - 已退款），用于累计限购校验
     *
     * TODO：旧实现从 eb_store_order_cart_info 表按 uid+product_id 聚合 cart_num 与 refund_num。
     * 该表已废弃（0 行），需改为：
     *   - 已支付数量：从 eb_order_center.extra_info.items 按 uid+product_id 聚合 cart_num
     *   - 已退款数量：从 eb_after_sale.product_snapshot JSON 按 order_center_id 关联 uid+product_id 聚合 refund_num
     *       （仅统计 status=refunded/completed 的售后单，避免把被拒/取消的售后计入，导致误限购）
     * 在补全上述逻辑前，累计限购（limit_type=2）暂不生效，返回 0 以保持与旧死表一致的行为。
     *
     * @param int $uid 用户ID
     * @param int $productId 商品ID
     * @return int 已购净数量（已支付 - 已退款）
     */
    protected function getUserPaidProductNum(int $uid, int $productId): int
    {
        // 暂返回 0，保持与旧死表（eb_store_order_cart_info 0 行）一致的行为，避免误限购
        return 0;
    }

    /**
     * 判断是否非付费会员购买会员专属商品
     * @param $user
     * @param $pid
     * @return bool
     * @author: 吴汐
     * @email 442384644@qq.com
     * @date: 2023/10/30
     */
    public function checkVipGoodsBuy($user, $pid)
    {
        $is_vip_product = app()->make(CenterProductServices::class)->value(['id' => $pid], 'vip_product');
        // 1=仅会员可见可买，2=所有人可见仅会员可买，非付费会员均无权购买
        if (in_array($is_vip_product, [1, 2]) && $user['is_money_level'] == 0) throw new ApiException('此商品为付费会员专属，您无权购买');
        return true;
    }
}
