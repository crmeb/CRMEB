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

namespace app\services\product;


use app\dao\product\CenterProductDao;
use app\Request;
use crmeb\trade\mode\TradeModeRegistry;
use app\services\activity\coupon\StoreCouponUserServices;
use crmeb\basic\BaseService;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\order\StoreCartServices;
use app\dao\order\OrderCenterDao;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductCateServices;
use app\services\product\product\StoreProductCouponServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\product\product\StoreProductLabelServices;
use app\services\product\product\StoreProductProtectionServices;
use app\services\product\product\CopyProductServices;
use app\services\product\product\StoreCategoryServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\UserLabelServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserLevelServices;
use app\services\user\UserSearchServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use crmeb\trade\product\ProductTypeRegistry;
use app\jobs\ProductCopyJob;
use crmeb\exceptions\ApiException;
use crmeb\utils\File;
use app\services\system\config\SystemGroupDataServices;
use think\facade\Config;

/**
 * 中台商品统一服务
 * 由旧 CenterProductServices 迁移而来，面向 eb_product_center 中台表
 * @method getOne(array $where) 获取一条数据
 * @method update(int $id, array $data) 获取一条数据
 * @method delete($id, ?string $key = null) 删除数据
 * @method value($where, ?string $field = null) 获取字段
 * @method incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 加库存减销量
 * @method count(array $where) 获取指定条件下的数量
 * @method getColumn(array $where, string $field, string $key = '') 获取某个字段数组
 * @method getSearchList(array $where, int $page = 0, int $limit = 0, ?array $field = ['*']) 获取列表
 * @method get(int $id, array $field) 获取一条数据
 * @method getCid(int $page, int $limit) 获取一级分类ID
 * @method downAdvance() 预售商品自动到期下架
 */
class CenterProductServices extends BaseService
{

    public function __construct(CenterProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取顶部标签
     * @param array $where
     * @return array[]
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/31
     */
    public function getHeader($where = [])
    {
        $where['cate_id'] = !empty($where['cate_id']) ? app()->make(StoreCategoryServices::class)->getColumn(['pid' => $where['cate_id']], 'id') : [];
        //出售中的商品
        $onsale = $this->dao->getCount($where + ['type' => 1]);
        //仓库中的商品
        $forsale = $this->dao->getCount($where + ['type' => 2]);
        //已经售罄商品
        $outofstock = $this->dao->getCount($where + ['type' => 4]);
        //警戒库存商品
        $policeforce = $this->dao->getCount($where + ['type' => 5, 'store_stock' => sys_config('store_stock') > 0 ? sys_config('store_stock') : 2]);
        //回收站的商品
        $recycle = $this->dao->getCount($where + ['type' => 6]);
        return [
            ['type' => 1, 'name' => '出售中的商品', 'count' => $onsale],
            ['type' => 2, 'name' => '仓库中的商品', 'count' => $forsale],
            ['type' => 4, 'name' => '已经售罄商品', 'count' => $outofstock],
            ['type' => 5, 'name' => '警戒库存商品', 'count' => $policeforce],
            ['type' => 6, 'name' => '回收站的商品', 'count' => $recycle]
        ];
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/17
     */
    public function getList(array $where, int $page = 0, int $limit = 0)
    {
        // 中台统一列表契约：keyword 等价 store_name；is_show/is_del 为页面显式状态筛选，
        // 与通用 type tab 口径互斥（type 会强制 is_show=1/is_del=0）；'' 表示全部不过滤，
        // 空值必须移除，避免搜索器对空值做松散比较（如 is_show='' 在 MySQL 中等值 0）
        if (($where['store_name'] ?? '') === '' && ($where['keyword'] ?? '') !== '') {
            $where['store_name'] = $where['keyword'];
        }
        unset($where['keyword']);
        $hasStatusFilter = false;
        foreach (['is_show', 'is_del'] as $statusKey) {
            if (($where[$statusKey] ?? '') === '') {
                unset($where[$statusKey]);
            } else {
                $hasStatusFilter = true;
            }
        }
        if ($hasStatusFilter || (string)($where['type'] ?? '') === '') {
            unset($where['type']);
        }
        $where['store_stock'] = sys_config('store_stock') > 0 ? sys_config('store_stock') : 2;
        if ($page <= 0 || $limit <= 0) {
            [$page, $limit] = $this->getPageValue();
        }
        $cateIds = [];
        if (isset($where['cate_id']) && $where['cate_id']) {
            /** @var StoreCategoryServices $storeCategory */
            $storeCategory = app()->make(StoreCategoryServices::class);
            $cateIds = $storeCategory->getColumn(['pid' => $where['cate_id']], 'id');
        }
        if ($cateIds) {
            $cateIds[] = $where['cate_id'];
            $where['cate_id'] = $cateIds;
        }
        $order_string = '';
        $order_arr = ['asc', 'desc'];
        if (isset($where['sales']) && in_array($where['sales'], $order_arr)) {
            $order_string = 'sales ' . $where['sales'];
        }
        unset($where['sales']);
        $list = $this->dao->getList($where, $page, $limit, $order_string);
        $cateIds = implode(',', array_column($list, 'cate_id'));
        /** @var StoreCategoryServices $categoryService */
        $categoryService = app()->make(StoreCategoryServices::class);
        $cateList = $categoryService->getCateParentAndChildName($cateIds);
        $oneCateList = $categoryService->getColumn(['pid' => 0], 'id');
        $parentCateList = $categoryService->getColumn([
            ['id', 'in', $cateIds]
        ], 'cate_name', 'id');
        $activityExist = $this->getActivityExist(array_column($list, 'id'));
        foreach ($list as &$item) {
            if ($item['spec_type'] == 0) {
                // 单规格商品直接从中台表字段构建默认 SKU，不再查询旧规格表
                $item['attr_value'] = [
                    'unique'     => '',
                    'cost'       => $item['cost'] ?? 0,
                    'price'      => $item['price'] ?? 0,
                    'ot_price'   => $item['original_price'] ?? 0,
                    'stock'      => $item['stock'] ?? 0,
                    'product_id' => $item['id'],
                ];
            }
            $cateName = array_filter($cateList, function ($val) use ($item) {
                if (in_array($val['id'], explode(',', $item['cate_id']))) {
                    return $val;
                }
            });
            $item['cate_name'] = [];
            foreach ($cateName as $k => $v) {
                $item['cate_name'][] = $v['one'] . '/' . $v['two'];
            }
            foreach (explode(',', $item['cate_id']) as $cate_value) {
                if (in_array($cate_value, $oneCateList)) $item['cate_name'][] = $parentCateList[$cate_value];
            }
            $item['cate_name'] = is_array($item['cate_name']) ? implode(',', $item['cate_name']) : '';
            $item['stock_attr'] = $item['stock'] > 0; //库存
            $item['product_type'] = $item['product_type'] ?? 'normal';
            $item['type_name'] = $this->getProductTypeName($item['product_type']);
            $item['image'] = set_file_url($item['image']);
            $item['slider_image'] = set_file_url($item['slider_image']);
            $item['activityExist'] = $activityExist[$item['id']];
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    public function getActivityExist($productIds)
    {
        if (!count($productIds)) return [];

        // 通过活动注册中心检查商品参与活动情况，仅检查营销类交易模式（排除普通购买等基础模式），
        // 活动名称由各交易模式 Handler 的 getLabel() 动态提供，新增活动类型无需改动此处
        $activityRegistry = app()->make(TradeModeRegistry::class);
        $marketingKeys = array_flip($activityRegistry->getMarketingKeys());
        $labels = array_column($activityRegistry->allInfo(), 'label', 'key');
        $exists = $activityRegistry->checkProductActivities($productIds);
        $result = [];
        foreach ($exists as $productId => $modeMap) {
            $list = [];
            foreach ($modeMap as $modeKey => $isExist) {
                // 仅保留参与中的营销类交易模式，输出结构化列表供前端动态渲染
                if ($isExist && isset($marketingKeys[$modeKey])) {
                    $list[] = ['type' => $modeKey, 'name' => $labels[$modeKey] ?? $modeKey];
                }
            }
            $result[$productId] = $list;
        }
        return $result;
    }

    /**
     * 设置商品上下架
     * @param $ids
     * @param $is_show
     */
    public function setShow(array $ids, int $is_show)
    {
        if (empty($ids)) throw new AdminException('参数错误');
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        foreach ($ids as $id) {
            // 获取商品当前状态
            $product = $this->dao->get($id);
            if ($product) {
                $oldStatus = $product['is_show'];
                
                $cartService->changeStatus($id, $is_show);
                
                // 商品状态变更事件（只有状态真正改变时触发）
                if ($oldStatus != $is_show) {
                    event('Product', ['status_change', [$id, $oldStatus, $is_show]]);
                }
            }
        }
        $res = $this->dao->batchUpdate($ids, ['is_show' => $is_show]);
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        $storeProductCateServices->batchUpdate($ids, ['status' => $is_show], 'product_id');
        return true;
    }

    /**
     * 获取规格模板
     * @return array
     */
    public function getRule()
    {
        /** @var ProductRuleServices $productRuleServices */
        $productRuleServices = app()->make(ProductRuleServices::class);
        $list = $productRuleServices->getList()['list'];
        foreach ($list as &$item) {
            $item['rule_value'] = json_decode($item['rule_value'], true);
            foreach ($item['rule_value'] as $ruleKey => $ruleItem) {
                foreach ($ruleItem['detail'] as $valueKey => $valueItem) {
                    if (is_string($valueItem)) {
                        $item['rule_value'][$ruleKey]['detail'][$valueKey] = ['value' => $valueItem, 'pic' => ''];
                        $item['rule_value'][$ruleKey]['add_pic'] = 0;
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取商品规格数据（用户端加购/下单链路）
     * 替代已弃用的 StoreProductAttrServices::getProductAttrDetail，从产品类型 Handler 的新 SKU 表读取
     * @param int $id 商品ID
     * @param int $uid 用户ID
     * @param int $type 活动类型（0=普通商品）
     * @return array [productAttr, productValue]
     */
    public function getProductAttrDetail(int $id, int $uid, int $type = 0)
    {
        $rules = $this->getProductRules($id);
        $items = $rules['items'] ?? [];
        $attrs = $rules['attrs'] ?? [];

        // 构造 productAttr（与旧格式兼容）
        $productAttr = [];
        foreach ($items as $item) {
            $attrName = $item['value'] ?? '';
            $detailValues = $item['detail'] ?? [];
            if (is_string($detailValues)) {
                $detailValues = json_decode($detailValues, true) ?: [];
            }
            $productAttr[] = [
                'attr_name'   => $attrName,
                'attr_values' => is_array($detailValues) ? $detailValues : [],
                'attr_value'  => array_map(function ($v) {
                    return ['attr' => $v, 'check' => false, 'pic' => ''];
                }, is_array($detailValues) ? $detailValues : []),
            ];
        }

        // 构造 productValue（与旧格式兼容）
        $values = [];
        foreach ($attrs as $attr) {
            $detail = $attr['detail'] ?? [];
            $sukKey = implode(',', array_values($detail));

            $values[$sukKey] = [
                'suk'            => $sukKey,
                'price'          => floatval($attr['price'] ?? 0),
                'ot_price'       => floatval($attr['ot_price'] ?? 0),
                'cost'           => floatval($attr['cost'] ?? 0),
                'stock'          => intval($attr['stock'] ?? 0),
                'quota'          => intval($attr['quota'] ?? $attr['stock'] ?? 0),
                'quota_show'     => intval($attr['quota_show'] ?? $attr['quota'] ?? $attr['stock'] ?? 0),
                'image'          => set_file_url($attr['pic'] ?? ''),
                'unique'         => $attr['unique'] ?? '',
                'bar_code'       => $attr['bar_code'] ?? '',
                'weight'         => floatval($attr['weight'] ?? 0),
                'volume'         => floatval($attr['volume'] ?? 0),
                'vip_price'      => floatval($attr['vip_price'] ?? 0),
                'brokerage'      => floatval($attr['brokerage'] ?? 0),
                'brokerage_two'  => floatval($attr['brokerage_two'] ?? 0),
                'cart_num'       => 0,
                'product_stock'  => intval($attr['stock'] ?? 0),
            ];
        }

        // 会员价未开启时清零
        $is_vip = $this->get($id, ['is_vip']);
        if (!$this->vipIsOpen(!!($is_vip['is_vip'] ?? 0))) {
            foreach ($values as &$value) {
                $value['vip_price'] = 0;
            }
            unset($value);
        }

        // 活动商品补充购物车数量
        if ($uid && $type) {
            /** @var StoreCartServices $storeCartService */
            $storeCartService = app()->make(StoreCartServices::class);
            $uniques = array_column($attrs, 'unique');
            $cartNumList = $storeCartService->getUserCartNums($uniques, $id, $uid);
            foreach ($values as &$val) {
                $val['cart_num'] = $cartNumList[$val['unique']] ?? 0;
                if (is_null($val['cart_num'])) $val['cart_num'] = 0;
            }
            unset($val);
        }

        return [$productAttr, $values];
    }

    /**
     * 获取商品详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        /** @var StoreCategoryServices $storeCatecoryService */
        $storeCatecoryService = app()->make(StoreCategoryServices::class);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        /** @var StoreCouponIssueServices $storeCouponIssueServices */
        $storeCouponIssueServices = app()->make(StoreCouponIssueServices::class);
        /** @var UserLabelServices $userLabelServices */
        $userLabelServices = app()->make(UserLabelServices::class);
        $data['tempList'] = $this->getTemp();
        $menus = [];
        foreach ($storeCatecoryService->getTierList(1) as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1]; //,'disabled'=>$menu['pid']== 0];
        }
        $data['cateList'] = $menus;
        $productInfo = $this->dao->getInfo($id);
        if ($productInfo) $productInfo = $productInfo->toArray();
        else throw new AdminException('商品不存在');

        // 补充中台表不存在的扩展字段（仅保留实物专属字段），通过产品类型 Handler 获取扩展信息
        $typeKey = $productInfo['product_type'] ?? 'normal';
        try {
            /** @var ProductTypeRegistry $registry */
            $registry = app()->make(ProductTypeRegistry::class);
            // has 前置判断避免未注册类型回退 normal 语义
            if ($registry->has($typeKey)) {
                $extendInfo = $registry->get($typeKey)->getAdminExtendInfo($id);
                if (!empty($extendInfo) && is_array($extendInfo)) {
                    // 仅从扩展表补充实物专属字段，通用字段已迁移到 eb_product_center
                    foreach (['weight', 'volume'] as $field) {
                        if (array_key_exists($field, $extendInfo) && !array_key_exists($field, $productInfo)) {
                            $productInfo[$field] = $extendInfo[$field];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // 扩展服务异常时忽略，继续使用中台基础数据
        }

        $couponIds = array_column($productInfo['coupons'], 'issue_coupon_id');
        // is_sub（分销开关）和 is_vip（会员价开关）是独立标量字段，前端对应两个独立表单控件
        $productInfo['is_sub'] = (int) ($productInfo['is_sub'] ?? 0);
        $productInfo['is_vip'] = (int) ($productInfo['is_vip'] ?? 0);
        $recommend = [];

        // 配送方式：从 delivery_methods 解析
        $deliveryMethods = $productInfo['delivery_methods'] ?? [];
        if (!is_array($deliveryMethods)) {
            $deliveryMethods = [];
        }
        $productInfo['express_shipping'] = in_array('express', $deliveryMethods);
        $productInfo['store_pickup'] = in_array('pickup', $deliveryMethods);
        // 是否支持上门服务（delivery_methods 含 door_service 时为 true）
        $productInfo['door_service'] = in_array('door_service', $deliveryMethods);
        // 上门费用：从 freight_config.door_service 节点读取（后台编辑回显）
        // 新格式：door_service.type=free 时强制 0，type=fixed 时读 postage
        $freightConfig = $productInfo['freight_config'] ?? [];
        if (!is_array($freightConfig)) {
            $freightConfig = [];
        }
        $doorConfig = $freightConfig['door_service'] ?? ['type' => 'free', 'postage' => 0];
        $productInfo['door_service_postage'] = ($doorConfig['type'] ?? 'free') === 'fixed'
            ? (float)($doorConfig['postage'] ?? 0)
            : 0.00;
        // 推荐商品列表：中台表存储逗号分隔的ID字符串，转换为带图片的数组
        $recommend_list = [];
        $recommendStr = $productInfo['recommend_list'] ?? '';
        if ($recommendStr !== '') {
            $recommendIds = array_filter(array_map('intval', explode(',', $recommendStr)));
            if (!empty($recommendIds)) {
                // 走 DAO 层按 ID 列表获取精简商品信息
                $list = $this->dao->getSimpleListByIds($recommendIds);
                // 保持推荐顺序与提交顺序一致
                $map = array_column($list, null, 'product_id');
                foreach ($recommendIds as $rid) {
                    if (isset($map[$rid])) {
                        $recommend_list[] = $map[$rid];
                    }
                }
            }
        }
        $productInfo['video_open'] = $productInfo['video_link'] !== '' ? 1 : 0;
        if (!empty($productInfo['video_link']) && (strpos($productInfo['video_link'], 'http') !== false)) {
            $productInfo['seletVideo'] = 1;
        } else {
            $productInfo['seletVideo'] = 0;
        }
        $productInfo['video_link'] = empty($productInfo['video_link']) ? '' : (strpos($productInfo['video_link'], 'http') === false ? sys_config('site_url') . $productInfo['video_link'] : $productInfo['video_link']);
        $productInfo['recommend_list'] = $recommend_list;
        $productInfo['coupons'] = $storeCouponIssueServices->productCouponList([['id', 'in', $couponIds]], 'title,id');
        $productInfo['cate_id'] = explode(',', $productInfo['cate_id']);
        // 中台表使用 label_list 字段存储标签ID，读取时映射为前端编辑所需的 label_id
        $labelList = $productInfo['label_list'] ?? '';
        $label_id = $labelList !== '' ? explode(',', $labelList) : [];
        $productInfo['label_id'] = $userLabelServices->getLabelList(['ids' => $label_id], ['id', 'label_name']);
        $productInfo['give_integral'] = floatval($productInfo['give_integral'] ?? 0);
        $pst = $productInfo['presale_start_time'] ?? 0;
        $productInfo['presale_time'] = $pst == 0 ? [] : [date('Y-m-d H:i:s', $pst), date('Y-m-d H:i:s', $productInfo['presale_end_time'] ?? 0)];
        $productInfo['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'activity_type' => 'normal']);
        $productInfo['custom_form'] = json_decode($productInfo['custom_form'] ?? '', true) ?? [];
        $productInfo['params_list'] = json_decode($productInfo['params_list'] ?? '', true) ?? [];
        $productInfo['label_list'] = $productInfo['label_list'] !== '' ? array_map('intval', explode(',', $productInfo['label_list'])) : [];
        $productInfo['protection_list'] = $productInfo['protection_list'] !== '' ? array_map('intval', explode(',', $productInfo['protection_list'])) : [];
        // 通过 ProductTypeHandler 获取规格数据，不再读取旧三表
        $typeKey = $productInfo['product_type'] ?? 'normal';
        try {
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($typeKey);
            $specs = $handler->getDetailSpecs($id);
            $productAttr = $specs['productAttr'] ?? [];
            $productValue = $specs['productValue'] ?? [];
            $skuInfo = $handler->getSkuInfo($id, '');
            if ($skuInfo === null) {
                $skuInfo = [];
            }
        } catch (\Throwable $e) {
            $productAttr = [];
            $productValue = [];
            $skuInfo = [];
        }

        if ($productInfo['spec_type'] == 1 && !empty($productAttr) && !empty($productValue)) {
            // 多规格：转换 Handler 规格结构为后台编辑格式
            $items = [];
            foreach ($productAttr as $attrItem) {
                $attrValues = $attrItem['attr_value'] ?? [];
                $detail = [];
                foreach ($attrValues as $v) {
                    $detail[] = [
                        'value' => is_array($v) ? ($v['attr'] ?? '') : (string)$v,
                        'pic'   => is_array($v) ? ($v['pic'] ?? '') : '',
                    ];
                }
                $items[] = [
                    'attr_name' => $attrItem['attr_name'] ?? '',
                    'detail'    => $detail,
                ];
            }

            $attrs = [];
            foreach ($productValue as $suk => $sku) {
                $attrArr = explode(',', $suk);
                $entry = [
                    'attr_arr' => $attrArr,
                    'detail'   => is_array($sku['detail'] ?? null) ? $sku['detail'] : [],
                    'pic'      => $sku['image'] ?? '',
                    'price'    => floatval($sku['price'] ?? 0),
                    'cost'     => floatval($sku['cost'] ?? 0),
                    'ot_price' => floatval($sku['ot_price'] ?? 0),
                    'stock'    => intval($sku['stock'] ?? 0),
                    'bar_code' => $sku['bar_code'] ?? '',
                    'bar_code_number' => $sku['bar_code_number'] ?? '',
                    'unique'   => $sku['unique'] ?? '',
                    'weight'   => floatval($sku['weight'] ?? 0),
                    'volume'   => floatval($sku['volume'] ?? 0),
                    'brokerage' => floatval($sku['brokerage'] ?? 0),
                    'brokerage_two' => floatval($sku['brokerage_two'] ?? 0),
                    'vip_price' => floatval($sku['vip_price'] ?? 0),
                    'is_show'  => $sku['is_show'] ?? 1,
                ];
                $num = 1;
                foreach ($attrArr as $v) {
                    $entry['value' . $num] = $v;
                    $num++;
                }
                $attrs[] = $entry;
            }

            $productInfo['items'] = $items;
            $productInfo['attrs'] = $attrs;
            $productInfo['attr'] = [
                'pic' => '', 'vip_price' => 0, 'price' => 0, 'cost' => 0, 'ot_price' => 0,
                'stock' => 0, 'bar_code' => '', 'bar_code_number' => '', 'weight' => 0,
                'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0,
            ];
        } else {
            // 单规格：直接使用 Handler 返回的默认 SKU 信息
            $productInfo['items'] = [];
            $productInfo['attrs'] = [];
            $productInfo['attr'] = [
                'pic'             => $skuInfo['image'] ?? '',
                'vip_price'       => floatval($skuInfo['vip_price'] ?? 0),
                'price'           => floatval($skuInfo['price'] ?? 0),
                'cost'            => floatval($skuInfo['cost'] ?? 0),
                'ot_price'        => floatval($skuInfo['ot_price'] ?? 0),
                'stock'           => floatval($skuInfo['stock'] ?? 0),
                'bar_code'        => $skuInfo['bar_code'] ?? '',
                'bar_code_number' => $skuInfo['bar_code_number'] ?? '',
                'virtual_list'    => [],
                'weight'          => floatval($skuInfo['weight'] ?? 0),
                'volume'          => floatval($skuInfo['volume'] ?? 0),
                'brokerage'       => floatval($skuInfo['brokerage'] ?? 0),
                'brokerage_two'   => floatval($skuInfo['brokerage_two'] ?? 0),
                'coupon_id'       => 0,
                'coupon_name'     => '',
                'disk_info'       => '',
                'unique'          => $skuInfo['unique'] ?? '',
            ];
        }
        // activity 字段已从中台表移除，使用空字符串兜底
        $activityRaw = $productInfo['activity'] ?? '';
        if ($activityRaw !== '' && $activityRaw !== null) {
            $activity = is_string($activityRaw) ? explode(',', $activityRaw) : (array)$activityRaw;
            foreach ($activity as $k => $v) {
                if ($v == 1) {
                    $activity[$k] = '秒杀';
                } elseif ($v == 2) {
                    $activity[$k] = '砍价';
                } elseif ($v == 3) {
                    $activity[$k] = '拼团';
                } elseif ($v == 0) {
                    $activity[$k] = '默认';
                }
            }
            $productInfo['activity'] = $activity;
        } else {
            $productInfo['activity'] = ['默认', '秒杀', '砍价', '拼团'];
        }
        $data['productInfo'] = $productInfo;
        return $data;
    }

    /**
     * 商品详情（用户端，兼容旧版扁平结构）
     * 返回旧版 goods_details/index.vue 消费的扁平字段：storeInfo / productAttr / productValue / reply / good_list 等
     * ProductDetailExtend 事件注入的扩展数据（label_list/protection_list/params_list/custom_form）自动写入 storeInfo
     * @param int $id 商品ID（中台商品 product_id）
     * @param int $uid 用户ID
     * @param string $activityType 活动类型（normal/integral/seckill/combination/bargain），默认 normal
     * @param int $activityId 活动商品ID（如积分商品ID），activity_type 非 normal 时使用
     * @return array 扁平结构，对应旧版接口格式
     * @throws \RuntimeException 商品不存在
     */
    public function getDetail(int $id, int $uid, string $activityType = 'normal', int $activityId = 0): array
    {
        // ========== 1. 中台商品基础数据 ==========
        $info = $this->dao->getOne(['id' => $id, 'is_show' => 1, 'is_del' => 0]);
        if (!$info) {
            throw new \RuntimeException('商品不存在');
        }
        $info = is_object($info) ? $info->toArray() : $info;
        $siteUrl = sys_config('site_url');
        $productType = $info['product_type'] ?? 'normal';

        // 会员商品可见性校验：vip_product=1 仅会员可见可买，非会员直接拦截详情页访问（防分享链接绕过列表过滤）
        $vipProduct = (int)($info['vip_product'] ?? 0);
        $isMoneyLevel = 0;
        if (in_array($vipProduct, [1, 2], true) && $uid > 0) {
            $isMoneyLevel = (int)app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level');
            // vip_product=1：仅会员可见可买，非会员直接拦截（列表已过滤，此处防分享链接直达）
            if ($vipProduct === 1 && $isMoneyLevel === 0) {
                throw new ApiException('此商品为会员专享，开通会员后可查看');
            }
        }

        // 通过 Handler 获取产品类型处理器
        $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
        $handler = $registry->get($productType);

        // ========== 2. storeInfo 基础信息（列名映射 → 旧版字段名）==========
        $sliderImages = $info['slider_image']
            ? (is_array($info['slider_image'])
                ? $info['slider_image']
                : (json_decode($info['slider_image'], true) ?: explode(',', $info['slider_image'])))
            : [];
        $sliderImages = array_map(function ($img) use ($siteUrl) {
            return set_file_url(trim($img), $siteUrl);
        }, $sliderImages);
        $videoLink = empty($info['video_link']) ? '' : (
            strpos($info['video_link'], 'http') === false ? $siteUrl . $info['video_link'] : $info['video_link']
        );
        $description = '';
        try {
            $descDao = app(\app\dao\product\product\StoreDescriptionDao::class);
            $descRow = $descDao->getDescription(['product_id' => $id, 'activity_type' => 'normal']);
            if ($descRow) {
                if (is_object($descRow)) $descRow = $descRow->toArray();
                $description = htmlspecialchars_decode($descRow['description'] ?? '');
            }
        } catch (\Throwable $e) {}

        $sales = ($info['sales'] ?? 0) + ($info['virtual_sales'] ?? 0);

        // storeInfo 按旧版字段名构建，eb_product_center 新列名做映射
        $storeInfo = [
            // ID/图片
            'id'               => $info['id'] ?? 0,
            // 中台商品ID与产品类型：供详情页下单链路（goCat 跳转订单确认页）使用
            'product_id'       => $info['id'] ?? 0,
            'product_type'     => $productType,
            'image'            => set_file_url($info['image'] ?? '', $siteUrl),
            'image_base'       => set_file_url($info['image'] ?? '', $siteUrl),
            'slider_image'     => $sliderImages,
            'recommend_image'  => $info['image'] ?? '',
            // 基本信息
            'store_name'       => $info['name'] ?? '',                 // eb_product_center.name → store_name
            'store_info'       => $info['intro'] ?? '',               // eb_product_center.intro → store_info
            'keyword'          => $info['keyword'] ?? '',
            'bar_code'         => $info['bar_code'] ?? '',
            'cate_id'          => $info['cate_id'] ?? '',
            'unit_name'        => $info['unit_name'] ?? '件',
            // 价格
            'price'            => $info['price'] ?? '0.00',
            'vip_price'        => $info['vip_price'] ?? '0.00',
            'ot_price'         => $info['original_price'] ?? '0.00',   // original_price → ot_price
            'postage'          => $info['postage'] ?? '0.00',
            'cost'             => $info['cost'] ?? '0.00',
            'give_integral'    => $info['give_integral'] ?? '0.00',
            // 库存/销量
            'stock'            => $info['stock'] ?? 0,
            'sales'            => $sales,
            'fsales'           => $sales,
            'ficti'            => $info['virtual_sales'] ?? 0,         // virtual_sales → ficti
            'browse'           => $info['views'] ?? 0,                 // views → browse
            // 排序
            'sort'             => $info['sort_order'] ?? 0,            // sort_order → sort
            // 状态位
            'is_show'          => (int)($info['is_show'] ?? 0),
            'is_hot'           => (int)($info['is_hot'] ?? 0),
            'is_benefit'       => (int)($info['is_benefit'] ?? 0),
            'is_best'          => (int)($info['is_best'] ?? 0),
            'is_new'           => (int)($info['is_new'] ?? 0),
            'is_virtual'       => (int)($info['is_virtual'] ?? 0),
            'virtual_type'     => (int)($info['virtual_type'] ?? 0),
            'is_postage'       => (int)($info['is_postage'] ?? 0),
            'is_del'           => (int)($info['is_del'] ?? 0),
            'is_seckill'       => (int)($info['is_seckill'] ?? 0),
            'is_bargain'       => $info['is_bargain'] ?? null,
            'is_good'          => (int)($info['is_good'] ?? 0),
            'is_sub'           => (int)($info['is_sub'] ?? 0),
            'is_vip'           => (int)($info['is_vip'] ?? 0),
            'is_gift'          => (int)($info['is_gift'] ?? 0),
            'gift_price'       => $info['gift_price'] ?? '0.00',
            'is_limit'         => (int)($info['is_limit'] ?? 0),
            'limit_type'       => (int)($info['limit_type'] ?? 0),
            'limit_num'        => (int)($info['limit_num'] ?? 0),
            'min_qty'          => (int)($info['min_qty'] ?? 1),
            // 规格/类型
            'spec_type'        => (int)($info['spec_type'] ?? 0),
            'activity'         => $info['activity'] ?? '',
            'spu'              => $info['spu'] ?? '',
            'label_id'         => $info['label_id'] ?? '',
            // 扩展
            'video_link'       => $videoLink,
            'command_word'     => $info['command_word'] ?? '',
            'recommend_list'   => $info['recommend_list'] ?? '',
            'vip_product'      => (int)($info['vip_product'] ?? 0),
            // vip_product=2 且当前用户非会员时下发 true，前端据此渲染"开通会员后可购买"引导按钮
            'vip_exclusive'    => ($vipProduct === 2 && $isMoneyLevel === 0),
            // 预售
            'presale'           => (int)($info['presale'] ?? 0),
            'presale_start_time' => $info['presale_start_time'] ?? '1970-01-01 08:00',
            'presale_end_time'   => $info['presale_end_time'] ?? '1970-01-01 08:00',
            'presale_day'        => (int)($info['presale_day'] ?? 0),
            // 物流
            'logistics'         => $info['logistics'] ?? '',
            'freight'           => (int)($info['freight'] ?? 0),
            // 时间
            'add_time'          => $info['add_time'] ?? 0,
            // 其他
            'code_path'         => $info['code_path'] ?? '',
            'soure_link'        => $info['soure_link'] ?? '',
            'temp_id'           => (int)($info['temp_id'] ?? 0),
            'mer_id'            => (int)($info['mer_id'] ?? 0),
            'mer_use'           => (int)($info['mer_use'] ?? 0),
            'default_sku'       => $info['default_sku'] ?? '',
            // 描述
            'description'       => $description,
            // 微信码（旧版前端依赖 wechat_code）
            'wechat_code'       => '',
            // 扩展字段（后续由 ProductDetailExtend 事件覆盖）
            'custom_form'       => [],
            'params_list'       => [],
            'protection_list'   => [],
            'label_list'        => [],
        ];

        // ========== 3. 规格数据（Handler 负责）==========
        $specs = $handler->getDetailSpecs($id);
        $productAttr = $specs['productAttr'] ?? [];
        $productValue = $specs['productValue'] ?? [];
        // 规格图拼站点域名（Handler 返回库内相对路径；仅展示侧拼接，编辑/存储链路保持相对路径）
        foreach ($productValue as &$value) {
            $value['image'] = set_file_url($value['image'] ?? '', $siteUrl);
        }
        unset($value);
        foreach ($productAttr as &$attrItem) {
            if (empty($attrItem['attr_value'])) {
                continue;
            }
            foreach ($attrItem['attr_value'] as &$attrValue) {
                if (is_array($attrValue) && !empty($attrValue['pic'])) {
                    $attrValue['pic'] = set_file_url($attrValue['pic'], $siteUrl);
                }
            }
            unset($attrValue);
        }
        unset($attrItem);
        $specUnique = '';
        if (count($productValue) === 1) {
            $firstValue = reset($productValue);
            $specUnique = $firstValue['unique'] ?? '';
        }
        // 收集规格图片
        $attrPics = [];
        foreach ($productAttr as $attrValues) {
            foreach ($attrValues['attr_value'] ?? [] as $picArr) {
                if (isset($picArr['pic']) && $picArr['pic'] !== '') $attrPics[] = $picArr['pic'];
            }
        }
        $storeInfo['attrPics'] = $attrPics;

        // ========== 4. 价格体系 ==========
        $svipOpen = false;
        try {
            $memberCardService = app()->make(MemberCardServices::class);
            $svipOpen = $memberCardService->isOpenMemberCard('vip_price');
        } catch (\Throwable $e) {}
        $svipPriceOpen = (int)sys_config('member_price_status');
        $storeInfo['svip_economize_price'] = bcsub((string)$storeInfo['price'], (string)$storeInfo['vip_price'], 2);
        if (!($svipOpen && sys_config('member_card_status') && $storeInfo['is_vip'] && sys_config('member_price_status', 1))) {
            $storeInfo['vip_price'] = 0;
        }

        // ========== 5. 用户状态 ==========
        $userCollect = false;
        try {
            $relationService = app()->make(\app\services\product\product\StoreProductRelationServices::class);
            $userCollect = $relationService->isProductRelation([
                'uid' => $uid, 'product_id' => $id, 'type' => 'collect', 'category' => 'product',
            ]);
        } catch (\Throwable $e) {}
        $isMoneyLevel = 0;
        try {
            if ($uid) {
                $isMoneyLevel = app()->make(\app\services\user\UserServices::class)->value(['uid' => $uid], 'is_money_level');
            }
        } catch (\Throwable $e) {}
        $storeInfo['userCollect'] = $userCollect;
        $storeInfo['userLike'] = false;
        $storeInfo['small_image'] = $storeInfo['image'];
        $storeInfo = get_thumb_water($storeInfo, 'small', ['small_image']);

        // ========== 6. 分销价标识 ==========
        $priceName = 0;
        if ($uid && (int)sys_config('brokerage_window_switch', 1)) {
            $priceName = $this->getPacketPrice($storeInfo, $productValue, $uid);
        }

        // ========== 7. 预售状态 ==========
        if ($storeInfo['presale']) {
            if ($storeInfo['presale_start_time'] > time()) {
                $storeInfo['presale_pay_status'] = 1;
            } elseif ($storeInfo['presale_start_time'] <= time() && $storeInfo['presale_end_time'] > time()) {
                $storeInfo['presale_pay_status'] = 2;
            } elseif ($storeInfo['presale_end_time'] < time()) {
                $storeInfo['presale_pay_status'] = 3;
            } else {
                $storeInfo['presale_pay_status'] = 0;
            }
        } else {
            $storeInfo['presale_pay_status'] = 0;
        }
        $storeInfo['presale_start_time'] = date('Y-m-d H:i', $storeInfo['presale_start_time']);
        $storeInfo['presale_end_time'] = date('Y-m-d H:i', $storeInfo['presale_end_time']);

        // 自定义表单/预售/虚拟商品不展示加入购物车按钮（custom_form 在步骤 13 注入后判断）

        // ========== 8. 评价 ==========
        $reply = null;
        $replyCount = 0;
        $replyChance = 0;
        try {
            $replyService = app()->make(\app\services\product\product\StoreProductReplyServices::class);
            $reply = get_thumb_water($replyService->getRecProductReply($id), 'big', ['pics']);
            [$replyCount, $goodReply, $replyChance] = $replyService->getProductReplyData($id);
        } catch (\Throwable $e) {}

        // ========== 9. 优惠券 ==========
        $coupons = [];
        try {
            $couponService = app()->make(StoreCouponIssueServices::class);
            $couponRes = $couponService->getIssueCouponList($uid, ['product_id' => $id, 'type' => -1]);
            $coupons = $couponRes['list'] ?? [];
        } catch (\Throwable $e) {}

        // ========== 10. 推荐商品 ==========
        $goodList = [];
        $recommendRaw = $info['recommend_list'] ?? [];
        if (!empty($recommendRaw)) {
            $recommendIds = is_array($recommendRaw)
                ? array_column($recommendRaw, 'product_id')
                : array_map('intval', explode(',', (string)$recommendRaw));
            try {
                $goodList = $recommendIds
                    ? $this->getProducts(['ids' => $recommendIds, 'is_del' => 0, 'is_show' => 1], 12)
                    : [];
            } catch (\Throwable $e) {}
        }

        // ========== 11. 活动 ==========
        $activity = [];
        try {
            $activity = $this->getActivityList($storeInfo, false);
        } catch (\Throwable $e) {}

        // ========== 12. 配置 ==========
        $mapKey = sys_config('tengxun_map_key');
        $storeSelfMention = (int)(sys_config('store_self_mention') ?? 0);
        $routineContactType = sys_config('routine_contact_type', 0);

        // ========== 13. 触发 ProductDetailExtend 事件，扩展层注入扩展数据 ==========
        // 扩展数据容器（事件监听器写入 extensions.*）
        $extensions = [];
        // 展示块容器（事件监听器写入 display_sections，按文档 4.4 order 区间约定）
        $displaySections = [];
        $eventData = [
            'extensions'       => &$extensions,
            'display_sections' => &$displaySections,
            'product_type'     => $productType,
            'activity_type'    => $activityType,
            'activity_id'      => $activityId,
            'product_id'       => $id,
            'uid'              => $uid,
            'handler'          => $handler,
            'info'             => $info,
            'store_info'       => &$storeInfo,
            'product_value'    => &$productValue,
            'specs'            => $specs,
        ];
        //  
        event('ProductDetailExtend', $eventData);

        // display_sections 按 order 升序排序（各监听器遵守区间约定：产品类型 10-99 / 交易模式 100-199 / 营销 200-299）
        usort($displaySections, function ($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });

        // 将扩展数据注入 storeInfo（旧版前端直接从 storeInfo 读取）
        if (!empty($extensions)) {
            if (isset($extensions['label_list'])) {
                $storeInfo['label_list'] = $extensions['label_list'];
            } else {
                $storeInfo['label_list'] = [];
            }
            if (isset($extensions['protection_list'])) {
                $storeInfo['protection_list'] = $extensions['protection_list'];
            } else {
                $storeInfo['protection_list'] = [];
            }
            if (isset($extensions['params_list'])) {
                $storeInfo['params_list'] = $extensions['params_list'];
            } else {
                $storeInfo['params_list'] = [];
            }
            if (isset($extensions['custom_form'])) {
                $storeInfo['custom_form'] = $extensions['custom_form'];
            } else {
                $storeInfo['custom_form'] = [];
            }
        } else {
            $storeInfo['label_list'] = [];
            $storeInfo['protection_list'] = [];
            $storeInfo['params_list'] = [];
            $storeInfo['custom_form'] = [];
        }

        // 透传选择/加购契约到 storeInfo（前端按契约动态渲染详情页选择区与加购按钮栏）
        $storeInfo['selection_spec'] = $handler->getSelectionSpec();
        $storeInfo['cart_strategy']  = $handler->getCartStrategy();

        // 自定义表单商品必须填写表单后直接下单，不支持加入购物车，强制 direct 单按钮模式
        if ($this->hasCustomForm($storeInfo['custom_form'] ?? [])) {
            $storeInfo['cart_strategy']['cart_mode'] = 'direct';
        }

        // cart_button 由 cart_strategy.cart_mode 决定：direct 模式不展示加入购物车按钮
        // 预售商品在模板中走独立分支，不影响此处判断
        $storeInfo['cart_button'] = ($storeInfo['cart_strategy']['cart_mode'] ?? 'cart') === 'direct' ? 0 : 1;

        // ========== 14. 组装扁平返回数据 ==========
        $result = [
            'uid'                   => $uid,
            'is_money_level'        => $isMoneyLevel,
            'spec_unique'           => $specUnique,
            'productAttr'           => $productAttr,
            'productValue'          => $productValue,
            'storeInfo'             => $storeInfo,
            'svip_open'             => $svipOpen,
            'svip_price_open'       => $svipPriceOpen,
            'priceName'             => $priceName,
            'reply'                 => $reply,
            'replyChance'           => $replyChance,
            'replyCount'            => $replyCount,
            'mer_id'                => 0,
            'good_list'             => $goodList,
            'mapKey'                => $mapKey,
            'store_self_mention'    => $storeSelfMention,
            'activity'              => $activity,
            'coupons'               => $coupons,
            'routine_contact_type'  => $routineContactType,
            // 展示块数组（数据驱动详情页渲染，各产品类型监听器输出，前端 P3 阶段消费）
            'display_sections'      => $displaySections,
        ];

        // ========== 15. 访问事件（二开可修改 &$result）==========
        try {
            event('UserVisit', [$uid, $id, 'product', $storeInfo['cate_id'], 'view']);
            event('Product', ['visit', [$id, $uid, date('Y-m-d H:i:s'), &$result]]);
        } catch (\Throwable $e) {}

        return $result;
    }

    /**
     * 组装默认 page_config sections 声明式渲染配置
     * 根据商品数据和产品类型，生成 sections 数组，前端引擎按此配置动态渲染
     * 当 Handler::getPageConfig('detail') 未提供 sections 时使用此默认配置
     * @param array $data 已组装的功能域分组数据
     * @return array {sections, bottom_bar, submit_api}
     */
    protected function buildDefaultPageConfig(array $data): array
    {
        $productType = $data['product_type'] ?? 'normal';
        $extensions = $data['extensions'] ?? [];
        $sections = [];

        // 该类型的下单表单组件由 Handler 声明（如酒店的日期范围/预约表单），中台按声明渲染，不硬编码扩展产品类型
        $orderFormComponents = app()->make(ProductTypeRegistry::class)->get($productType)->getOrderFormComponents();

        // 辅助：添加一个 section
        $add = function (string $key, string $component, array $props, bool $visible, int $order) use (&$sections) {
            $sections[] = [
                'key'       => $key,
                'component' => $component,
                'props'     => $props,
                'visible'   => $visible,
                'order'     => $order,
            ];
        };

        // 10. 图片轮播
        $add('carousel', 'image_carousel', ['images' => '$.product.slider_image', 'video_link' => '$.product.video_link'], true, 10);
        // 15. 履约提示条（自动发货/到店核销等）
        $add('notice', 'notice_bar', ['text' => '$.extensions.fulfillment_preview'], !empty($extensions['fulfillment_preview']), 15);
        // 20. 商品基础信息
        $add('product_info', 'product_info', ['product' => '$.product', 'price' => '$.price', 'type_info' => '$.extensions.type_info'], true, 20);
        // 22. 浏览/销量/最近成交
        $add('social_proof', 'social_proof', ['social_proof' => '$.social_proof'], true, 22);
        // 25. 会员价标签
        $add('price_tags', 'price_tags', ['price' => '$.price'], !empty($data['price']['svip_open']), 25);
        // 28. 服务承诺
        $add('service_promise', 'service_promise', ['promises' => '$.service_promise.promises'], !empty($data['service_promise']['promises']), 28);
        // 30. 活动入口
        $add('activity', 'activity_entry', ['activity' => '$.activity.activity'], !empty($data['activity']['activity']), 30);
        // 35. 优惠券
        $add('coupon', 'coupon_entry', ['coupons' => '$.coupon.coupons'], !empty($data['coupon']['coupons']), 35);
        // 36. 营销（满减满赠）
        $add('promotion', 'promotion_banner', ['marketing' => '$.marketing'], !empty($extensions['marketing']) || !empty($data['marketing']['full_reduction']), 36);
        // 40. SKU 规格
        $add('sku', 'sku_selector', ['productAttr' => '$.specs.productAttr', 'productValue' => '$.specs.productValue'], true, 40);
        // 42. 场次选择（票务）
        $add('sessions', 'session_picker', ['sessions' => '$.extensions.sessions'], !empty($extensions['sessions']), 42);
        // 44. 日期库存日历（票务/酒店）
        $add('date_inventory', 'date_inventory_calendar', ['date_inventory' => '$.extensions.date_inventory'], !empty($extensions['date_inventory']), 44);
        // 46. 日期范围（酒店）——组件是否挂载由对应产品类型 Handler::getOrderFormComponents() 声明，中台不硬编码扩展类型
        $add('date_range', 'date_range_picker', ['booking' => '$.extensions.booking'], !empty($extensions['booking']) && in_array('date_range', $orderFormComponents), 46);
        // 48. 房型列表（酒店）
        $add('room_types', 'room_type_list', ['room_types' => '$.extensions.room_types'], !empty($extensions['room_types']), 48);
        // 50. 自定义表单
        $add('custom_form', 'form_builder', ['fields' => '$.extensions.custom_form'], !empty($extensions['custom_form']), 50);
        // 52. 实名表单（票务）
        $add('realname', 'realname_form', ['realname' => '$.extensions.realname'], !empty($extensions['realname']), 52);
        // 54. 预约表单（酒店/上门）——组件是否挂载由对应产品类型 Handler::getOrderFormComponents() 声明，中台不硬编码扩展类型
        $add('booking', 'booking_form', ['booking' => '$.extensions.booking'], !empty($extensions['booking']) && in_array('booking', $orderFormComponents), 54);
        // 56. 上门预约
        $add('appointment', 'appointment_picker', ['booking' => '$.extensions.booking'], !empty($extensions['onsite']), 56);
        // 58. 服务人员（上门）
        $add('service_staff', 'service_staff', ['staffs' => '$.extensions.onsite.staffs'], !empty($extensions['onsite']), 58);
        // 60. 参数列表
        $add('params', 'params_list', ['params' => '$.extensions.params_list'], !empty($extensions['params_list']), 60);
        // 62. 保障服务
        $add('protection', 'protection_list', ['protections' => '$.extensions.protection_list'], !empty($extensions['protection_list']), 62);
        // 64. 商品标签
        $add('label', 'label_list', ['labels' => '$.extensions.label_list'], !empty($extensions['label_list']), 64);
        // 65. 配送至（收货地址）
        $add('address', 'address_selector', ['address' => '$.address'], !empty($data['shipping']['requires_shipping']), 65);
        // 66. 配送/运费
        $add('shipping', 'shipping_info', ['shipping' => '$.shipping'], !empty($data['shipping']['requires_shipping']), 66);
        // 68. 门店信息（自提）
        $add('store', 'store_info', ['store' => '$.store'], !empty($data['shipping']['supports_pickup']), 68);
        // 70. 商家卡片（到店团购）
        $add('merchant', 'merchant_card', ['merchant' => '$.extensions.merchant'], !empty($extensions['merchant']), 70);
        // 72. 套餐内容（props key 用 package_data，引擎转驼峰为 packageData，避免 package 保留字冲突）
        $add('package', 'package_content', ['package_data' => '$.extensions.package'], !empty($extensions['package']), 72);
        // 74. 多门店列表
        $add('stores', 'store_list', ['stores' => '$.extensions.stores'], !empty($extensions['stores']), 74);
        // 76. 票务信息
        $add('ticket', 'ticket_info', ['ticket' => '$.extensions.ticket'], !empty($extensions['ticket']), 76);
        // 78. 核销方式
        $add('verify', 'verify_info', ['verify' => '$.extensions.verify'], !empty($extensions['verify']), 78);
        // 80. 设施服务
        $add('facility', 'facility_list', ['facilities' => '$.extensions.facilities'], !empty($extensions['facilities']), 80);
        // 82. 服务区域（上门）
        $add('service_area', 'service_area', ['onsite' => '$.extensions.onsite'], !empty($extensions['onsite']), 82);
        // 84. 地图位置
        $add('map', 'map_card', ['location' => '$.location', 'config' => '$.config'], !empty($data['location']['latitude']), 84);
        // 86. 评价区
        $add('reply', 'reply_section', ['reply' => '$.reply'], !empty($data['reply']['reply']), 86);
        // 88. 短视频/买家秀
        $add('video', 'video_show', ['videos' => '$.extensions.video'], !empty($extensions['video']), 88);
        // 90. 推荐商品
        $add('recommend', 'recommend_list', ['recommend' => '$.recommend'], !empty($data['recommend']['good_list']) || !empty($data['recommend']['recommend_list']), 90);
        // 92. 购买须知
        $add('purchase_notice', 'purchase_notice', ['notices' => '$.purchase_notice.notices'], !empty($data['purchase_notice']['notices']), 92);
        // 94. 分销悬浮框（到手价/自购省/预估佣金）
        $add('spread_float', 'spread_float', ['spread' => '$.extensions.spread'], !empty($extensions['spread']) && !empty($data['config']['spread_switch']), 94);
        // 95. 分享海报（生成海报+保存相册）
        $add('share_poster', 'share_poster', ['spread' => '$.extensions.spread'], !empty($extensions['spread']) && !empty($data['config']['spread_switch']), 95);
        // 96. 分销素材（文案/图片/视频）
        $add('spread_material', 'spread_material', ['spread' => '$.extensions.spread'], !empty($extensions['spread']), 96);
        // 97. 图文详情
        $add('description', 'rich_text', ['content' => '$.product.description'], true, 97);

        // 按 order 升序排序
        usort($sections, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return [
            'sections'   => $sections,
            'bottom_bar' => [
                'text'   => '立即购买',
                'action' => 'submitOrder',
                // cart_button 由 cart_strategy.cart_mode 决定：direct 模式不展示加入购物车按钮
                // 自定义表单商品需填表后直接下单，同样不展示加入购物车
                'extra'  => ['cart_button' => !$this->hasCustomForm($extensions['custom_form'] ?? []) && $this->getCartModeByType($productType) !== 'direct'],
            ],
            'submit_api' => 'center/order/create',
        ];
    }

    /**
     * 获取运费模板列表
     * 通过事件机制解耦，由 modules/product/normal 监听器返回数据
     * @return array
     */
    public function getTemp()
    {
        // 直接调用 app/ 层运费模板服务
        /** @var \app\services\shipping\ShippingTemplatesServices $shippingServices */
        $shippingServices = app()->make(\app\services\shipping\ShippingTemplatesServices::class);
        return $shippingServices->getSelectList();
    }

    /**
     * 获取商品规格
     * @param array $data
     * @param int $id
     * @param int $type
     * @return array
     */
    public function getAttr(array $data, int $id, int $type)
    {
        /** @var StoreCouponIssueServices $storeCouponIssueServices */
        $storeCouponIssueServices = app()->make(StoreCouponIssueServices::class);
        $attr = $data['attrs'];
        $is_virtual = $data['is_virtual']; //是否虚拟商品
        $productType = $data['product_type'] ?? 'normal'; //产品类型
        $attr = array_merge($attr);
        list($value, $head) = attr_format($attr);
        $valueNew = [];
        $count = 0;

        // 编辑商品时，从产品类型 Handler 读取已有 SKU 数据作为默认值
        $existingSkus = [];
        $skuExtraFields = [];
        try {
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($productType);
            $skuExtraFields = $handler->getSkuExtraFields();
            if ($id) {
                $specs = $handler->getDetailSpecs($id);
                $existingSkus = $specs['productValue'] ?? [];
            }
        } catch (\Throwable $e) {
            // 未注册类型回退到 normal，无扩展字段
        }

        foreach ($value as $suk) {
            $detail = explode(',', $suk);

            $types = 1;
            if ($id) {
                $sku = $existingSkus[$suk] ?? [];
                if (empty($sku)) {
                    if ($type == 0) $types = 0; //编辑商品时，将没有规格的数据不生成默认值
                    $sukValue[$suk]['pic'] = '';
                    $sukValue[$suk]['price'] = 0;
                    $sukValue[$suk]['cost'] = 0;
                    $sukValue[$suk]['ot_price'] = 0;
                    $sukValue[$suk]['stock'] = 0;
                    $sukValue[$suk]['bar_code'] = '';
                    $sukValue[$suk]['bar_code_number'] = '';
                    if ($is_virtual) {
                        foreach ($skuExtraFields as $field) {
                            $sukValue[$suk][$field['key']] = $field['new_default'];
                        }
                    }
                    $sukValue[$suk]['weight'] = 0;
                    $sukValue[$suk]['volume'] = 0;
                    $sukValue[$suk]['brokerage'] = 0;
                    $sukValue[$suk]['brokerage_two'] = 0;
                } else {
                    $sukValue[$suk] = [
                        'pic'             => $sku['image'] ?? '',
                        'price'           => $sku['price'] ?? 0,
                        'cost'            => $sku['cost'] ?? 0,
                        'ot_price'        => $sku['ot_price'] ?? 0,
                        'stock'           => $sku['stock'] ?? 0,
                        'bar_code'        => $sku['bar_code'] ?? '',
                        'bar_code_number' => $sku['bar_code_number'] ?? '',
                        'unique'          => $sku['unique'] ?? '',
                        'weight'          => $sku['weight'] ?? 0,
                        'volume'          => $sku['volume'] ?? 0,
                        'brokerage'       => $sku['brokerage'] ?? 0,
                        'brokerage_two'   => $sku['brokerage_two'] ?? 0,
                        'vip_price'       => $sku['vip_price'] ?? 0,
                    ];
                    if ($is_virtual) {
                        foreach ($skuExtraFields as $field) {
                            $key = $field['key'];
                            $sukValue[$suk][$key] = $sku[$key] ?? $field['new_default'];
                        }
                    }
                }
            } else {
                $sukValue[$suk]['pic'] = '';
                $sukValue[$suk]['price'] = 0;
                $sukValue[$suk]['cost'] = 0;
                $sukValue[$suk]['ot_price'] = 0;
                $sukValue[$suk]['stock'] = 0;
                $sukValue[$suk]['bar_code'] = '';
                $sukValue[$suk]['bar_code_number'] = '';
                if ($is_virtual) {
                    foreach ($skuExtraFields as $field) {
                        $sukValue[$suk][$field['key']] = $field['new_default'];
                    }
                }
                $sukValue[$suk]['weight'] = 0;
                $sukValue[$suk]['volume'] = 0;
                $sukValue[$suk]['brokerage'] = 0;
                $sukValue[$suk]['brokerage_two'] = 0;
            }
            if ($types) { //编辑商品时，将没有规格的数据不生成默认值
                foreach ($head as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 130;
                }
                foreach ($detail as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = array_combine($head, $detail);
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = !empty($sukValue[$suk]['price']) ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = !empty($sukValue[$suk]['cost']) ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['vip_price'] = isset($sukValue[$suk]['vip_price']) ? floatval($sukValue[$suk]['vip_price']) : 0;
                $valueNew[$count]['stock'] = !empty($sukValue[$suk]['stock']) ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['bar_code_number'] = $sukValue[$suk]['bar_code_number'] ?? '';
                if ($is_virtual) {
                    foreach ($skuExtraFields as $field) {
                        $key = $field['key'];
                        // 编辑模式（type=0）回填所有扩展字段；新增模式仅回填有 slot 声明的字段
                        if (!$type || !empty($field['slot'])) {
                            $valueNew[$count][$key] = $sukValue[$suk][$key] ?? $field['new_default'];
                        }
                    }
                }
                $valueNew[$count]['weight'] = floatval($sukValue[$suk]['weight'] ?? 0);
                $valueNew[$count]['volume'] = floatval($sukValue[$suk]['volume'] ?? 0);
                $valueNew[$count]['brokerage'] = floatval($sukValue[$suk]['brokerage'] ?? 0);
                $valueNew[$count]['brokerage_two'] = floatval($sukValue[$suk]['brokerage_two'] ?? 0);
                $count++;
            }
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '划线价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '商品编码', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '条形码', 'slot' => 'bar_code_number', 'align' => 'center', 'minWidth' => 140];
        if ($is_virtual) {
            // 由产品类型 Handler 声明是否需要渲染网盘产品列
            if (!empty($skuExtraFields)) {
                $header[] = ['title' => '网盘信息', 'slot' => 'fictitious', 'align' => 'center', 'minWidth' => 140];
            }
        } else {
            $header[] = ['title' => '重量(KG)', 'slot' => 'weight', 'align' => 'center', 'minWidth' => 140];
            $header[] = ['title' => '体积(m³)', 'slot' => 'volume', 'align' => 'center', 'minWidth' => 140];
        }
        $header[] = ['title' => '操作', 'slot' => 'action', 'align' => 'center', 'minWidth' => 70];
        return ['attr' => $attr, 'value' => $valueNew, 'header' => $header];
    }

    /**
     * SPU
     * @return string
     */
    public function createSpu()
    {
        return substr(implode('', array_map('ord', str_split(substr(uniqid('', true), 7, 13), 1))), 0, 8) . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 新增编辑商品
     * @param int $id
     * @param array $data
     */
    public function save(int $id, array $data, bool $fromModule = false)
    {
        // 前端使用 label_id 字段提交标签ID，中台表实际存储字段为 label_list
        if (!empty($data['label_id']) && empty($data['label_list'])) {
            $data['label_list'] = is_array($data['label_id']) ? $data['label_id'] : explode(',', $data['label_id']);
        }

        // is_sub（分销开关）和 is_vip（会员价开关）是独立标量字段，兼容旧版数组格式
        
        $data['is_vip'] = intval($data['is_vip'] ?? 0);
        $data['is_sub'] = intval($data['is_sub'] ?? 0);
        

        // 已迁移商品类型（普通/卡密/虚拟）通过产品类型 Handler 转发到对应模块的保存服务
        if (!$fromModule) {
            $productType = $data['product_type'] ?? 'normal';
            /** @var ProductTypeRegistry $registry */
            $registry = app()->make(ProductTypeRegistry::class);
            // has 前置判断避免未注册类型回退 normal；Handler 返回 null 表示该类型无模块化保存
            if ($registry->has($productType)) {
                $result = $registry->get($productType)->saveFromAdmin($id, $data);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        if (count($data['cate_id']) < 1) throw new AdminException('请选择商品分类');
        if (!$data['store_name']) throw new AdminException('请输入商品名称');
        if (count($data['slider_image']) < 1) throw new AdminException('请选择商品轮播图');
        if ($data['is_limit'] == 1 && $data['min_qty'] > $data['limit_num']) throw new AdminException('起购数量不能大于限购数量');

        $detail = $data['attrs'];
        $attr = $data['items'];
        $cate_id = $data['cate_id'];
        $coupon_ids = $data['coupon_ids'];
        $description = $data['description'];
        $type = $data['type'];
        $data['recommend_list'] = count($data['recommend_list']) ? implode(',', array_column($data['recommend_list'], 'product_id')) : '';
        $data['vip_product'] = intval($data['vip_product']);
        $data['presale'] = intval($data['presale']);
        $data['presale_start_time'] = $data['presale'] ? strtotime($data['presale_time'][0]) : 0;
        $data['presale_end_time'] = $data['presale'] ? strtotime($data['presale_time'][1]) : 0;
        // 预售发货时间（预售活动结束后N天内发货）：>0 才覆盖写入，未传时新增走列默认值1、编辑保持原值
        if (isset($data['presale_day']) && intval($data['presale_day']) > 0) {
            $data['presale_day'] = intval($data['presale_day']);
        } else {
            unset($data['presale_day']);
        }
        $data['is_limit'] = intval($data['is_limit']);
        if (!$data['is_limit']) {
            $data['limit_type'] = 0;
            $data['limit_num'] = 0;
        } else {
            if (!in_array($data['limit_type'], [1, 2])) throw new AdminException('请选择限购类型');
            if ($data['limit_num'] <= 0) throw new AdminException('限购数量不能小于1');
        }
        // 根据产品类型 Handler 判断是否为虚拟商品
        try {
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $data['is_virtual'] = $registry->get($data['product_type'] ?? 'normal')->isVirtual() ? 1 : 0;
        } catch (\Throwable $e) {
            $data['is_virtual'] = 0;
        }
        $data['logistics'] = implode(',', $data['logistics']);
        $data['custom_form'] = json_encode($data['custom_form']);
        $data['params_list'] = json_encode($data['params_list']);
        $data['label_list'] = implode(',', $data['label_list']);
        $data['protection_list'] = implode(',', $data['protection_list']);
        if ($data['freight'] == 2) {
            $data['temp_id'] = 0;
        } elseif ($data['freight'] == 3) {
            $data['postage'] = 0;
        }
        foreach ($detail as &$item) {
            // 未开启分销时清空 SKU 返佣字段
            if (!$data['is_sub']) {
                $item['brokerage'] = 0;
                $item['brokerage_two'] = 0;
            }
            if (($item['brokerage'] + $item['brokerage_two']) > $item['price']) {
                throw new AdminException('一二级返佣相加不能大于商品售价');
            }
            if (isset($item['detail'])) {
                foreach ($item['detail'] as $detail_k => $detail_v) {
                    if (preg_match('/[=;]/', $detail_k) === 1 || preg_match('/[=;]/', $detail_v) === 1) {
                        throw new AdminException('规格以及规格值中不能包含=或;符号');
                    }
                }
            }
        }
        foreach ($data['activity'] as $k => $v) {
            if ($v == '秒杀') {
                $data['activity'][$k] = 1;
            } elseif ($v == '砍价') {
                $data['activity'][$k] = 2;
            } elseif ($v == '拼团') {
                $data['activity'][$k] = 3;
            } else {
                $data['activity'][$k] = 0;
            }
        }

        if ($data['spec_type'] == 1) {
            $defaultItems = array_filter($detail, function ($item) {
                return $item['is_default_select'] === 1;
            });
            if (!empty($defaultItems)) {
                $defaultItem = reset($defaultItems);
                $data['price'] = $defaultItem['price'];
                $data['ot_price'] = $defaultItem['ot_price'];
                $data['cost'] = $defaultItem['cost'] ?? 0;
            } else {
                $filtered = array_filter($detail, function ($item) {
                    return $item['is_show'] == 1;
                });
                $data['price'] = min(array_column($filtered, 'price'));
                $data['ot_price'] = min(array_column($filtered, 'ot_price'));
                $data['cost'] = isset($data['cost']) ? min(array_column($filtered, 'cost')) : 0;
                if (!$data['cost']) {
                    $data['cost'] = 0;
                }
            }
        } else {
            $data['price'] = min(array_column($detail, 'price'));
            $data['ot_price'] = min(array_column($detail, 'ot_price'));
            $data['cost'] = isset($data['cost']) ? min(array_column($detail, 'cost')) : 0;
            if (!$data['cost']) {
                $data['cost'] = 0;
            }
        }

        $data['activity'] = implode(',', $data['activity']);
        $data['cate_id'] = implode(',', $data['cate_id']);
        // 中台表使用 label_list 字段，label_id 为前端提交字段，已在方法入口映射
        $slider_image = $data['slider_image'];
        $data['image'] = $data['slider_image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['description'], $data['coupon_ids'], $data['items'], $data['attrs'], $data['type']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        $is_copy = $data['is_copy'] ?? 0;
        unset($data['is_copy']);
        $descriptionImages = [];
        if (isset($data['description_images'])) {
            $descriptionImages = $data['description_images'];
        }
        return $this->transaction(function () use ($id, $is_copy, $data, $descriptionImages, $description, $cate_id, $storeDescriptionServices, $storeProductCateServices, $storeProductCouponServices, $storeCategoryServices, $coupon_ids, $type, $slider_image) {
            if ($id) {
                $oldInfo = $this->get($id)->toArray();
                if (($oldInfo['product_type'] ?? 'normal') != ($data['product_type'] ?? 'normal')) {
                    throw new AdminException('商品类型不能切换');
                }
                unset($data['sales']);
                $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription($id, $description, 'normal');
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'] ?: $cid, 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($id, $cateData);
                if (!empty($coupon_ids)) {
                    $storeProductCouponServices->setCoupon($id, $coupon_ids);
                } else {
                    $storeProductCouponServices->delete(['product_id' => $id]);
                }

                // 商品更新事件
                event('Product', ['update', [$id, $oldInfo, $data]]);

                // 商品价格变动事件（检测价格是否变化）
                if (isset($oldInfo['price']) && $oldInfo['price'] != $data['price']) {
                    event('Product', ['price_change', [$id, '', $oldInfo['price'], $data['price'], 'update']]);
                }

                return $id;

            } else {
                $data['add_time'] = time();
                $data['code_path'] = '';
                $data['spu'] = $this->createSpu();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription($res->id, $description, 'normal');
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $res->id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($res->id, $cateData);
                if (!empty($coupon_ids)) $storeProductCouponServices->setCoupon($res->id, $coupon_ids);

                // 商品创建事件
                event('Product', ['create', $data]);

                //采集商品下载图片
                if ($type == -1) {
                    $s_image_down = [];
                    //下载商品轮播图
                    foreach ($slider_image as $s_image) {
                        if (sys_config('queue_open', 0) == 1) {
                            ProductCopyJob::dispatch('copySliderImage', [$res->id, $s_image, count($slider_image)]);
                        } else {
                            //下载图片
                            $s_image_down[] = app()->make(CopyProductServices::class)->downloadCopyImage(!is_int(strpos($s_image, 'http')) ? 'http://' . ltrim($s_image, '\//') : $s_image);
                        }
                    }

                    //下载商品详情图
                    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $description, $match);
                    foreach ($match[1] as $d_image) {
                        if (sys_config('queue_open', 0) == 1) {
                            ProductCopyJob::dispatch('copyDescriptionImage', [$res->id, $description, $d_image, count($match[1])]);
                        } else {
                            $d_img = app()->make(CopyProductServices::class)->downloadCopyImage(!is_int(strpos($d_image, 'http')) ? 'http://' . ltrim($d_image, '\//') : $d_image);
                            $description = str_replace($d_image, $d_img, $description);
                        }
                    }

                    // TODO: 商品规格图下载已迁移至各产品类型 ProductSaveServices，旧规格表弃用后此处不再处理

                    if (sys_config('queue_open', 0) == 0) {
                        $this->update($res->id, ['slider_image' => $s_image_down ? json_encode($s_image_down) : '', 'image' => $s_image_down[0]]);
                        $storeDescriptionServices->saveDescription((int)$res->id, $description, 'normal');
                    }
                }

                return $res->id;
            }
        });
    }

    /**
     * 根据商品ID获取产品类型标识
     * @param int $productId
     * @return string
     */
    public function getProductType(int $productId): string
    {
        return (string)($this->dao->value(['id' => $productId], 'product_type') ?: 'normal');
    }

    /**
     * 判断指定产品类型是否为虚拟商品
     * 通过 ProductTypeRegistry 咨询对应 Handler
     * @param string $typeKey
     * @return bool
     */
    protected function isVirtualProduct(string $typeKey): bool
    {
        try {
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            return $registry->get($typeKey)->isVirtual();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 按产品类型获取加购模式
     * 咨询对应 Handler 的 getCartStrategy()，返回 cart_mode
     * 静态缓存避免列表场景重复实例化 Handler
     * @param string $typeKey 产品类型标识
     * @return string cart_mode：cart（双按钮加购）| direct（单按钮直接下单）
     */
    protected function getCartModeByType(string $typeKey): string
    {
        static $cache = [];
        if (isset($cache[$typeKey])) {
            return $cache[$typeKey];
        }
        try {
            $registry = app()->make(ProductTypeRegistry::class);
            $mode = $registry->get($typeKey)->getCartStrategy()['cart_mode'] ?? 'cart';
        } catch (\Throwable $e) {
            $mode = 'cart';
        }
        $cache[$typeKey] = $mode;
        return $mode;
    }

    /**
     * 判断商品是否配置了自定义表单
     * 自定义表单商品需先填写表单才能下单，无法按普通购物车流程结算，因此不支持加入购物车
     * @param mixed $customForm 中台 custom_form 原值（JSON 字符串或已解码数组）
     * @return bool
     */
    protected function hasCustomForm($customForm): bool
    {
        if (empty($customForm)) {
            return false;
        }
        if (is_array($customForm)) {
            return !empty($customForm);
        }
        $decoded = json_decode((string)$customForm, true);
        if (is_array($decoded)) {
            return !empty($decoded);
        }
        return trim((string)$customForm) !== '';
    }

    /**
     * 获取产品类型展示名称
     * 通过 ProductTypeRegistry 咨询对应 Handler
     * @param string $typeKey
     * @return string
     */
    protected function getProductTypeName(string $typeKey): string
    {
        try {
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            return $registry->get($typeKey)->getTypeName();
        } catch (\Throwable $e) {
            return '普通商品';
        }
    }

    /**
     * 添加商品属性数据判断
     * @param array $attrList
     * @param array $valueList
     * @param int $productId
     * @param int $type
     * @param int $validate
     * @param bool $isNew
     * @return array
     */
    public function validateProductAttr(array $attrList, array $valueList, int $productId, $type = 0, int $validate = 1, bool $isNew = false)
    {
        $result = ['attr' => $attrList, 'value' => $valueList];
        $attrValueList = [];
        $attrNameList = [];
        foreach ($attrList as $index => $attr) {
            if (!isset($attr['value'])) {
                throw new AdminException('请输入规则名称');
            }
            $attr['value'] = trim($attr['value']);
            if (!isset($attr['value'])) {
                throw new AdminException('请输入规则名称');
            }
            if (!isset($attr['detail']) || !count($attr['detail'])) {
                throw new AdminException('请输入属性名称');
            }
            foreach ($attr['detail'] as $k => $attrValue) {
                if (is_string($attrValue) || is_int($attrValue) || is_float($attrValue)) {
                    $attrValueTemp = (string)$attrValue;
                    $attrValue = [];
                    $attrValue['value'] = $attrValueTemp;
                    $attrValue['pic'] = '';
                } elseif (is_array($attrValue)) {
                    $attrValue['value'] = trim($attrValue['value']);
                    $attrValue['pic'] = isset($attr['add_pic']) ? $attr['add_pic'] == 1 ? trim($attrValue['pic']) : '' : '';
                }
                if (empty($attrValue['value'])) {
                    throw new AdminException('请输入正确的属性');
                }
                $attr['detail'][$k] = $attrValue;
                $attrValueList[] = $attrValue;
            }
            $attrNameList[] = $attr['value'];
            $attrList[$index] = $attr;
        }
        $attrCount = count($attrList);
        foreach ($valueList as $index => $value) {
            if (!isset($value['detail']) || count($value['detail']) != $attrCount) {
                throw new AdminException('请填写正确的商品信息');
            }
            if (!isset($value['price']) || !is_numeric($value['price']) || floatval($value['price']) != $value['price']) {
                throw new AdminException('请填写正确的商品价格');
            }
            if ($validate && (!isset($value['stock']) || !is_numeric($value['stock']) || intval($value['stock']) != $value['stock'])) {
                throw new AdminException('请填写正确的商品库存');
            }
            if (!isset($value['cost']) || !is_numeric($value['cost']) || floatval($value['cost']) != $value['cost']) {
                throw new AdminException('请填写正确的商品成本价格');
            }
            if ($validate && (!isset($value['pic']) || empty($value['pic']))) {
                throw new AdminException('请设置规格图片');
            }
            foreach ($value['detail'] as $attrName => $attrValue) {
                //如果attrName 存在空格 则这个规格key 会出现两次
                unset($valueList[$index]['detail'][$attrName]);
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if (!in_array($attrName, $attrNameList, true)) {
                    throw new AdminException('{:name}规格不存在', ['name' => $attrName]);
                }
                if (!in_array($attrValue, array_column($attrValueList, 'value'), true)) {
                    throw new AdminException('{:name}属性不存在', ['name' => $attrValue]);
                }
                if (empty($attrName)) {
                    throw new AdminException('请输入正确的属性');
                }
                $valueList[$index]['detail'][$attrName] = $attrValue;
            }
        }
        $attrGroup = [];
        $valueGroup = [];
        foreach ($attrList as $k => $value) {
            $attrGroup[] = [
                'product_id' => $productId,
                'attr_name' => $value['value'],
                'attr_values' => $value['detail'],
                'type' => $type
            ];
        }
        // 已迁移到实物商品独立 SKU 表 eb_pt_normal_sku，按 suk（JSON）复用原 unique_code
        //通过事件代理查询 SKU unique_code 映射，避免 app 层直接引用 modules
        $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumnByProductId', 'args' => [$productId, 'unique_code', 'suk']]);
        $skuArray = proxy_event_result($results, []);
        foreach ($valueList as $k => $value) {
            $sku = implode(',', array_map(function ($key) use ($value) {
                return $value['detail'][$key];
            }, array_column($attrList, 'value')));
            $skuValue = [
                'product_id' => $productId,
                'suk' => $sku,
                'price' => $value['price'],
                'cost' => $value['cost'],
                'ot_price' => $value['ot_price'],
                'unique' => $isNew ? '' : $value['unique'] ?? '',
                'image' => $value['pic'],
                'bar_code' => $value['bar_code'] ?? '',
                'bar_code_number' => $value['bar_code_number'] ?? '',
                'weight' => $value['weight'] ?? 0,
                'volume' => $value['volume'] ?? 0,
                'brokerage' => $value['brokerage'] ?? 0,
                'brokerage_two' => $value['brokerage_two'] ?? 0,
                'type' => $type,
                'quota' => $value['quota'] ?? 0,
                'quota_show' => $value['quota'] ?? 0,
                'vip_price' => $value['vip_price'] ?? 0,
                'is_virtual' => $value['is_virtual'] ?? 0,
                'coupon_id' => $value['coupon_id'] ?? 0,
                'virtual_list' => $value['virtual_list'] ?? [],
                'disk_info' => $value['disk_info'] ?? '',
                'is_show' => $value['is_show'] ?? 1,
                'is_default_select' => $value['is_default_select'] ?? 0,
            ];

            if ($validate) {
                $skuValue['stock'] = $value['stock'];
            }
            $valueGroup[$sku] = $skuValue;
        }

        if (!count($attrGroup) || !count($valueGroup)) {
            throw new AdminException('请设置至少一个属性');
        }
        $result = ['attr' => $attrList, 'value' => $valueList];
        return compact('result', 'attrGroup', 'valueGroup');
    }

    /**
     * 放入回收站
     * @param int $id
     * @return string
     */
    public function del(int $id)
    {
        if (!$id) throw new AdminException('参数错误');
        $productInfo = $this->dao->get($id);
        if (!$productInfo) throw new AdminException('商品不存在');
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        if ($productInfo['is_del'] == 1) {
            $data['is_del'] = 0;
            $res = $this->dao->update($id, $data);
            $storeProductCateServices->update(['product_id' => $id], ['status' => 1]);
            if (!$res) throw new AdminException('恢复失败');
            return '恢复成功';
        } else {
            $data['is_del'] = 1;
            $data['is_show'] = 0;
            $res = $this->dao->update($id, $data);
            $storeProductCateServices->update(['product_id' => $id], ['status' => 0]);
            if (!$res) throw new AdminException('删除失败');

            // 商品删除事件
            event('Product', ['delete', [$id, $productInfo->toArray()]]);

            return '移动回收站成功';
        }
    }

    /**
     * 获取选择的商品列表
     * 从商品中台表（eb_product_center）查询，兼容旧 SKU 表回退
     * @param array $where
     * @return array
     */
    public function searchList(array $where, bool $isStock = false, $is_page = true)
    {
        // 解析 type 筛选
        $store_stock = sys_config('store_stock');
        $where['store_stock'] = $store_stock > 0 ? $store_stock : 2;

        // 使用商品中台表查询
        /** @var \app\dao\product\CenterProductDao $centerProductDao */
        $centerProductDao = app()->make(\app\dao\product\CenterProductDao::class);

        [$page, $limit] = $this->getPageValue($is_page);

        // 构建中台查询条件
        $searchWhere = $where;
        $count = $centerProductDao->count($searchWhere);
        $list = $centerProductDao->getSearchList($searchWhere, $page, $limit, ['*']);

        // 字段映射：中台字段 → 前端期望格式
        $cateIds = [];
        foreach ($list as &$item) {
            $item['store_name'] = $item['name'];
            $item['ot_price'] = $item['original_price'] ?? '0.00';
            $item['price'] = floatval($item['price'] ?? 0);
            // 中台表没有的旧字段设默认值
            $item['give_integral'] = 0;
            $item['vip_price'] = '0.00';
            $item['postage'] = '0.00';
            $item['cost'] = '0.00';
            $item['is_product_type'] = 1;
            $item['product_type'] = $item['product_type'] ?? 'normal';
            $item['type_name'] = $this->getProductTypeName($item['product_type']);
            $item['logistics'] = [];
            // 相对路径图片补全站点域名，与 getList 口径保持一致（image 主图、recommend_image 推荐图、slider_image 轮播图为数组）
            $item['image'] = set_file_url($item['image'] ?? '');
            $item['recommend_image'] = set_file_url($item['recommend_image'] ?? '');
            $item['slider_image'] = set_file_url($item['slider_image'] ?? []);
            // slider_image 由模型获取器自动解码
            if (isset($item['cate_id']) && $item['cate_id'] !== '') {
                $cateIds[] = $item['cate_id'];
            }
        }
        unset($item);

        // 补充分类名称
        if (!empty($cateIds)) {
            /** @var StoreCategoryServices $storeCategoryServices */
            $storeCategoryServices = app()->make(StoreCategoryServices::class);
            $cateList = $storeCategoryServices->getCateArray(implode(',', array_unique($cateIds)));
            foreach ($list as &$item) {
                $cateName = array_filter($cateList, function ($val) use ($item) {
                    return $val['id'] == $item['cate_id'];
                });
                $item['cate_name'] = !empty($cateName) ? implode(',', array_column($cateName, 'cate_name')) : '';
            }
            unset($item);
        } else {
            foreach ($list as &$item) {
                $item['cate_name'] = '';
            }
            unset($item);
        }

        // 补充商品规格（兼容旧 SKU 表 & 中台新建商品）
        foreach ($list as &$item) {
            $item['attrs'] = [];
            $rules = $this->getProductRules($item['id'], 0);
            if (!empty($rules) && isset($rules['attrs'])) {
                $key = 0;
                foreach ($rules['attrs'] as $items) {
                    $item['attrs'][$key] = $items;
                    $item['attrs'][$key]['suk'] = implode(',', $items['detail']);
                    $item['attrs'][$key]['product_price'] = $items['price'];
                    // 规格图片同样补全站点域名
                    $item['attrs'][$key]['pic'] = set_file_url($items['pic'] ?? '');
                    $key += 1;
                }
            }
        }
        unset($item);

        return compact('count', 'list');
    }

    /**
     * 后台获取商品列表展示
     * @param array $where
     * @return array
     */
    public function getProductList(array $where, bool $isStock = true, $is_page = true)
    {
        $prefix = Config::get('database.connections.' . Config::get('database.default') . '.prefix');
        if ($isStock) {
            $field = [
                '*',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'collect\') as collect',
                '(SELECT count(*) FROM `' . $prefix . 'store_product_relation` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = \'like\') as likes',
                '(SELECT count(*) FROM `' . $prefix . 'store_visit` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `product_type` = \'product\') as visitor',
            ];
        } else {
            $field = ['*'];
        }
        [$page, $limit] = $this->getPageValue($is_page);
        $list = $this->dao->getSearchList($where, $page, $limit, $field, ['description']);
        foreach ($list as &$product) {
            $product['product_price'] = $product['price'];
            $attrValue = $product['attrs'] ?? [];
            foreach ($attrValue as &$value) {
                $value['product_price'] = $value['price'];
            }
            $product['attrs'] = $attrValue;
        }
        $count = $this->dao->getCount($where);
        return compact('count', 'list');
    }

    /**
     * 获取商品规格
     * @param int $id
     * @param int $type
     * @return array
     */
    public function getProductRules(int $id, int $type = 0)
    {
        // 优先通过 ProductTypeHandler 读取实际 SKU 数据，
        // 适用范围：普通商品（type=0）、活动商品（type>0 如秒杀/砍价/拼团/积分等）
        // 通过读取 product_center 表的 product_type 字段确定真实产品类型（normal/virtual/cardkey），
        // 使用对应 ProductTypeHandler 获取 SKU 规格数据。
        try {
            $typeKey = (string)($this->dao->value(['id' => $id], 'product_type') ?: 'normal');
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($typeKey);
            $specs = $handler->getDetailSpecs($id);
            $rules = $this->convertHandlerSpecsToRules($specs, $type);
            if ($rules !== null) {
                return $rules;
            }
            // 门票/酒店等类型 getDetailSpecs 返回空（规格由详情页 display_sections 承载），
            // 但活动后台（积分/秒杀等）选择 SKU 需要实际票种/房型列表，回退到 getActivitySkuList
            if (method_exists($handler, 'getActivitySkuList')) {
                $activitySpecs = $handler->getActivitySkuList($id);
                $activityRules = $this->convertHandlerSpecsToRules($activitySpecs, $type);
                if ($activityRules !== null) {
                    return $activityRules;
                }
            }
        } catch (\Throwable $e) {
            // 异常时回退到后续方案
        }

        // 回退方案 1：从新 SKU 表（eb_pt_normal_sku）读取
        $newSkuData = $this->getProductRulesFromNewSkuTable($id, $type);
        if ($newSkuData !== null) {
            return $newSkuData;
        }

        // 回退方案 2：中台表默认单规格
        return $this->getProductRulesFallback($id, $type);
    }

    /**
     * 将 ProductTypeHandler 返回的规格结构转换为后台规则表格格式
     * 输出与 getProductRules 同构的 ['items' => ..., 'attrs' => ..., 'header' => ...]
     * @param array $specs Handler 返回的 ['productAttr' => ..., 'productValue' => ...]
     * @param int $type 活动类型，决定 header 列
     * @return array|null 有规格数据返回数组，否则返回 null 让调用方回退
     */
    protected function convertHandlerSpecsToRules(array $specs, int $type): ?array
    {
        $productAttr = $specs['productAttr'] ?? [];
        $productValue = $specs['productValue'] ?? [];

        // 无规格时交给 fallback 处理，保持默认单规格行为一致
        if (empty($productAttr) || empty($productValue)) {
            return null;
        }

        // 1. 构造 items（属性定义）
        $attr = [];
        $headerMap = [];
        $i = 1;
        foreach ($productAttr as $value) {
            $attrValues = $value['attr_values'] ?? [];
            if (is_string($attrValues)) {
                $attrValues = json_decode($attrValues, true) ?? [];
            }
            $attrName = $value['attr_name'] ?? '';
            $attr[] = [
                'value'       => $attrName,
                'detailValue' => '',
                'attrHidden'  => true,
                'detail'      => is_array($attrValues) ? $attrValues : [],
            ];
            $headerMap[$attrName] = 'value' . $i;
            $i++;
        }

        // 2. 构造 attrs（具体 SKU 值）
        $valueNew = [];
        $count = 0;
        foreach ($productValue as $suk => $sku) {
            $detail = $sku['detail'] ?? [];
            if (!is_array($detail) || empty($detail)) {
                $detail = [$attr[0]['value'] ?? '规格' => $suk];
            }

            $entry = [];
            $i = 1;
            foreach ($productAttr as $attrItem) {
                $attrName = $attrItem['attr_name'] ?? '';
                $entry['value' . $i] = $detail[$attrName] ?? '';
                $i++;
            }
            $entry['detail']          = $detail;
            $entry['pic']             = $sku['image'] ?? '';
            $entry['price']           = floatval($sku['price'] ?? 0);
            $entry['cost']            = floatval($sku['cost'] ?? 0);
            $entry['ot_price']        = floatval($sku['ot_price'] ?? 0);
            $entry['stock']           = intval($sku['stock'] ?? 0);
            $entry['quota']           = intval($sku['stock'] ?? 0);
            $entry['bar_code']        = $sku['bar_code'] ?? '';
            $entry['bar_code_number'] = $sku['bar_code_number'] ?? '';
            $entry['unique']          = $sku['unique'] ?? '';
            $entry['weight']          = floatval($sku['weight'] ?? 0);
            $entry['volume']          = floatval($sku['volume'] ?? 0);
            $entry['brokerage']       = floatval($sku['brokerage'] ?? 0);
            $entry['brokerage_two']   = floatval($sku['brokerage_two'] ?? 0);
            $entry['vip_price']       = floatval($sku['vip_price'] ?? 0);
            if ($type == 2) {
                $entry['min_price'] = 0;
            }
            if (in_array($type, [1, 2, 3])) {
                $entry['r_price'] = floatval($sku['price'] ?? 0);
            }
            $valueNew[$count] = $entry;
            $count++;
        }

        // 3. 构造 header（表头）
        $header = [];
        $i = 1;
        foreach ($productAttr as $attrItem) {
            $header[] = [
                'title'    => $attrItem['attr_name'] ?? '',
                'key'      => 'value' . $i,
                'align'    => 'center',
                'minWidth' => 80,
            ];
            $i++;
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        if ($type == 1) {
            $header[] = ['title' => '秒杀价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 2) {
            $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 3) {
            $header[] = ['title' => '拼团价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 4) {
            $header[] = ['title' => '兑换积分', 'slot' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 6) {
            $header[] = ['title' => '预售价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'slot' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编码', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '条形码', 'key' => 'bar_code_number', 'align' => 'center', 'minWidth' => 80];

        return ['items' => $attr, 'attrs' => $valueNew, 'header' => $header];
    }

    /**
     * 从新 SKU 表（eb_pt_normal_sku）读取产品多规格数据
     * 迁移至 product_center 后，正常商品的多规格数据存储在新表中。
     * 无论 type 参数，只要旧表无数据且新表有记录，均可作为回退来源。
     * @param int $id   商品中台 ID
     * @param int $type 活动类型（用于决定 header 格式，不限制查询表）
     * @return array|null 有数据返回与 getProductRules 同构的数组，否则返回 null
     */
    protected function getProductRulesFromNewSkuTable(int $id, int $type): ?array
    {
        try {
            //通过事件代理查询商品启用的 SKU 列表，避免 app 层直接引用 modules
            $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getShowListByProductId', 'args' => [$id]]);
            $skus = proxy_event_result($results, []);
        } catch (\Throwable $e) {
            // 表不存在或查询异常，静默降级
            return null;
        }

        if (empty($skus)) {
            return null;
        }

        // 1. 解析所有 suk JSON，提取属性定义
        $attrDefs = []; // attrName => [value => true]
        foreach ($skus as $sku) {
            $suk = json_decode($sku['suk'], true);
            if (!is_array($suk)) continue;
            foreach ($suk as $attrName => $attrValue) {
                $attrDefs[$attrName][$attrValue] = true;
            }
        }
        if (empty($attrDefs)) {
            return null;
        }

        // 2. 构建 items（属性定义），按属性名排序保持一致性
        ksort($attrDefs);
        $attr = [];
        $headerMap = []; // attrName => 'value1', 'value2', ...
        $i = 1;
        foreach ($attrDefs as $attrName => $values) {
            $attr[] = [
                'value'       => $attrName,
                'detailValue' => '',
                'attrHidden'  => true,
                'detail'      => array_keys($values),
            ];
            $headerMap[$attrName] = 'value' . $i;
            $i++;
        }

        // 3. 构建 attrs（具体 SKU 值）
        $valueNew = [];
        foreach ($skus as $sku) {
            $suk = json_decode($sku['suk'], true);
            if (!is_array($suk)) continue;

            $entry = [];
            $detail = [];
            $i = 1;
            foreach ($attrDefs as $attrName => $_) {
                $entry['value' . $i] = $suk[$attrName] ?? '';
                $detail[$attrName] = $suk[$attrName] ?? '';
                $i++;
            }
            $entry['detail']        = $detail;
            $entry['pic']           = $sku['image'] ?? '';
            $entry['price']         = floatval($sku['price'] ?? 0);
            $entry['cost']          = floatval($sku['cost'] ?? 0);
            $entry['ot_price']      = floatval($sku['original_price'] ?? 0);
            $entry['stock']         = intval($sku['stock'] ?? 0);
            $entry['quota']         = intval($sku['stock'] ?? 0);
            $entry['bar_code']      = $sku['bar_code'] ?? '';
            $entry['bar_code_number'] = $sku['bar_code_number'] ?? '';
            $entry['unique']        = $sku['unique_code'] ?? '';
            $entry['weight']        = floatval($sku['weight'] ?? 0);
            $entry['volume']        = floatval($sku['volume'] ?? 0);
            $entry['brokerage']     = floatval($sku['brokerage'] ?? 0);
            $entry['brokerage_two'] = floatval($sku['brokerage_two'] ?? 0);
            $entry['vip_price']     = floatval($sku['vip_price'] ?? 0);
            $valueNew[] = $entry;
        }

        // 4. 构建 header（表头），根据 $type 决定展示哪些列
        $header = [];
        $i = 1;
        foreach ($attrDefs as $attrName => $_) {
            $header[] = [
                'title'    => $attrName,
                'key'      => 'value' . $i,
                'align'    => 'center',
                'minWidth' => 80,
            ];
            $i++;
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        if ($type == 1) {
            $header[] = ['title' => '秒杀价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 2) {
            $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 3) {
            $header[] = ['title' => '拼团价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 4) {
            $header[] = ['title' => '兑换积分', 'slot' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 6) {
            $header[] = ['title' => '预售价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'slot' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编码', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '条形码', 'key' => 'bar_code_number', 'align' => 'center', 'minWidth' => 80];

        return ['items' => $attr, 'attrs' => $valueNew, 'header' => $header];
    }

    /**
     * getProductRules 回退方案
     * 旧 SKU 表无数据时，从商品中台表（eb_product_center）读取基本信息，
     * 构建一条"默认规格"供前端作为模板。
     * @param int $id   商品中台 ID
     * @param int $type 活动类型（同 getProductRules）
     * @return array
     */
    protected function getProductRulesFallback(int $id, int $type = 0): array
    {
        /** @var \app\dao\product\CenterProductDao $centerProductDao */
        $centerProductDao = app()->make(\app\dao\product\CenterProductDao::class);
        $product = $centerProductDao->get($id, ['id', 'price', 'stock', 'original_price as ot_price', 'image']);
        if (!$product) {
            return [];
        }

        // 构造单条"默认"规格的 attr 定义
        $attr = [
            [
                'value'       => '规格',
                'detailValue' => '',
                'attrHidden'  => true,
                'detail'      => ['默认'],
            ],
        ];

        // 构造默认 SKU 值
        $valueNew = [
            [
                'value1'          => '默认',
                'detail'          => ['规格' => '默认'],
                'pic'             => $product['image'] ?? '',
                'price'           => floatval($product['price'] ?? 0),
                'cost'            => 0.0,
                'ot_price'        => floatval($product['ot_price'] ?? 0),
                'stock'           => intval($product['stock'] ?? 0),
                'quota'           => intval($product['stock'] ?? 0),
                'bar_code'        => '',
                'bar_code_number' => '',
                'unique'          => '',
                'weight'          => 0.0,
                'volume'          => 0.0,
                'brokerage'       => 0.0,
                'brokerage_two'   => 0.0,
                'vip_price'       => 0.0,
            ],
        ];

        // 构造表头（与 getProductRules 保持一致）
        $header = [
            ['title' => '规格', 'key' => 'value1', 'align' => 'center', 'minWidth' => 80],
        ];

        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        if ($type == 1) {
            $header[] = ['title' => '秒杀价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 2) {
            $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 3) {
            $header[] = ['title' => '拼团价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 4) {
            $header[] = ['title' => '兑换积分', 'slot' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 6) {
            $header[] = ['title' => '预售价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '划线价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'slot' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编码', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '条形码', 'key' => 'bar_code_number', 'align' => 'center', 'minWidth' => 80];

        return ['items' => $attr, 'attrs' => $valueNew, 'header' => $header];
    }

    /**
     * 更新实物商品 SKU 图片
     * 商品采集复制时异步下载规格图片后写入 eb_pt_normal_sku
     * @param int $skuId SKU 主键
     * @param string $image 图片地址
     * @return bool
     */
    public function updateNormalSkuImage(int $skuId, string $image): bool
    {
        try {
            //通过事件代理更新 SKU 图片，避免 app 层直接引用 modules
            $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'updateImage', 'args' => [$skuId, $image]]);
            return proxy_event_result($results, false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 获取实物商品 SKU 列表
     * 供移动管理端读取规格库存
     * @param int $productId
     * @return array
     */
    public function getNormalProductSkuList(int $productId): array
    {
        try {
            //通过事件代理查询商品启用的 SKU 列表，避免 app 层直接引用 modules
            $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getShowListByProductId', 'args' => [$productId]]);
            return proxy_event_result($results, []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 批量更新实物商品 SKU 并同步中台主表
     * 移动管理端修改规格价格/库存时调用
     * @param int $productId
     * @param array $attrValues 以 unique_code 为键的规格数据
     * @return bool
     */
    public function batchUpdateNormalProductSku(int $productId, array $attrValues): bool
    {
        try {
            //通过事件代理查询 SKU 列表（以 unique_code 为键），避免 app 层直接引用 modules
            $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumnByProductId', 'args' => [$productId, '*', 'unique_code']]);
            $skuList = proxy_event_result($results, []);
        } catch (\Throwable $e) {
            return false;
        }

        $productStock = $productPrice = $productOtPrice = $productCost = 0;
        foreach ($skuList as $unique => $sku) {
            $attr = $attrValues[$unique] ?? [];
            $update = [
                'price'    => isset($attr['price']) ? (float)$attr['price'] : (float)$sku['price'],
                'stock'    => isset($attr['stock']) ? (int)$attr['stock'] : (int)$sku['stock'],
                'cost'     => isset($attr['cost']) ? (float)$attr['cost'] : (float)$sku['cost'],
                'ot_price' => isset($attr['ot_price']) ? (float)$attr['ot_price'] : (float)$sku['ot_price'],
            ];
            if ($attr) {
                //通过事件代理更新 SKU 数据，避免 app 层直接引用 modules
                event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'update', 'args' => [(int)$sku['id'], $update]]);
            }
            $productStock = bcadd((string)$productStock, (string)$update['stock'], 0);
            $productPrice = max($productPrice, $update['price']);
            $productOtPrice = max($productOtPrice, $update['ot_price']);
            $productCost = max($productCost, $update['cost']);
        }

        return $this->update($productId, [
            'stock' => $productStock,
            'price' => $productPrice,
            'ot_price' => $productOtPrice,
            'cost' => $productCost,
        ]) !== false;
    }

    /**
     * 检查商品是否有活动
     * @param  $id
     * @return bool
     */
    public function checkActivity($id = 0, $return = false)
    {
        if ($id) {
            // 通过活动注册中心检查商品是否参与营销类活动（排除普通购买等基础交易模式，避免误拦所有商品），
            // 活动名称由各交易模式 Handler 的 getLabel() 动态提供，禁止硬编码模式映射表
            $activityRegistry = app()->make(TradeModeRegistry::class);
            $marketingKeys = array_flip($activityRegistry->getMarketingKeys());
            $labels = array_column($activityRegistry->allInfo(), 'label', 'key');
            $activities = $activityRegistry->checkProductActivities([$id]);

            if (!empty($activities[$id])) {
                foreach ($activities[$id] as $type => $exist) {
                    if ($exist && isset($marketingKeys[$type])) {
                        $typeName = $labels[$type] ?? $type;

                        if ($return) {
                            return false;
                        } else {
                            throw new AdminException("商品参与{$typeName}活动开启，无法进行此操作");
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * 保存
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->dao->save($data);
    }

    /**
     * 前台获取商品列表
     * @param array $where
     * @param int $uid
     * @return array|array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsList(array $where, int $uid)
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;
        $where['star'] = 1;
        $ifKeyword = isset($where['store_name']) && $where['store_name'];
        if ($ifKeyword) {
            app()->make(UserSearchServices::class)->saveUserSearch($uid, $where['store_name'], [$where['store_name']], []);
        }
        [$page, $limit] = $this->getPageValue();
        $where['vip_user'] = $uid ? app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level') : 0;
        $list = $this->dao->getSearchList($where, $page, $limit, ['id,name as store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(virtual_sales, 0) as sales,price,stock,original_price as ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price,presale,custom_form,product_type,min_qty,label_list']);
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $vipStatus = $memberCardService->isOpenMemberCard('vip_price');

        // 提取所有 label_list 并过滤空值
        $labelLists = array_map(function ($item) {
            return $item['label_list'];
        }, $list);

        // 将 label_list 转换为数组，过滤空值，并去重
        $uniqueLabels = array_unique(array_reduce($labelLists, function ($carry, $labels) {
            // 将 label_list 按逗号分割成数组
            $labelsArray = explode(',', $labels);
            // 过滤掉空值
            $labelsArray = array_filter($labelsArray, function ($label) {
                return !empty(trim($label));
            });
            return array_merge($carry, $labelsArray);
        }, []));

        $labelList = app()->make(StoreProductLabelServices::class)->labelListByIds($uniqueLabels);
        // 使用 id 作为键
        $labelList = array_column($labelList, null, 'id');

        foreach ($list as &$item) {
            if (!$this->vipIsOpen(!!$item['is_vip'], $vipStatus)) {
                $item['vip_price'] = 0;
            }
            // 自定义表单商品不支持加入购物车（前端据此点击直接进详情）
            $item['cart_button'] = $this->hasCustomForm($item['custom_form'] ?? '') || $this->getCartModeByType($item['product_type'] ?? 'normal') === 'direct' ? 0 : 1;
            if (count($item['star'])) {
                $item['star'] = bcdiv((string)array_sum(array_column($item['star'], 'product_score')), (string)count($item['star']), 1);
            } else {
                $item['star'] = '5.0';
            }
            $item['label_list'] = $item['label_list'] !== '' ? array_map(function ($labelId) use ($labelList) {
                return $labelList[$labelId] ?? [];
            }, explode(',', $item['label_list'])) : [];
        }
        $list = $this->getActivityList($list);
        $list = $this->getProduceOtherList($list, $uid, !!$where['type']);
        return $list;
    }

    /**
     * 获取某些模板所需得购物车数量
     * @param array $list
     * @param int $uid
     * @return array
     */
    public function getProduceOtherList(array $list, int $uid, bool $type = true)
    {
        if (!$type || !$list) {
            return $list;
        }
        $productIds = array_column($list, 'id');
        if ($productIds) {
            if ($uid) {
                /** @var StoreCartServices $cartServices */
                $cartServices = app()->make(StoreCartServices::class);
                $cartNumList = $cartServices->productIdByCartNum($productIds, $uid);
                $data = [];
                foreach ($cartNumList as $item) {
                    $data[$item['product_id']][] = $item['cart_num'];
                }
                $newNumList = [];
                foreach ($data as $key => $item) {
                    $newNumList[$key] = array_sum($item);
                }
                $cartNumList = $newNumList;
            } else {
                $cartNumList = [];
            }
            foreach ($list as &$item) {
                // 多规格标记直接由中台表 spec_type 决定，不再查询 eb_store_product_attr_value
                $item['is_att'] = !empty($item['spec_type']);
                $item['cart_num'] = $cartNumList[$item['id']] ?? 0;
            }
        }
        return $list;
    }

    /**
     * 获取商品活动标签
     * @param array $list
     * @param array $productIds
     * @return array
     */
    public function getActivityList(array $list, bool $status = true, $seckillIdsList = false, $pinkIdsList = false, $bargrainIdsList = false)
    {
        if (!$list) return [];
        if ($status) {
            $productIds = array_column($list, 'id');
        } else {
            $productIds = [$list['id']];
            $list = [$list];
        }

        // 通过活动注册中心获取活动ID列表
        $activityRegistry = app()->make(TradeModeRegistry::class);
        if ($seckillIdsList === false) {
            $seckillIdsList = $activityRegistry->get('seckill')->getActivityIdsList($productIds, ['id', 'time_id', 'product_id']);
        }
        if ($pinkIdsList === false) {
            $pinkIdsList = $activityRegistry->get('combination')->getActivityIdsList($productIds, ['id']);
        }
        if ($bargrainIdsList === false) {
            $bargrainIdsList = $activityRegistry->get('bargain')->getActivityIdsList($productIds, ['id']);
        }

        /** @var StoreCouponIssueServices $couponIssueServices */
        $couponIssueServices = app()->make(StoreCouponIssueServices::class);

        foreach ($list as &$item) {
            $seckillId = array_filter($seckillIdsList, function ($val) use ($item) {
                if ($val['product_id'] === $item['id']) {
                    return $val;
                }
            });
            $item['activity'] = $this->activity($item['activity'], $item['id'], $pinkIdsList[$item['id']] ?? 0, $seckillId, $bargrainIdsList[$item['id']] ?? 0, $status);
            $item['checkCoupon'] = $couponIssueServices->checkProductCoupon($item['id']);
        }
        if ($status) {
            return $list;
        } else {
            return $list[0]['activity'];
        }
    }

    /**
     * 获取商品在此时段活动优先类型
     * @param string $activity
     * @param int $id
     * @param int $combinationId
     * @param array $seckillId
     * @param int $bargainId
     * @param bool $status
     * @return array
     */
    public function activity(string $activity, int $id, int $combinationId, array $seckillId, int $bargainId, bool $status = true)
    {
        if (!$activity) {
            $activity = '0,1,2,3'; //如果老商品没有活动顺序，默认活动顺序，秒杀-砍价-拼团
        }
        $activity = explode(',', $activity);
        if ($activity[0] == 0 && $status) return [];
        $activityId = [];
        $time_id = 0;
        if ($seckillId) {
            foreach ($seckillId as $v) {
                // 按 time_id 查询秒杀时间段数据（直接调 app 层服务，无需全局函数封装）
                try {
                    $_ids = is_string($v['time_id']) ? explode(',', $v['time_id']) : $v['time_id'];
                    $_data = app()->make(SystemGroupDataServices::class)->getGroupDataColumn($_ids);
                    $timeInfo = is_object($_data) ? $_data->toArray() : $_data;
                } catch (\Exception $e) {
                    $timeInfo = [];
                }
                foreach ($timeInfo as $time) {
                    $value = json_decode($time['value'], true);
                    if (date('H') >= $value['time']['value'] && date('H') < ($value['time']['value'] + $value['continued']['value'])) {
                        $activityId[1] = $v['id'];
                        $time_id = $time['id'];
                        break;
                    }
                }
            }
        }
        if ($bargainId) $activityId[2] = $bargainId;
        if ($combinationId) $activityId[3] = $combinationId;
        $data = [];
        foreach ($activity as $k => $v) {
            if (array_key_exists($v, $activityId)) {
                if ($status) {
                    $data['type'] = $v;
                    $data['id'] = $activityId[$v];
                    if ($v == 1) $data['time_id'] = $time_id;
                    break;
                } else {
                    if ($v != 0) {
                        $arr['type'] = $v;
                        $arr['id'] = $activityId[$v];
                        if ($v == 1) $arr['time_id'] = $time_id;
                        $data[] = $arr;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 获取热门商品
     * @param array $where
     * @param int $num
     * @return array|array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProducts(array $where, int $num = 0)
    {
        [$page, $limit] = $this->getPageValue();
        if ($num) {
            $page = 1;
            $limit = $num;
        }
        $list = $this->dao->getSearchList($where, $page, $limit, ['id,name as store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(virtual_sales, 0) as sales,price,stock,unit_name,presale,is_vip,vip_price']);
        // eb_product_center 无 activity 列，补默认值兼容 getActivityList
        foreach ($list as &$item) {
            $item['activity'] = $item['activity'] ?? '';
        }
        $list = $this->getActivityList($list);
        return $list;
    }

    /**
     * 获取商品详情
     * @param Request $request
     * @param int $id
     * @param int $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productDetail(Request $request, int $id, int $type)
    {
        $uid = (int)$request->uid();
        $data['uid'] = $uid;

        $storeInfo = $this->dao->getOne(['id' => $id, 'is_show' => 1, 'is_del' => 0], '*', ['description']);
        if (!$storeInfo) {
            throw new AdminException('商品不存在');
        } else {
            $storeInfo = $storeInfo->toArray();
        }
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['video_link'] = empty($storeInfo['video_link']) ? '' : (strpos($storeInfo['video_link'], 'http') === false ? (sys_config('site_url') . $storeInfo['video_link']) : $storeInfo['video_link']);
        $storeInfo['fsales'] = ($storeInfo['virtual_sales'] ?? 0) + $storeInfo['sales'];
        $storeInfo['custom_form'] = json_decode($storeInfo['custom_form'] ?? '', true) ?? [];
        $storeInfo['params_list'] = json_decode($storeInfo['params_list'] ?? '', true) ?? [];
        if ($storeInfo['label_list'] !== '') {
            $labelIds = explode(',', $storeInfo['label_list']);
            $storeInfo['label_list'] = [];
            $labelList = app()->make(StoreProductLabelServices::class)->labelListByIds($labelIds);
            foreach ($labelIds as $labelId) {
                foreach ($labelList as $label) {
                    if ($labelId == $label['id']) {
                        $storeInfo['label_list'][] = $label;
                    }
                }
            }
        } else {
            $storeInfo['label_list'] = [];
        }
        if ($storeInfo['protection_list'] !== '') {
            $storeInfo['protection_list'] = app()->make(StoreProductProtectionServices::class)->protectionListByIds(explode(',', $storeInfo['protection_list']));
        } else {
            $storeInfo['protection_list'] = [];
        }
        $storeInfo['slider_image'] = set_file_url($storeInfo['slider_image'], $siteUrl);

        if (sys_config('share_qrcode', 0) && request()->isWechat()) {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            $storeInfo['wechat_code'] = $qrcodeService->getTemporaryQrcode('product-' . $id, $uid)->url;
        } else {
            $storeInfo['wechat_code'] = '';
        }

        /** @var StoreProductRelationServices $storeProductRelationServices */
        $storeProductRelationServices = app()->make(StoreProductRelationServices::class);
        $storeInfo['userCollect'] = $storeProductRelationServices->isProductRelation(['uid' => $uid, 'product_id' => $id, 'type' => 'collect', 'category' => 'product']);
        $storeInfo['userLike'] = false;
        $storeInfo['small_image'] = $storeInfo['image'];
        $storeInfo = get_thumb_water($storeInfo, 'small', ['small_image']);

        //预售相关
        if ($storeInfo['presale']) {
            if ($storeInfo['presale_start_time'] > time()) {
                $storeInfo['presale_pay_status'] = 1;
            } elseif ($storeInfo['presale_start_time'] <= time() && $storeInfo['presale_end_time'] > time()) {
                $storeInfo['presale_pay_status'] = 2;
            } elseif ($storeInfo['presale_end_time'] < time()) {
                $storeInfo['presale_pay_status'] = 3;
            } else {
                $storeInfo['presale_pay_status'] = 0;
            }
        } else {
            $storeInfo['presale_pay_status'] = 0;
        }
        $storeInfo['presale_start_time'] = date('Y-m-d H:i', $storeInfo['presale_start_time']);
        $storeInfo['presale_end_time'] = date('Y-m-d H:i', $storeInfo['presale_end_time']);

        // 已迁移到 ProductTypeHandler::getDetailSpecs，不再查询 eb_store_product_attr_result
        $productType = $this->getProductType($id);
        $handler = app()->make(ProductTypeRegistry::class)->get($productType);

        // 自定义表单商品不支持加入购物车，强制 direct 单按钮模式
        $cartStrategy = $handler->getCartStrategy();
        if ($this->hasCustomForm($storeInfo['custom_form'] ?? [])) {
            $cartStrategy['cart_mode'] = 'direct';
        }
        $storeInfo['cart_strategy'] = $cartStrategy;

        // cart_button 由 cart_strategy.cart_mode 决定：direct 模式不展示加入购物车按钮
        // 预售商品在模板中走独立分支，不影响此处判断
        $storeInfo['cart_button'] = ($cartStrategy['cart_mode'] ?? 'cart') === 'direct' ? 0 : 1;

        $specs = $handler->getDetailSpecs($id);
        $productAttr = $specs['productAttr'] ?? [];
        $productValue = $specs['productValue'] ?? [];
        $attrPics = [];
        foreach ($productAttr as $attrValues) {
            foreach ($attrValues['attr_value'] as $picArr) {
                if (isset($picArr['pic']) && $picArr['pic'] !== '') {
                    $attrPics[] = $picArr['pic'];
                }
            }
        }
        $storeInfo['attrPics'] = $attrPics;
        $attrValue = $productValue;
        if (!$storeInfo['spec_type']) {
            $productAttr = [];
            $productValue = [];
            $data['spec_unique'] = $attrValue['默认']['unique'];
        } else {
            foreach ($productValue as &$sku) {
                if ($sku['is_show'] == 0) {
                    $storeInfo['stock'] = $storeInfo['stock'] - $sku['stock'];
                    $sku['stock'] = 0;
                }
            }
            $data['spec_unique'] = '';
        }
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['storeInfo'] = $storeInfo;

        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $data['svip_open'] = $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
        $data['svip_price_open'] = (int)sys_config('member_price_status');
        $data['storeInfo']['svip_economize_price'] = bcsub((string)$data['storeInfo']['price'], (string)$data['storeInfo']['vip_price'], 2);
        if (!$this->vipIsOpen(!!$storeInfo['is_vip'], $vipStatus)) {
            $data['storeInfo']['vip_price'] = 0;
        }
        $data['priceName'] = 0;
        if ($uid && (int)sys_config('brokerage_window_switch', 1)) {
            $user = $request->user();
            $agentProfile = event('DistributionServiceProxy', [
            'service' => 'DistributionUserServices',
            'action' => 'profile',
        ]);
            $isPromoter = (int)($agentProfile[0]['is_promoter'] ?? $user->is_promoter);
            if (!$isPromoter) {
                /** @var OrderCenterDao $storeOrderService */
                $storeOrderService = app()->make(OrderCenterDao::class);
                $price = $storeOrderService->sum(['pay_status' => 1, 'refund_status' => 0, 'uid' => $uid], 'pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    $handled = event('DistributionServiceProxy', [
                        'service' => 'DistributionUserServices',
                        'action' => 'saveProfile',
                        'args' => [(int)$uid, ['is_promoter' => 1, 'spread_open' => 1]],
                    ]);
                    if (!is_array($handled) || ($handled[0] ?? null) === null) {
                        app()->make(UserServices::class)->update($uid, ['is_promoter' => 1]);
                    }
                }
            }
            $data['priceName'] = $this->getPacketPrice($storeInfo, $attrValue, $uid);
            //用户访问事件
            event('UserVisit', [$uid, $id, 'product', $storeInfo['cate_id'], 'view']);
        }

        /** @var StoreProductReplyServices $storeProductReplyService */
        $storeProductReplyService = app()->make(StoreProductReplyServices::class);
        $data['reply'] = get_thumb_water($storeProductReplyService->getRecProductReply($id), 'big', ['pics']);
        [$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData($id);
        $data['replyChance'] = $replyChance;
        $data['replyCount'] = $replyCount;
        $data['mer_id'] = 0;
        $vip_user = $uid ? app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level') : 0;
        // 商品详情关联推荐仅使用 recommend_list，is_good 字段已删除
        if ($storeInfo['recommend_list'] !== '') {
            $recommend_list = explode(',', $storeInfo['recommend_list']);
            $data['good_list'] = $this->getProducts(['ids' => $recommend_list, 'is_del' => 0, 'is_show' => 1], 12);
        } else {
            $data['good_list'] = [];
        }
        $data['mapKey'] = sys_config('tengxun_map_key');
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0; //门店自提是否开启
        $data['activity'] = $this->getActivityList($data['storeInfo'], false);
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        $data['coupons'] = $couponService->getIssueCouponList($uid, ['product_id' => $id, 'type' => -1])['list'];
        $data['routine_contact_type'] = sys_config('routine_contact_type', 0);

        // 商品访问事件（处理访问记录）
        event('Product', ['visit', [$id, $uid, date('Y-m-d H:i:s'), &$data]]);

        return $data;
    }

    /**
     * 是否开启vip
     * @param bool $vip
     * @return bool
     */
    public function vipIsOpen(bool $vip = false, $vipStatus = -1)
    {
        if ($vipStatus == -1) {
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
        }
        return $vipStatus && sys_config('member_card_status') && $vip && sys_config('member_price_status', 1);
    }

    /**
     * 获取商品分销佣金最低和最高
     * @param $storeInfo
     * @param $productValue
     * @param int $uid
     * @return int|string
     */
    public function getPacketPrice($storeInfo, $productValue, int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        if (!$userServices->checkUserPromoter($uid)) {
            return 0;
        }
        if (!count($productValue)) {
            return 0;
        }
        if (!sys_config('brokerage_func_status')) {
            return 0;
        }
        // 未开启分销时不计算返佣
        if (empty($storeInfo['is_sub'])) {
            return 0;
        }
        $store_brokerage_ratio = sys_config('store_brokerage_ratio');
        $store_brokerage_ratio = bcdiv((string)$store_brokerage_ratio, '100', 2);
        $maxPrice = max(array_column($productValue, 'price'));
        $minPrice = min(array_column($productValue, 'price'));
        $maxPrice = bcmul((string)$store_brokerage_ratio, (string)$maxPrice, 0);
        $minPrice = bcmul((string)$store_brokerage_ratio, (string)$minPrice, 0);
        if ($minPrice == 0 && $maxPrice == 0) {
            $priceName = 0;
        } else if ($minPrice == 0 && $maxPrice)
            $priceName = $maxPrice;
        else if ($maxPrice == 0 && $minPrice)
            $priceName = $minPrice;
        else if ($maxPrice == $minPrice && $minPrice)
            $priceName = $maxPrice;
        else
            $priceName = $minPrice . '~' . $maxPrice;
        return strlen(trim($priceName)) ? $priceName : 0;
    }

    /**
     * 设置会员价格
     * @param $list
     * @param int $uid
     * @param $userInfo
     * @param $vipStatus
     * @param int $discount
     * @param float $vipPrice
     * @param bool $is_show
     * @return array|float|int|mixed|string
     */
    public function setLevelPriceV2($price, int $uid, $userInfo, $vipStatus, $discount = 0, $vipPrice = 0.00, $is_vip = 0, $is_show = false)
    {
        if ($uid) {
            if (!$userInfo) {
                /** @var UserServices $user */
                $user = app()->make(UserServices::class);
                $userInfo = $user->getUserInfo($uid);
            }
            if ($discount === 0) {
                /** @var SystemUserLevelServices $systemLevel */
                $systemLevel = app()->make(SystemUserLevelServices::class);
                $discount = $systemLevel->value(['id' => $userInfo['level'], 'is_del' => 0, 'is_show' => 1], 'discount');
            }
        } else {
            //没登录
            $discount = 100;
            $userInfo = [];
        }
        $discount = bcdiv((string)$discount, '100', 2);
        if (!$vipStatus) $is_vip = 0;
        //is_vip == 0表示会员价格不启用，展示为零
        if ($is_vip == 0) $vipPrice = 0;

        $noPayVipPrice = ($discount && sys_config('member_func_status')) ? bcmul((string)$discount, (string)$price, 2) : $price;
        $vipPrice = ($vipPrice < $noPayVipPrice && $vipPrice > 0) ? $vipPrice : $noPayVipPrice;
        //如果$isSingle==true 返回优惠后的总金额，否则返回优惠的金额
        if ($vipStatus && $is_vip == 1 && (!$is_show || ($is_show && $userInfo && isset($userInfo['is_money_level']) && $userInfo['is_money_level'] > 0))) {
            return [(float)$vipPrice, (float)bcsub((string)$price, (string)$vipPrice, 2)];
        } else {
            return [(float)$noPayVipPrice, (float)bcsub((string)$price, (string)$noPayVipPrice, 2)];
        }
    }

    /**
     * 设置会员价格
     * @param $list
     * @param int $uid
     * @param $userInfo
     * @param $vipStatus
     * @param bool $isSingle
     * @param int $discount
     * @param float $vipPrice
     * @param bool $is_show
     * @return array|float|int|mixed|string
     */
    public function setLevelPrice($price, int $uid, $userInfo, $vipStatus, $discount = 0, $vipPrice = 0.00, $is_vip = 0, $is_show = false)
    {
        if (!(float)$price) return [0, 0, 'level'];
        if (!$vipStatus) $is_vip = 0;
        //已登录
        if ($uid) {
            if (!$userInfo) {
                /** @var UserServices $user */
                $user = app()->make(UserServices::class);
                $userInfo = $user->getUserInfo($uid);
            }
            if ($discount === 0) {
                $discount = 100;
                if (sys_config('member_func_status', 1)) {
                    /** @var SystemUserLevelServices $systemLevel */
                    $systemLevel = app()->make(SystemUserLevelServices::class);
                    $discount = $systemLevel->value(['id' => $userInfo['level'], 'is_del' => 0, 'is_show' => 1], 'discount') ?: 100;
                }
            }
        } else {
            //没登录
            $discount = 100;
        }
        $discount = bcdiv((string)$discount, '100', 2);
        //执行减去会员优惠金额
        [$truePrice, $vip_truePrice, $type] = $this->isPayLevelPrice($uid, $userInfo, $vipStatus, $price, $discount, $vipPrice, $is_vip, $is_show);
        //返回优惠后的总金额
        $truePrice = $truePrice < 0.01 ? 0.01 : $truePrice;
        //优惠的金额
        $vip_truePrice = $vip_truePrice == $price ? bcsub((string)$vip_truePrice, '0.01', 2) : $vip_truePrice;
        return [(float)$truePrice, (float)$vip_truePrice, $type];
    }


    /**商品列表
     * @param array $where
     * @param $limit
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductLimit(array $where, $limit, $field)
    {
        return $this->dao->getProductLimit($where, $limit, $field);
    }

    /**通过条件获取商品列表
     * @param $where
     * @param $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductListByWhere($where, $field)
    {
        return $this->dao->getProductListByWhere($where, $field);
    }

    /**
     * 根据指定id获取商品列表
     * @param array $ids
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductColumn(array $ids, string $field = '')
    {
        $productData = [];
        $productInfoField = 'id,image,price,original_price as ot_price,vip_price,postage,give_integral,sales,stock,store_name,unit_name,is_show,is_del,is_postage,cost,is_sub,is_vip,temp_id';
        if (!empty($ids)) {
            $productAll = $this->dao->idByProductList($ids, $field ?: $productInfoField);
            if (!empty($productAll))
                $productData = array_combine(array_column($productAll, 'id'), $productAll);
        }
        return $productData;
    }

    /**
     * 商品是否存在
     * @param int $productId
     * @param string $field
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/15
     */
    public function isValidProduct(int $productId, string $field = '*')
    {
        return $this->dao->getOne(['id' => $productId, 'is_del' => 0, 'is_show' => 1], $field);
    }

    /**
     * 获取商品库存
     * @param int $productId
     * @param string $uniqueId
     * @return int|mixed
     */
    public function getProductStock(int $productId, string $uniqueId = '')
    {
        // 空 unique 直接返回中台表总库存
        if ($uniqueId === '') {
            return (int)($this->dao->value(['id' => $productId], 'stock') ?: 0);
        }

        // 通过 ProductTypeHandler 获取对应 SKU 库存
        try {
            $typeKey = $this->getProductType($productId);
            /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
            $registry = app()->make(\crmeb\trade\product\ProductTypeRegistry::class);
            $handler = $registry->get($typeKey);
            $skuInfo = $handler->getSkuInfo($productId, $uniqueId);
            if (!empty($skuInfo)) {
                return (int)($skuInfo['stock'] ?? 0);
            }
        } catch (\Throwable $e) {
            // 异常时回退到中台表总库存
        }

        return (int)($this->dao->value(['id' => $productId], 'stock') ?: 0);
    }

    /**
     * 减库存,加销量
     * 已迁移到 ProductTypeHandler，不再操作 eb_store_product_attr_value
     * @param $num
     * @param $productId
     * @param string $unique
     * @return bool
     */
    public function decProductStock(int $num, int $productId, string $unique = '')
    {
        $res = true;
        if ($unique) {
            $productType = $this->getProductType($productId);
            $handler = app()->make(ProductTypeRegistry::class)->get($productType);
            $res = $res && $handler->decStock($productId, $unique, $num);
        }
        return $res && $this->dao->decStockIncSales(['id' => $productId], $num);
    }

    /**
     * 加销量减库存
     * 已迁移到 ProductTypeHandler，不再操作 eb_store_product_attr_value
     * @param int $num
     * @param int $productId
     * @param string $unique
     * @return bool
     */
    public function incProductStock(int $num, int $productId, string $unique = '')
    {
        $res = true;
        if ($unique) {
            $productType = $this->getProductType($productId);
            $handler = app()->make(ProductTypeRegistry::class)->get($productType);
            $res = $res && $handler->incStock($productId, $unique, $num);
        }
        $res = $res && $this->dao->incStockDecSales(['id' => $productId], $num);
        return $res;
    }

    /**
     * 库存预警发送消息
     * @param int $productId
     */
    public function workSendStock(int $productId)
    {
        $stock = $this->dao->value(['id' => $productId], 'stock');
        $replenishment_num = sys_config('store_stock') ?? 0; //库存预警界限
        if ($replenishment_num >= $stock) {
            try {
                \crmeb\services\workerman\ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * 获取首页推荐商品（多个）
     * @param int $uid
     * @param array $fields
     * @param bool $is_num
     * @param string $type
     * @return array[]
     */
    public function getRecommendProductArr(int $uid, array $fields, bool $is_num = true, string $type = 'mid')
    {
        // is_best/is_new/is_benefit/is_hot/is_good 推荐位字段已删除，首页推荐位功能下线
        return [[], [], [], [], []];
    }

    /**
     * 单个获取首页推荐商品
     * @param int $uid
     * @param $field
     * @param int $num
     * @param string $type
     * @return array|array[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecommendProduct(int $uid, $field, int $num = 0, string $type = 'mid')
    {
        // is_hot/is_new/is_benefit/is_best/is_good 推荐位字段已删除，直接返回空数组
        if (is_string($field) && in_array($field, ['is_hot', 'is_new', 'is_benefit', 'is_best', 'is_good'], true)) {
            return [];
        }

        // 会员商品推荐保留（is_vip 字段未删除）
        $vip_user = $uid ? app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level') : 0;
        $where = ['is_show' => 1, 'is_del' => 0, 'vip_user' => $vip_user];
        if (is_array($field)) {
            $where = array_merge($where, $field);
        } elseif ($field === 'is_vip') {
            $where['is_vip'] = 1;
        }
        $list = $this->dao->getRecommendProduct($where, is_string($field) ? $field : '', $num, 0, 0);
        foreach ($list as &$item) {
            $item['image'] = set_file_url($item['image']);
        }
        return $list;
    }

    /**
     * 商品名称 图片
     * @param array $productIds
     * @return array
     */
    public function getProductArray(array $where, string $field, string $key)
    {
        return $this->dao->getColumn($where, $field, $key);
    }

    /**
     * 获取商品详情
     * @param int $productId
     * @param string $field
     * @param array $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductInfo(int $productId, string $field = '*', array $with = [])
    {
        return $this->dao->getOne(['is_del' => 0, 'is_show' => 1, 'id' => $productId], $field, $with);
    }

    /** 生成商品复制口令关键字
     * @param int $productId
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductWords(int $productId)
    {
        $productInfo = $this->dao->getOne(['is_del' => 0, 'is_show' => 1, 'id' => $productId]);
        $keyWords = "";
        if ($productInfo) {
            $oneKey = "crmeb-fu致文本 Http:/ZБ";
            $twoKey = "Б轉移至☞" . sys_config('site_name') . "☜";
            $threeKey = "【" . $productInfo['store_name'] . "】";
            $mainKey = base64_encode($productId);
            $keyWords = $oneKey . $mainKey . $twoKey . $threeKey;
        }
        return $keyWords;
    }

    /**
     * 获取会员价格（付费会员价格和购买商品会员价格）
     * @param int $uid
     * @param $userInfo
     * @param $vipStatus
     * @param $goodsList
     * @param string $discount
     * @param bool $isSingle
     * @param float $payVipPrice
     * @param bool $is_show
     * @return float|int|mixed|string
     */
    public function isPayLevelPrice(int $uid, $userInfo, $vipStatus, $price, string $discount, $payVipPrice = 0.00, $is_vip = 0, $is_show = false)
    {
        //is_vip == 0表示会员价格不启用，展示为零
        if ($is_vip == 0) $payVipPrice = 0;
        if (!$userInfo && $uid) {
            //检测用户是否是付费会员
            /** @var  UserServices $userService */
            $userService = app()->make(UserServices::class);
            $userInfo = $userService->getUserInfo($uid);
        }
        $noPayVipPrice = ($discount && $discount != 0.00) ? bcmul((string)$discount, (string)$price, 2) : $price;
        if ($payVipPrice < $noPayVipPrice && $payVipPrice > 0) {
            $vipPrice = $payVipPrice;
            $type = 'member';
        } else {
            $vipPrice = $noPayVipPrice;
            $type = 'level';
        }

        //如果$isSingle==true 返回优惠后的总金额，否则返回优惠的金额
        if ($vipStatus && $is_vip == 1) {
            //$is_show == false 是计算支付价格，true是展示
            if (!$is_show) {
                return [$vipPrice, bcsub((string)$price, (string)$vipPrice, 2), $type];
            } else {
                if ($userInfo && isset($userInfo['is_money_level']) && $userInfo['is_money_level'] > 0) {
                    return [$vipPrice, bcsub((string)$price, (string)$vipPrice, 2), $type];
                } else {
                    return [$noPayVipPrice, bcsub((string)$price, (string)$noPayVipPrice, 2), $type];
                }
            }
        } else {
            return [$noPayVipPrice, bcsub((string)$price, (string)$noPayVipPrice, 2), $type];
        }
    }

    /**
     * 通过商品id获取商品分类
     * @param array $productId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function productIdByProductCateName(array $productId)
    {
        $data = $this->dao->productIdByCateId($productId);
        $cateData = [];
        foreach ($data as $item) {
            $cateData[$item['id']] = implode(',', array_map(function ($i) {
                return $i['cate_name'];
            }, $item['cateName']));
        }
        return $cateData;
    }

    /**
     * 获取预售列表
     * @param $where
     * @return mixed
     */
    public function getAdvanceList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getAdvanceList($where, $page, $limit);
        foreach ($data['list'] as &$item) {
        }
        return $data;
    }

    /**
     * 商品分享二维码 推广员
     * @param int $id
     * @param string $userType
     * @param $user
     * @return mixed|string
     */
    public function getCode(int $id, string $userType, $user)
    {
        if (!$id || !$this->isValidProduct($id, 'id')) {
            throw new ApiException('商品不存在');
        }
        if ($userType == 'routine') {
            /** @var QrcodeServices $qrcodeService */
            $qrcodeService = app()->make(QrcodeServices::class);
            $url = $qrcodeService->getRoutineQrcodePath($id, $user['uid'], 0, ['is_promoter' => $user['is_promoter']]);
            if (!$url) {
                throw new ApiException('二维码生成失败');
            } else {
                if ($url == 'unpublished') throw new ApiException('小程序尚未发布,无法生成商品海报');
                return $url;
            }
        }
        return '';
    }

    /**
     * 商品批量设置
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function batchSetting($data)
    {
        $ids = $data['ids'];
        $batchData = [];
        if (!count($ids)) throw new AdminException('请选择商品');
        switch ($data['type']) {
            case 1: // 修改分类
                $cate_id = $data['cate_id'];
                if (!count($cate_id)) throw new AdminException('请选择分类');
                /** @var StoreCategoryServices $storeCategoryServices */
                $storeCategoryServices = app()->make(StoreCategoryServices::class);
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                if (!$cateGory) throw new AdminException('分类不存在');
                $time = time();
                $cateData = [];
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        foreach ($ids as $product_id) {
                            $cateData[$product_id][] = ['product_id' => $product_id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => 1, 'add_time' => $time];
                        }
                    }
                }
                /** @var StoreProductCateServices $storeProductCateServices */
                $storeProductCateServices = app()->make(StoreProductCateServices::class);
                foreach ($ids as $product_id) {
                    $storeProductCateServices->change($product_id, $cateData[$product_id]);
                    $this->dao->update($product_id, ['cate_id' => implode(',', $cate_id)]);
                }
                break;
            case 2:
                foreach ($ids as $product_id) {
                    // 仅实物商品批量修改物流信息
                    if (!$this->isVirtualProduct($this->getProductType($product_id))) {
                        // 通过事件机制解耦 app/ 层对 modules/ 层扩展服务的直接引用
                        event('ProductBatchLogistics', [$product_id, $data]);
                    }
                }
                break;
            case 3:
                foreach ($ids as $product_id) {
                    $batchData[] = [
                        'id' => $product_id,
                        'give_integral' => $data['give_integral']
                    ];
                }
                if (count($batchData)) $this->dao->saveAll($batchData);
                break;
            case 4:
                /** @var StoreProductCouponServices $storeProductCouponServices */
                $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
                foreach ($ids as $product_id) {
                    if (!empty($data['coupon_ids'])) {
                        $storeProductCouponServices->setCoupon($product_id, $data['coupon_ids']);
                    } else {
                        $storeProductCouponServices->delete(['product_id' => $product_id]);
                    }
                }
                break;
            case 5:
                foreach ($ids as $product_id) {
                    $batchData[] = [
                        'id' => $product_id,
                        // 中台表使用 label_list 字段存储标签ID
                        'label_list' => implode(',', $data['label_id'])
                    ];
                }
                if (count($batchData)) $this->dao->saveAll($batchData);
                break;
            case 9:
                foreach ($ids as $product_id) {
                    $batchData[] = [
                        'id' => $product_id,
                        'label_list' => implode(',', $data['label_list'])
                    ];
                }
                if (count($batchData)) $this->dao->saveAll($batchData);
                break;
            case 10:
                $productTypeInfo = $this->dao->getColumn(['id' => $ids], 'product_type', 'id');
                foreach ($ids as $product_id) {
                    // 仅实物商品批量修改赠品信息
                    if (!$this->isVirtualProduct($productTypeInfo[$product_id] ?? 'normal')) {
                        $batchData[] = [
                            'id' => $product_id,
                            'is_gift' => $data['is_gift'],
                            'gift_price' => $data['gift_price'],
                        ];
                    }
                }
                if (count($batchData)) $this->dao->saveAll($batchData);
                break;
            default:
                return true;
        }
        return true;
    }

    /**
     * 商品迁移导出
     * @param $where
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/10/9
     */
    public function productExportList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $cateIds = [];
        if (isset($where['cate_id']) && $where['cate_id']) {
            /** @var StoreCategoryServices $storeCategory */
            $storeCategory = app()->make(StoreCategoryServices::class);
            $cateIds = $storeCategory->getColumn(['pid' => $where['cate_id']], 'id');
        }
        if ($cateIds) {
            $cateIds[] = $where['cate_id'];
            $where['cate_id'] = $cateIds;
        }
        $productList = $this->dao->getList($where, $page, $limit);
        $header = [
            '商品编号',
            '商品名称',
            '商品类型',
            '商品分类(一级)',
            '商品分类(二级)',
            '商品单位',
            '商品图片',
            '商品视频',
            '商品详情',
            '已售数量',
            '起购数量',
            '规格类型',
            '规格类型值',
            '规格名称',
            '规格值组合',
            '规格图片',
            '售价',
            '划线价',
            '成本价',
            '库存',
            '重量',
            '体积',
            '商品编码',
            '条形码',
            '商品简介',
            '商品关键字',
            '商品口令',
            '购买送积分'
        ];
        $filename = '商品迁移数据_' . date('YmdHis', time());
        $export = $fileKey = [];
        if (!empty($productList)) {
            $productList = array_column($productList, null, 'id');
            $productIds = array_column($productList, 'id');
            $descriptionArr = app()->make(StoreDescriptionServices::class)->getColumn([['product_id', 'in', $productIds], ['type', '=', 0]], 'description', 'product_id');
            $cateIds = implode(',', array_column($productList, 'cate_id'));
            /** @var StoreCategoryServices $categoryService */
            $categoryService = app()->make(StoreCategoryServices::class);
            $cateList = $categoryService->getCateParentAndChildName($cateIds);
            /** @var ProductTypeRegistry $registry */
            $registry = app()->make(ProductTypeRegistry::class);
            $i = 0;
            foreach ($productList as $product_id => $productInfo) {
                // 通过产品类型 Handler 获取规格数据，不再查询已弃用的 eb_store_product_attr_result
                $handler = $registry->get($productInfo['product_type'] ?? 'normal');
                $specs = $handler->getDetailSpecs((int)$product_id);
                $productAttr = $specs['productAttr'] ?? [];
                $productValue = $specs['productValue'] ?? [];

                $cateName = array_filter($cateList, function ($val) use ($productInfo) {
                    if (in_array($val['id'], explode(',', $productInfo['cate_id']))) {
                        return $val;
                    }
                });
                if (count($cateName) == 1 && is_null($cateName[0]['one'])) {
                    $cateName = [['one' => $cateName[0]['two'], 'two' => '', 'id' => $cateName[0]['id']]];
                }

                // 构造规格类型值字符串
                $attrArr = [];
                foreach ($productAttr as $attrItem) {
                    $attrValues = $attrItem['attr_values'] ?? [];
                    $attrArr[] = ($attrItem['attr_name'] ?? '') . '=' . implode(',', $attrValues);
                }
                $attrString = implode(';', $attrArr);

                foreach ($productValue as $unique => $value) {
                    $detail = $value['detail'] ?? ($value['suk'] ? array_map('trim', explode(',', $value['suk'])) : ['默认']);
                    $skuArr = [];
                    foreach ($productAttr as $index => $attrItem) {
                        $attrName = $attrItem['attr_name'] ?? ('规格' . ($index + 1));
                        $skuArr[$attrName] = $detail[$index] ?? '';
                    }
                    $one_data = [
                        'id' => intval($product_id),
                        'store_name' => $productInfo['name'],
                        'product_type' => $productInfo['product_type'] ?? 'normal',
                        'type_name' => $this->getProductTypeName($productInfo['product_type'] ?? 'normal'),
                        'cate_name_one' => reset($cateName)['one'] ?? '',
                        'cate_name_two' => reset($cateName)['two'] ?? '',
                        'unit_name' => $productInfo['unit_name'],
                        'slider_image' => implode(';', $productInfo['slider_image']),
                        'video_link' => $productInfo['video_link'],
                        'description' => htmlspecialchars_decode($descriptionArr[$productInfo['id']] ?? ''),
                        'ficti' => intval($productInfo['virtual_sales'] ?? 0),
                        'min_qty' => intval($productInfo['min_qty'] ?? 1),
                        'spec_type' => intval($productInfo['spec_type']) == 1 ? '多规格' : '单规格',
                        'sku_type_value' => $attrString,
                        'sku_name' => implode(',', $detail),
                        'sku_value' => implode(';', array_map(function ($key, $value) {
                            return "$key=$value";
                        }, array_keys($skuArr), $skuArr)),
                        'pic' => $value['image'] ?? '',
                        'price' => floatval($value['price'] ?? 0),
                        'ot_price' => floatval($value['ot_price'] ?? 0),
                        'cost' => floatval($value['cost'] ?? 0),
                        'stock' => intval($value['stock'] ?? 0),
                        'volume' => intval($value['volume'] ?? 0),
                        'weight' => intval($value['weight'] ?? 0),
                        'bar_code' => $value['bar_code'] ?? '',
                        'bar_code_number' => $value['bar_code_number'] ?? '',
                        'store_info' => $productInfo['intro'],
                        'keyword' => $productInfo['keyword'],
                        'command_word' => '',
                        'give_integral' => $productInfo['give_integral'],
                    ];
                    $export[] = $one_data;
                    if ($i == 0) {
                        $fileKey = array_keys($one_data);
                    }
                    $i++;
                }
            }
        }
        return compact('header', 'fileKey', 'export', 'filename');
    }

    public function productImport($file)
    {
        $all = $success = $fail = 0;
        $file = public_path() . substr($file, 1);
        // 获取文件后缀
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($suffix, ['xls', 'xlsx'])) {
            return app('json')->fail('文件格式不正确，请上传xls或xlsx格式的文件！');
        }
        $importData = app()->make(File::class)->readExcel($file, 'product', 1, ucfirst($suffix));
        if (!$importData) {
            throw new AdminException('导入数据为空');
        }
        $productCateServices = app()->make(StoreCategoryServices::class);
        $productData = $issetProductArr = [];
        // 已迁移到实物商品独立 SKU 表 eb_pt_normal_sku 校验条形码唯一性
        //通过事件代理查询 SKU 条形码集合，避免 app 层直接引用 modules
        $barCodeResults = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumn', 'args' => [[], 'bar_code', 'id']]);
        $barCodeArr = array_unique((is_array($barCodeResults) && $barCodeResults[0] !== null) ? $barCodeResults[0] : []);
        $barCodeNumberResults = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumn', 'args' => [[], 'bar_code_number', 'id']]);
        $barCodeNumberArr = array_unique((is_array($barCodeNumberResults) && $barCodeNumberResults[0] !== null) ? $barCodeNumberResults[0] : []);
        foreach ($importData as $sku) {
            if ($sku['id'] == null) continue;
            if (!isset($productData[$sku['id']])) {
                $productData[$sku['id']]['disk_info'] = '';
                $productData[$sku['id']]['logistics'] = [1, 2];
                $productData[$sku['id']]['freight'] = 2;
                $productData[$sku['id']]['postage'] = 0;
                $productData[$sku['id']]['recommend'] = [];
                $productData[$sku['id']]['presale'] = 0;
                $productData[$sku['id']]['is_limit'] = 0;
                $productData[$sku['id']]['limit_type'] = 0;
                $productData[$sku['id']]['limit_num'] = 0;
                $productData[$sku['id']]['video_open'] = $sku['video_link'] !== '' ? 1 : 0;
                $productData[$sku['id']]['vip_product'] = 0;
                $productData[$sku['id']]['custom_form'] = [];
                $productData[$sku['id']]['store_name'] = $sku['store_name'];
                $productData[$sku['id']]['cate_id'] = $productCateServices->getCateId($sku['cate_name_one'] ?? '', $sku['cate_name_two'] ?? '');
                $productData[$sku['id']]['keyword'] = $sku['keyword'] ?? '';
                $productData[$sku['id']]['unit_name'] = $sku['unit_name'] ?? '';
                $productData[$sku['id']]['store_info'] = $sku['store_info'] ?? '';
                $productData[$sku['id']]['image'] = '';
                $productData[$sku['id']]['recommend_image'] = '';
                $productData[$sku['id']]['slider_image'] = explode(';', $sku['slider_image']);
                $productData[$sku['id']]['description'] = $sku['description'] ?? '';
                $productData[$sku['id']]['ficti'] = $sku['ficti'] ?? 0;
                $productData[$sku['id']]['give_integral'] = $sku['give_integral'] ?? 0;
                $productData[$sku['id']]['sort'] = 0;
                $productData[$sku['id']]['is_show'] = 0;
                $productData[$sku['id']]['is_postage'] = 0;
                $productData[$sku['id']]['is_sub'] = 0;
                $productData[$sku['id']]['is_vip'] = 0;
                $productData[$sku['id']]['recommend_list'] = [];
                $productData[$sku['id']]['product_type'] = $sku['product_type'] ?? 'normal';
                $productData[$sku['id']]['spec_type'] = $sku['spec_type'] == '多规格' ? 1 : 0;
                $productData[$sku['id']]['is_virtual'] = 0;
                $productData[$sku['id']]['video_link'] = is_null($sku['video_link']) ? '' : $sku['video_link'];
                $productData[$sku['id']]['temp_id'] = '';
                $productData[$sku['id']]['activity'] = ['默认', '秒杀', '砍价', '拼团'];
                $productData[$sku['id']]['couponName'] = [];
                $productData[$sku['id']]['coupon_ids'] = [];
                $productData[$sku['id']]['command_word'] = $sku['command_word'] ?? '';
                $productData[$sku['id']]['min_qty'] = $sku['min_qty'];
                $productData[$sku['id']]['type'] = 0;
                $productData[$sku['id']]['is_copy'] = 0;
                $productData[$sku['id']]['label_id'] = [];
                $productData[$sku['id']]['params_list'] = [];
                $productData[$sku['id']]['label_list'] = [];
                $productData[$sku['id']]['protection_list'] = [];
            }
            $detail = [];
            foreach (explode(';', $sku['sku_value']) as $pair) {
                list($key, $value) = explode('=', $pair);
                $detail[$key] = $value;
            }
            if ($sku['bar_code'] !== '' && in_array($sku['bar_code'], $barCodeArr)) {
                $issetProductArr[] = $sku['id'];
            }
            if ($sku['bar_code_number'] !== '' && in_array($sku['bar_code_number'], $barCodeNumberArr)) {
                $issetProductArr[] = $sku['id'];
            }
            $productData[$sku['id']]['attrs'][] = [
                'attr_arr' => explode(',', $sku['sku_name']),
                'detail' => $detail,
                'price' => $sku['price'],
                'pic' => $sku['pic'],
                'ot_price' => $sku['ot_price'] ?? 0,
                'cost' => $sku['cost'],
                'stock' => $sku['stock'],
                'is_show' => 1,
                'is_default_select' => 0,
                'is_virtual' => 0,
                'brokerage' => 0,
                'brokerage_two' => 0,
                'vip_price' => 0,
                'vip_proportion' => 0,
                'unique' => '',
                'weight' => $sku['weight'],
                'volume' => $sku['volume'],
                'bar_code' => $sku['bar_code'],
                'bar_code_number' => $sku['bar_code_number'],
            ];
            $items = [];
            $pairs = explode(';', $sku['sku_type_value']);
            foreach ($pairs as $pair) {
                // 将每个部分按等号分割成 key 和 value
                list($key, $values) = explode('=', $pair);
                // 将 value 部分按逗号分割为数组
                $detailArray = explode(',', $values);
                $detail = [];
                foreach ($detailArray as &$det) {
                    $detail[] = [
                        'value' => $det,
                        'pic' => '',
                    ];
                }
                // 重新构建原始数组的结构
                $items[] = [
                    'value' => $key,
                    'detail' => $detail
                ];
            }
            $productData[$sku['id']]['items'] = $items;
        }
        $all = count($productData);
        foreach (array_unique($issetProductArr) as $issetProduct) {
            if (isset($productData[$issetProduct])) {
                unset($productData[$issetProduct]);
            }
        }
        $success = count($productData);
        $jump = $all - $success;
        foreach ($productData as $info) {
            $this->save(0, $info);
        }
        $fail = 0;
        return compact('all', 'success', 'jump', 'fail');
    }

    public function otherInfo($id, $type)
    {
        $storeInfo = $this->dao->get($id, ['id', 'is_sub', 'is_vip', 'vip_product']);
        if (!$storeInfo) throw new AdminException('商品不存在');
        $storeInfo = $storeInfo->toArray();
        $storeInfo['store_brokerage_ratio'] = sys_config('store_brokerage_ratio');
        $storeInfo['store_brokerage_two'] = sys_config('store_brokerage_two');
        // 已迁移到实物商品独立 SKU 表 eb_pt_normal_sku
        //通过事件代理查询商品启用的 SKU 列表，避免 app 层直接引用 modules
        $attrValueResults = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getShowListByProductId', 'args' => [(int)$id]]);
        $attrValue = (is_array($attrValueResults) && $attrValueResults[0] !== null) ? $attrValueResults[0] : [];
        // SKU 图片在库中为相对路径（/uploads/...），返回前补全站点域名，避免后台规格图裂图
        $siteUrl = sys_config('site_url');
        foreach ($attrValue as &$attrItem) {
            foreach (['image', 'pic'] as $imageField) {
                if (!empty($attrItem[$imageField]) && is_string($attrItem[$imageField])) {
                    $attrItem[$imageField] = set_file_url($attrItem[$imageField], $siteUrl);
                }
            }
        }
        unset($attrItem);
        return compact('storeInfo', 'attrValue');
    }

    public function otherSave($id, $type, $data)
    {
        $upProductData = [];
        if ($type == 2) {
            $upProductData = [
                'is_sub' => intval($data['is_sub'] ?? 0),
                'is_vip' => intval($data['is_vip'] ?? 0),
                'vip_product' => $data['vip_product'],
            ];
        }
        if ($upProductData) $this->dao->update($id, $upProductData);
        // 已迁移到实物商品独立 SKU 表 eb_pt_normal_sku，不再维护 eb_store_product_attr_result
        foreach ($data['attr_value'] as $item) {
            $upAttrData = [];
            if ($type == 2) {
                // 未开启会员价时清空 SKU 会员价字段
                $upAttrData = [
                    'vip_price' => !empty($data['is_vip']) ? ($item['vip_price'] ?? 0) : 0
                ];
            }
            if ($upAttrData && !empty($item['id'])) {
                //通过事件代理更新 SKU 会员价，避免 app 层直接引用 modules
                event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'update', 'args' => [(int)$item['id'], $upAttrData]]);
            }
        }
        return true;
    }

    /**
     * 真实价格
     * @param $uid
     * @param $id
     * @param $unique
     * @return array
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/2/5
     */
    public function realPrice($uid, $id, $unique)
    {
        $isMember = $isVip = $levelPrice = $memberPrice = $realPrice = $price = 0;
        $levelDiscount = 100;
        if ($uid) {
            $isMember = app()->make(UserServices::class)->value(['uid' => $uid], 'is_money_level');
            if (sys_config('member_func_status', 0)) {
                $levelDiscount = app()->make(UserLevelServices::class)->getUerLevelInfoByUid($uid, 'discount');
            }
        }
        $productIsVip = $this->dao->value(['id' => $id], 'is_vip');
        // 已迁移到 ProductTypeHandler::getSkuInfo，不再查询 eb_store_product_attr_value
        $productType = $this->getProductType((int)$id);
        $handler = app()->make(ProductTypeRegistry::class)->get($productType);
        $attrInfo = $handler->getSkuInfo((int)$id, (string)$unique);
        if (empty($attrInfo)) {
            return ['real_price' => 0, 'price' => 0, 'is_vip' => 0, 'product_is_vip' => $productIsVip, 'member_price' => 0, 'level_price' => 0, 'user_is_member' => $isMember, 'ot_price' => 0];
        }
        $realPrice = $price = $attrInfo['price'];
        $levelPrice = bcmul($attrInfo['price'], bcdiv($levelDiscount, 100, 2), 2);
        $memberPrice = $attrInfo['vip_price'] ?? 0;
        $otPrice = $attrInfo['ot_price'] ?? 0;
        if ($levelDiscount && $isMember && $productIsVip) {
            $realPrice = min($levelPrice, $memberPrice);
            $isVip = $memberPrice < $levelPrice ? 1 : 0;
        }
        if ($levelDiscount && (!$isMember || !$productIsVip)) {
            $realPrice = $levelPrice;
            $isVip = 0;
        }
        if (!$levelDiscount && $isMember && $productIsVip) {
            $realPrice = $memberPrice;
            $isVip = 1;
        }
        /** @var CenterProductServices $storeProductService */
        $storeProductService = app()->make(CenterProductServices::class);
        /** @var StoreCategoryServices $storeCategoryService */
        $storeCategoryService = app()->make(StoreCategoryServices::class);
        $cateId = $storeProductService->value(['id' => $id], 'cate_id');
        $cateId = explode(',', (string)$cateId);
        $cateId = array_merge($cateId, $storeCategoryService->cateIdByPid($cateId));
        $cateId = array_diff($cateId, [0]);
        $list = app()->make(StoreCouponIssueServices::class)->getPcIssueCouponList($uid, $cateId, $id);
        usort($list, function ($a, $b) {
            return $b['coupon_price'] - $a['coupon_price'];
        });
        $time = time();
        foreach ($list as $item) {
            // 优惠券不在使用时间范围内
            if ($item['start_use_time'] != 0 && ($item['start_use_time'] > $time || $item['end_use_time'] < $time)) {
                continue;
            }
            // 用户未登录或者不是付费会员跳过付费会员券
            if ($item['receive_type'] == 4 && !$isMember) {
                continue;
            }
            // 判断用户是否还能领取或者已经领取未使用
            if ($uid) {
                $canUserCoupon = app()->make(StoreCouponUserServices::class)->getUserCouponCanUse($uid, $item['id'], $item['receive_limit']);
                if (!$canUserCoupon) {
                    continue;
                }
            }
            // 满足优惠券使用门槛
            if ($realPrice >= $item['use_min_price']) {
                $realPrice = bcsub($realPrice, $item['coupon_price'], 2);
                if ($realPrice < 0) $realPrice = 0;
                break;
            }
        }
        return ['real_price' => $realPrice, 'price' => $price, 'is_vip' => $isVip, 'product_is_vip' => $productIsVip, 'member_price' => $memberPrice, 'level_price' => $levelPrice, 'user_is_member' => $isMember, 'ot_price' => $otPrice];
    }

    /**
     * 批量移动回收站
     * @param $ids
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2025/6/18
     */
    public function batchDelete($ids)
    {
        $passNum = 0;
        foreach ($ids as $id) {
            //删除商品检测是否有参与活动
            $res = $this->checkActivity($id, true);
            if ($res) {
                $data['is_del'] = 1;
                $data['is_show'] = 0;
                $this->dao->update($id, $data);
                app()->make(StoreProductCateServices::class)->update(['product_id' => $id], ['status' => 0]);
                app()->make(StoreCartServices::class)->changeStatus($id, 0);
            } else {
                $passNum++;
            }
        }
        return $passNum ? $passNum . '件商品参与活动无法移到回收站，其他商品移动回收站成功' : '移动回收站成功';
    }

    public function batchRecover($ids)
    {
        foreach ($ids as $id) {
            $data['is_del'] = 0;
            $this->dao->update($id, $data);
            app()->make(StoreProductCateServices::class)->update(['product_id' => $id], ['status' => 1]);
        }
        return true;
    }

    /**
     * 自定义组件-商品
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/1/13
     */
    public function getThemeProduct($where)
    {
        $sort = $where['sort'] ? 'asc' : 'desc';
        switch ($where['order']) {
            case 0:
                $order = 'sales ' . $sort;
                break;
            case 1:
                $order = 'price ' . $sort;
                break;
            default:
                $order = 'sort desc';
                break;
        }
        $limit = empty($where['ids']) ? (int)$where['limit'] : 1000;
        $list = $this->dao->getThemeProduct($where, $order, $limit);
        foreach ($list as &$item) {
            $item['cate_name'] = $item['cateName'][0]['cate_name'];
            unset($item['cateName']);
        }
        if (empty($where['ids'])) return $list;
        // 将$list转换为以id为键的数组
        $list = array_column($list, null, 'id');
        $data = [];
        // 遍历where中的ids，按顺序取出对应商品
        foreach (explode(',', $where['ids']) as $id) {
            if (isset($list[$id])) {
                $data[] = $list[$id];
            }
        }
        return $data;
    }

    /**
     * 清除回收站商品
     * @param int $id 商品ID，0表示清除所有回收站商品
     * @return bool
     */
    public function recycleProduct(int $id = 0): bool
    {
        if ($id) {
            $ids = [$id];
        } else {
            $ids = $this->getColumn(['is_del' => 1], 'id');
        }

        if (empty($ids)) {
            return true;
        }

        // 触发产品类型删除回调，由各自 Handler 清理扩展表/SKU 数据
        // 不再直接操作已弃用的 eb_store_product_attr* 表
        /** @var ProductTypeRegistry $registry */
        $registry = app()->make(ProductTypeRegistry::class);
        foreach ($ids as $productId) {
            try {
                $productType = $this->getProductType((int)$productId);
                $registry->get($productType)->afterProductDelete((int)$productId);
            } catch (\Throwable $e) {
                // 单个商品删除回调异常不影响整体流程
            }
        }

        // 删除商品详情
        /** @var StoreDescriptionServices $productDescription */
        $productDescription = app()->make(StoreDescriptionServices::class);
        $productDescription->delete([['product_id', 'in', $ids], ['type', '=', '0']]);

        // 删除商品关联分类数据
        /** @var StoreProductCateServices $productCate */
        $productCate = app()->make(StoreProductCateServices::class);
        $productCate->delete([['product_id', 'in', $ids]]);

        // 删除商品关联优惠券数据
        /** @var StoreProductCouponServices $productCoupon */
        $productCoupon = app()->make(StoreProductCouponServices::class);
        $productCoupon->delete([['product_id', 'in', $ids]]);

        // 删除商品收藏记录
        /** @var StoreProductReplyServices $productRelation */
        $productRelation = app()->make(StoreProductReplyServices::class);
        $productRelation->delete([['product_id', 'in', $ids], ['reply_type', '=', 'product']]);

        // 删除商品的评论
        /** @var StoreProductReplyServices $productReply */
        $productReply = app()->make(StoreProductReplyServices::class);
        $productReply->delete([['product_id', 'in', $ids]]);

        // 删除商品
        if ($id) {
            $this->delete($id);
        } else {
            $this->delete(1, 'is_del');
        }

        return true;
    }

    /**
     * 用户端商品列表
     * 查询 eb_product_center 中 is_show=1, is_del=0 的商品
     * 字段映射：中台字段名 → 前端期望字段名
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUserList(array $where, int $page, int $limit): array
    {
        $where['is_show'] = 1;
        $where['is_del'] = 0;

        // 查询字段：中台列 → 前端需的字段
        $field = [
            'id',
            'name as store_name',
            'cate_id',
            'image',
            'IFNULL(sales, 0) + IFNULL(virtual_sales, 0) as sales',
            'price',
            'stock',
            'original_price as ot_price',
            'spec_type',
            'image as recommend_image',
            'unit_name',
            'product_type',
            'sort_order',
            'is_vip',
            'vip_price',
            'custom_form',
        ];

        $list = $this->dao->getSearchList($where, $page, $limit, $field);

        // 为每项补充前端需要但中台表没有的字段（默认值）
        foreach ($list as &$item) {
            $item['activity'] = '';
            $item['vip_price'] = '0.00';
            $item['is_virtual'] = $this->isVirtualProduct($item['product_type'] ?? 'normal');
            $item['presale'] = 0;
            $item['min_qty'] = 1;
            $item['label_list'] = '';
            $item['store_info'] = '';
            $item['star'] = '5.0';
            // 自定义表单商品不支持加入购物车（前端据此点击直接进详情）
            $item['cart_button'] = $this->hasCustomForm($item['custom_form'] ?? '') || $this->getCartModeByType($item['product_type'] ?? 'normal') === 'direct' ? 0 : 1;

            // 标记活动商品
            if (!empty($item['activity'])) {
                $item['activity'] = json_decode($item['activity'], true) ?? '';
            }
        }

        return $list;
    }

    /**
     * 获取产品类型列表（供后台创建商品选择/活动选商品）
     * @return array
     */
    public function getTypeList(): array
    {
        /** @var \crmeb\trade\product\ProductTypeRegistry $registry */
        $registry = $this->app->make(\crmeb\trade\product\ProductTypeRegistry::class);
        return $registry->allInfo();
    }
}
