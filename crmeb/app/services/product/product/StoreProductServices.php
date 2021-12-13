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

namespace app\services\product\product;


use app\dao\product\product\StoreProductDao;
use app\Request;
use app\services\activity\StoreAdvanceServices;
use app\services\activity\StoreBargainServices;
use app\services\activity\StoreCombinationServices;
use app\services\activity\StoreSeckillServices;
use app\services\BaseServices;
use app\services\coupon\StoreCouponIssueServices;
use app\services\order\StoreCartServices;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\product\sku\StoreProductRuleServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\shipping\ShippingTemplatesServices;
use app\services\system\attachment\SystemAttachmentCategoryServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\MemberCardServices;
use app\services\user\UserSearchServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use app\jobs\ProductLogJob;
use app\jobs\ProductCopyJob;
use crmeb\services\GroupDataService;
use Lizhichao\Word\VicWord;
use think\exception\ValidateException;
use think\facade\Config;

/**
 * Class StoreProductService
 * @package app\services\product\product
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
 */
class StoreProductServices extends BaseServices
{
    public function __construct(StoreProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取顶部标签
     * @return array[]
     */
    public function getHeader()
    {
        //出售中的商品
        $onsale = $this->dao->getCount(['type' => 1]);
        //仓库中的商品
        $forsale = $this->dao->getCount(['type' => 2]);
        //已经售馨商品
        $outofstock = $this->dao->getCount(['type' => 4]);
        //警戒库存商品
        $policeforce = $this->dao->getCount(['type' => 5, 'store_stock' => sys_config('store_stock') > 0 ? sys_config('store_stock') : 2]);
        //回收站的商品
        $recycle = $this->dao->getCount(['type' => 6]);
        return [
            ['type' => 1, 'name' => '出售中的商品', 'count' => $onsale],
            ['type' => 2, 'name' => '仓库中的商品', 'count' => $forsale],
            ['type' => 4, 'name' => '已经售馨商品', 'count' => $outofstock],
            ['type' => 5, 'name' => '警戒库存商品', 'count' => $policeforce],
            ['type' => 6, 'name' => '回收站的商品', 'count' => $recycle]
        ];
    }

    /**
     * 获取列表
     * @param $where
     * @return array
     */
    public function getList(array $where)
    {
        $where['store_stock'] = sys_config('store_stock') > 0 ? sys_config('store_stock') : 2;
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
        foreach ($list as &$item) {
            $cateName = array_filter($cateList, function ($val) use ($item) {
                if (in_array($val['id'], explode(',', $item['cate_id']))) {
                    return $val;
                }
            });
            $item['cate_name'] = [];
            foreach ($cateName as $k => $v) {
                $item['cate_name'][] = $v['one'] . '/' . $v['two'];
            }
            $item['cate_name'] = is_array($item['cate_name']) ? implode(',', $item['cate_name']) : '';
            $item['stock_attr'] = $item['stock'] > 0 ? true : false;//库存
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 设置商品上下架
     * @param $ids
     * @param $is_show
     */
    public function setShow(array $ids, int $is_show)
    {
        if ($is_show == 0) {
            //下架检测是否有参与活动商品
            $this->checkActivity($ids);
        }
        if ($is_show == 1) {
            /** @var StoreAdvanceServices $advanceService */
            $advanceService = app()->make(StoreAdvanceServices::class);
            if ($advanceService->getAdvanceStatus($ids)) throw new AdminException('商品参与预售活动，无法上架');
        }
        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        foreach ($ids as $id) {
            $cartService->changeStatus($id, $is_show);
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
        /** @var StoreProductRuleServices $storeProductRuleServices */
        $storeProductRuleServices = app()->make(StoreProductRuleServices::class);
        $list = $storeProductRuleServices->getList()['list'];
        foreach ($list as &$item) {
            $item['rule_value'] = json_decode($item['rule_value'], true);
        }
        return $list;
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
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        /** @var StoreCouponIssueServices $storeCouponIssueServices */
        $storeCouponIssueServices = app()->make(StoreCouponIssueServices::class);
        $data['tempList'] = $this->getTemp();
        $menus = [];
        foreach ($storeCatecoryService->getTierList(1) as $menu) {
            $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['cate_name'], 'disabled' => $menu['pid'] == 0 ? 0 : 1];//,'disabled'=>$menu['pid']== 0];
        }
        $data['cateList'] = $menus;
        $productInfo = $this->dao->getInfo($id);
        if ($productInfo) $productInfo = $productInfo->toArray();
        else throw new ValidateException('商品不存在');
        $couponIds = array_column($productInfo['coupons'], 'issue_coupon_id');
        $is_sub = [];
        if ($productInfo['is_sub'] == 1) array_push($is_sub, 1);
        if ($productInfo['is_vip'] == 1) array_push($is_sub, 0);
        $productInfo['is_sub'] = $is_sub;
        $recommend_list = [];
        if ($productInfo['recommend_list'] != '') {
            $productInfo['recommend_list'] = explode(',', $productInfo['recommend_list']);
            if (count($productInfo['recommend_list'])) {
                $images = $this->getColumn([['id', 'in', $productInfo['recommend_list']]], 'image', 'id');
                foreach ($productInfo['recommend_list'] as $item) {
                    $recommend_list[] = [
                        'product_id' => $item,
                        'image' => $images[$item]
                    ];
                }
            }
        }
        $productInfo['recommend_list'] = $recommend_list;
        $productInfo['coupons'] = $storeCouponIssueServices->productCouponList([['id', 'in', $couponIds]], 'title,id');
        $productInfo['cate_id'] = explode(',', $productInfo['cate_id']);
        $productInfo['label_id'] = explode(',', $productInfo['label_id']);
        $productInfo['give_integral'] = floatval($productInfo['give_integral']);
        $productInfo['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 0]);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        //无属性添加默认属性
        if (!$storeProductAttrResultServices->getResult(['product_id' => $id, 'type' => 0])) {
            $attr = [
                [
                    'value' => '规格',
                    'detailValue' => '',
                    'attrHidden' => '',
                    'detail' => ['默认']
                ]
            ];
            $detail[0] = [
                'value1' => '默认',
                'detail' => ['规格' => '默认'],
                'pic' => $productInfo['image'],
                'price' => $productInfo['price'],
                'cost' => $productInfo['cost'],
                'ot_price' => $productInfo['ot_price'],
                'stock' => $productInfo['stock'],
                'bar_code' => '',
                'weight' => 0,
                'volume' => 0,
                'brokerage' => 0,
                'brokerage_two' => 0,
            ];
            $skuList = $this->validateProductAttr($attr, $detail, $id);
            $storeProductAttrServices->saveProductAttr($skuList, $id, 0);
            $this->dao->update($id, ['spec_type' => 0]);
        }
        if ($productInfo['spec_type'] == 1) {
            $result = $storeProductAttrResultServices->getResult(['product_id' => $id, 'type' => 0]);
            foreach ($result['value'] as $k => $v) {
                $num = 1;
                foreach ($v['detail'] as $dv) {
                    $result['value'][$k]['value' . $num] = $dv;
                    $num++;
                }
            }
            $productInfo['items'] = $result['attr'];
            $productInfo['attrs'] = $result['value'];
            $productInfo['attr'] = ['pic' => '', 'vip_price' => 0, 'price' => 0, 'cost' => 0, 'ot_price' => 0, 'stock' => 0, 'bar_code' => '', 'weight' => 0, 'volume' => 0, 'brokerage' => 0, 'brokerage_two' => 0];
        } else {
            /** @var StoreProductVirtualServices $virtualService */
            $virtualService = app()->make(StoreProductVirtualServices::class);
            $result = $storeProductAttrValueServices->getOne(['product_id' => $id, 'type' => 0]);
            $productInfo['items'] = [];
            $productInfo['attrs'] = [];
            $productInfo['attr'] = [
                'pic' => $result['image'] ?? '',
                'vip_price' => $result['vip_price'] ? floatval($result['vip_price']) : 0,
                'price' => $result['price'] ? floatval($result['price']) : 0,
                'cost' => $result['cost'] ? floatval($result['cost']) : 0,
                'ot_price' => $result['ot_price'] ? floatval($result['ot_price']) : 0,
                'stock' => $result['stock'] ? floatval($result['stock']) : 0,
                'bar_code' => $result['bar_code'] ?? '',
                'virtual_list' => $virtualService->getArr($result['unique'], $id),
                'weight' => $result['weight'] ? floatval($result['weight']) : 0,
                'volume' => $result['volume'] ? floatval($result['volume']) : 0,
                'brokerage' => $result['brokerage'] ? floatval($result['brokerage']) : 0,
                'brokerage_two' => $result['brokerage_two'] ? floatval($result['brokerage_two']) : 0,
                'coupon_id' => $result['coupon_id'],
                'coupon_name' => $storeCouponIssueServices->value(['id' => $result['coupon_id']], 'title')
            ];
        }
        if ($productInfo['activity']) {
            $activity = explode(',', $productInfo['activity']);
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
     * 获取运费模板列表
     * @return array
     */
    public function getTemp()
    {
        /** @var ShippingTemplatesServices $shippingTemplatesServices */
        $shippingTemplatesServices = app()->make(ShippingTemplatesServices::class);
        return $shippingTemplatesServices->getSelectList();
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
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        /** @var StoreProductVirtualServices $virtualService */
        $virtualService = app()->make(StoreProductVirtualServices::class);
        /** @var StoreCouponIssueServices $storeCouponIssueServices */
        $storeCouponIssueServices = app()->make(StoreCouponIssueServices::class);
        $attr = $data['attrs'];
        $is_virtual = $data['is_virtual']; //是否虚拟商品
        $virtual_type = $data['virtual_type']; //虚拟商品类型
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            foreach ($detail as $v => $d) {
                $detail[$v] = trim($d);
            }
            $suk = implode(',', $detail);
            $types = 1;
            if ($id) {
                $sukValue = $storeProductAttrValueServices->getColumn(['product_id' => $id, 'type' => 0, 'suk' => $suk], 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,vip_price,is_virtual,coupon_id,unique', 'suk');
                if (!$sukValue) {
                    if ($type == 0) $types = 0; //编辑商品时，将没有规格的数据不生成默认值
                    $sukValue[$suk]['pic'] = '';
                    $sukValue[$suk]['price'] = 0;
                    $sukValue[$suk]['cost'] = 0;
                    $sukValue[$suk]['ot_price'] = 0;
                    $sukValue[$suk]['stock'] = 0;
                    $sukValue[$suk]['bar_code'] = '';
                    if ($is_virtual) {
                        if ($virtual_type == 1) {
                            $sukValue[$suk]['virtual_list'] = [];
                        } elseif ($virtual_type == 2) {
                            $sukValue[$suk]['coupon_id'] = 0;
                        }
                    }
                    $sukValue[$suk]['weight'] = 0;
                    $sukValue[$suk]['volume'] = 0;
                    $sukValue[$suk]['brokerage'] = 0;
                    $sukValue[$suk]['brokerage_two'] = 0;
                }
            } else {
                $sukValue[$suk]['pic'] = '';
                $sukValue[$suk]['price'] = 0;
                $sukValue[$suk]['cost'] = 0;
                $sukValue[$suk]['ot_price'] = 0;
                $sukValue[$suk]['stock'] = 0;
                $sukValue[$suk]['bar_code'] = '';
                if ($is_virtual) {
                    if ($virtual_type == 1) {
                        $sukValue[$suk]['virtual_list'] = [];
                    } elseif ($virtual_type == 2) {
                        $sukValue[$suk]['coupon_id'] = 0;
                    }
                }
                $sukValue[$suk]['weight'] = 0;
                $sukValue[$suk]['volume'] = 0;
                $sukValue[$suk]['brokerage'] = 0;
                $sukValue[$suk]['brokerage_two'] = 0;
            }
            if ($types) { //编辑商品时，将没有规格的数据不生成默认值
                foreach (array_keys($detail) as $k => $title) {
                    $header[$k]['title'] = $title;
                    $header[$k]['align'] = 'center';
                    $header[$k]['minWidth'] = 130;
                }
                foreach (array_values($detail) as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                    $header[$k]['key'] = 'value' . ($k + 1);
                }
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['vip_price'] = isset($sukValue[$suk]['vip_price']) ? floatval($sukValue[$suk]['vip_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                if ($is_virtual) {
                    if ($virtual_type == 1) {
                        if (!$type) $valueNew[$count]['virtual_list'] = $virtualService->getArr($sukValue[$suk]['unique'], $id);
                    } elseif ($virtual_type == 2) {
                        $valueNew[$count]['coupon_id'] = $sukValue[$suk]['coupon_id'] ?? '';
                        $valueNew[$count]['coupon_name'] = $storeCouponIssueServices->value(['id' => $sukValue[$suk]['coupon_id']], 'title');
                    }
                }
                $valueNew[$count]['weight'] = floatval($sukValue[$suk]['weight']) ?? 0;
                $valueNew[$count]['volume'] = floatval($sukValue[$suk]['volume']) ?? 0;
                $valueNew[$count]['brokerage'] = floatval($sukValue[$suk]['brokerage']) ?? 0;
                $valueNew[$count]['brokerage_two'] = floatval($sukValue[$suk]['brokerage_two']) ?? 0;
                $count++;
            }
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '售价', 'slot' => 'price', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '成本价', 'slot' => 'cost', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '原价', 'slot' => 'ot_price', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '库存', 'slot' => 'stock', 'align' => 'center', 'minWidth' => 140];
        $header[] = ['title' => '产品编号', 'slot' => 'bar_code', 'align' => 'center', 'minWidth' => 140];
        if ($is_virtual) {
            if ($virtual_type == 1) {
                $header[] = ['title' => '虚拟商品', 'slot' => 'fictitious', 'align' => 'center', 'minWidth' => 140];
            } elseif ($virtual_type == 2) {
                $header[] = ['title' => '虚拟商品', 'slot' => 'fictitious', 'align' => 'center', 'minWidth' => 140];
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
        return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8) . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * 新增编辑商品
     * @param int $id
     * @param array $data
     */
    public function save(int $id, array $data)
    {
        $detail = $data['attrs'];
        $attr = $data['items'];
        $cate_id = $data['cate_id'];
        $coupon_ids = $data['coupon_ids'];
        $description = $data['description'];
        $type = $data['type'];
        if (count($data['recommend_list'])) {
            $data['recommend_list'] = implode(',', array_column($data['recommend_list'], 'product_id'));
        } else {
            $data['recommend_list'] = '';
        }
        $data['is_vip'] = in_array(0, $data['is_sub']) ? 1 : 0;
        $data['is_sub'] = in_array(1, $data['is_sub']) ? 1 : 0;
        if (count($data['cate_id']) < 1) throw new AdminException('请选择商品分类');
        if (!$data['store_name']) throw new AdminException('请输入商品名称');
        if (count($data['image']) < 1) throw new AdminException('请上传商品图片');
        if (count($data['slider_image']) < 1) throw new AdminException('请上传商品轮播图');
        if ($data['is_virtual'] == 0) $data['virtual_type'] = 0;
        foreach ($detail as &$item) {
            if ($data['is_sub'] == 0) {
                $item['brokerage'] = 0;
                $item['brokerage_two'] = 0;
            }
            if (($item['brokerage'] + $item['brokerage_two']) > $item['price']) {
                throw new AdminException('一二级返佣相加不能大于商品售价');
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

        $data['activity'] = implode(',', $data['activity']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['cost'] = min(array_column($detail, 'cost'));
        if (!$data['cost']) {
            $data['cost'] = 0;
        }
        $data['cate_id'] = implode(',', $data['cate_id']);
        $data['label_id'] = implode(',', $data['label_id']);
        $data['image'] = $data['image'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['description'], $data['coupon_ids'], $data['items'], $data['attrs'], $data['type']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductCouponServices $storeProductCouponServices */
        $storeProductCouponServices = app()->make(StoreProductCouponServices::class);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        $is_copy = $data['is_copy'] ?? 0;
        unset($data['is_copy']);
        $this->transaction(function () use ($id, $is_copy, $data, $description, $cate_id, $storeDescriptionServices, $storeProductCateServices, $storeProductAttrServices, $storeProductCouponServices, $storeCategoryServices, $detail, $attr, $coupon_ids, $type) {
            if ($data['spec_type'] == 0) {
                $attr = [
                    [
                        'value' => '规格',
                        'detailValue' => '',
                        'attrHidden' => '',
                        'detail' => ['默认']
                    ]
                ];
                $detail[0]['value1'] = '规格';
                $detail[0]['detail'] = ['规格' => '默认'];
            }
            foreach ($detail as &$item) {
                $item['is_virtual'] = $data['is_virtual'];
            }
            if ($id) {
                if ($this->dao->value(['id' => $id], 'is_show') == 1 && $data['is_show'] == 0) {
                    //下架检测是否有参与活动商品
                    $this->checkActivity($id);
                }
                $oldInfo = $this->get($id)->toArray();
                if ($oldInfo['is_virtual'] && $oldInfo['virtual_type'] != $data['virtual_type']) {
                    throw new AdminException('编辑虚拟商品不能切换虚拟类型！');
                }
                unset($data['sales']);
                $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription($id, $description);
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($id, $cateData);
                $skuList = $this->validateProductAttr($attr, $detail, $id);
                $attrRes = $storeProductAttrServices->saveProductAttr($skuList, $id, 0, $data['is_vip'], $data['virtual_type']);
                if (!empty($coupon_ids)) {
                    $storeProductCouponServices->setCoupon($id, $coupon_ids);
                } else {
                    $storeProductCouponServices->delete(['product_id' => $id]);
                }
                if (!$attrRes) throw new AdminException('添加失败！');
            } else {
                if ($is_copy) {
                    $data = $this->copyDownImage($data);
                }
                $data['add_time'] = time();
                $data['code_path'] = '';
                $data['spu'] = $this->createSpu();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription($res->id, $description);
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $res->id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($res->id, $cateData);
                $skuList = $this->validateProductAttr($attr, $detail, $res->id);
                $attrRes = $storeProductAttrServices->saveProductAttr($skuList, $res->id, 0, $data['is_vip'], $data['virtual_type']);
                if (!empty($coupon_ids)) $storeProductCouponServices->setCoupon($res->id, $coupon_ids);
                if (!$attrRes) throw new AdminException('添加失败！');
                if ($type == -1) {
                    ProductCopyJob::dispatch([(int)$res->id]);
                }
            }
        });
    }

    /**
     * 采集商品入库前下载远程图片
     * @param array $data
     * @return array
     */
    public function copyDownImage(array $data)
    {
        /** @var CopyTaobaoServices $copyServices */
        $copyServices = app()->make(CopyTaobaoServices::class);

        //查询附件分类
        /** @var SystemAttachmentCategoryServices $systemAttachmentCategoryService */
        $systemAttachmentCategoryService = app()->make(SystemAttachmentCategoryServices::class);
        $AttachmentCategory = $systemAttachmentCategoryService->getOne(['name' => $copyServices->AttachmentCategoryName]);
        //不存在则创建
        if (!$AttachmentCategory) $AttachmentCategory = $systemAttachmentCategoryService->save(['pid' => '0', 'name' => $copyServices->AttachmentCategoryName, 'enname' => '']);
        //生成附件目录
        try {
            if (make_path('attach', 3, true) === '')
                throw new AdminException('无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS);

        } catch (\Exception $e) {
            throw new AdminException($e->getMessage() . '或无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS);
        }

        //放入主图
        $images = [
            ['w' => 305, 'h' => 305, 'line' => $data['image'], 'valuename' => 'image']
        ];
        //放入轮播图
        foreach ($data['slider_image'] as $item) {
            $value = ['w' => 640, 'h' => 640, 'line' => $item, 'valuename' => 'slider_image', 'isTwoArray' => true];
            array_push($images, $value);
        }
        //执行下载
        $res = $copyServices->uploadImage($images, false, 0, $AttachmentCategory['id']);
        if (!is_array($res)) throw new AdminException($this->errorInfo ? $this->errorInfo : '保存图片失败');
        if (isset($res['image'])) $data['image'] = $res['image'];
        if (isset($res['slider_image'])) $data['slider_image'] = $res['slider_image'];
        $data['image'] = str_replace('\\', '/', $data['image']);
        if (count($data['slider_image'])) {
            $data['slider_image'] = array_map(function ($item) {
                $item = str_replace('\\', '/', $item);
                return $item;
            }, $data['slider_image']);
        }
        $data['slider_image'] = count($data['slider_image']) ? json_encode($data['slider_image']) : '';
        //替换并下载详情里面的图片默认下载全部图片
        $data['description'] = preg_replace('#<style>.*?</style>#is', '', $data['description']);
        $data['description'] = $copyServices->uploadImage($data['description_images'], $data['description'], 1, $AttachmentCategory['id']);
        unset($data['description_images']);
        return $data;
    }

    /**
     * 添加商品属性数据判断
     * @param array $attrList
     * @param array $valueList
     * @param int $productId
     * @param int $type
     * @return array
     */
    public function validateProductAttr(array $attrList, array $valueList, int $productId, $type = 0)
    {
        $result = ['attr' => $attrList, 'value' => $valueList];
        $attrValueList = [];
        $attrNameList = [];
        foreach ($attrList as $index => $attr) {
            if (!isset($attr['value'])) {
                throw new AdminException('请输入规则名称!');
            }
            $attr['value'] = trim($attr['value']);
            if (!isset($attr['value'])) {
                throw new AdminException('请输入规则名称!!');
            }
            if (!isset($attr['detail']) || !count($attr['detail'])) {
                throw new AdminException('请输入属性名称!');
            }
            foreach ($attr['detail'] as $k => $attrValue) {
                $attrValue = trim($attrValue);
                if (empty($attrValue)) {
                    throw new AdminException('请输入正确的属性');
                }
                $attr['detail'][$k] = $attrValue;
                $attrValueList[] = $attrValue;
                $attr['detail'][$k] = $attrValue;
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
            if (!isset($value['stock']) || !is_numeric($value['stock']) || intval($value['stock']) != $value['stock']) {
                throw new AdminException('请填写正确的商品库存');
            }
            if (!isset($value['cost']) || !is_numeric($value['cost']) || floatval($value['cost']) != $value['cost']) {
                throw new AdminException('请填写正确的商品成本价格');
            }
            if (!isset($value['pic']) || empty($value['pic'])) {
                throw new AdminException('请上传商品图片');
            }
            foreach ($value['detail'] as $attrName => $attrValue) {
                //如果attrName 存在空格 则这个规格key 会出现两次
                unset($valueList[$index]['detail'][$attrName]);
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if (!in_array($attrName, $attrNameList, true)) {
                    throw new AdminException($attrName . '规则不存在');
                }
                if (!in_array($attrValue, $attrValueList, true)) {
                    throw new AdminException($attrName . '属性不存在');
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
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $skuArray = $storeProductAttrValueServices->getColumn(['product_id' => $productId, 'type' => $type], 'unique', 'suk');
        foreach ($valueList as $k => $value) {
            $sku = implode(',', $value['detail']);
            $valueGroup[$sku] = [
                'product_id' => $productId,
                'suk' => $sku,
                'price' => $value['price'],
                'cost' => $value['cost'],
                'ot_price' => $value['ot_price'],
                'stock' => $value['stock'],
                'unique' => $skuArray[$sku] ?? '',
                'image' => $value['pic'],
                'bar_code' => $value['bar_code'] ?? '',
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
                'virtual_list' => $value['virtual_list'] ?? []
            ];
        }

        if (!count($attrGroup) || !count($valueGroup)) {
            throw new AdminException('请设置至少一个属性!');
        }
        return compact('result', 'attrGroup', 'valueGroup');
    }

    /**
     * 放入回收站
     * @param int $id
     * @return string
     */
    public function del(int $id)
    {
        if (!$id) throw new AdminException('参数不正确');
        $productInfo = $this->dao->get($id);
        if (!$productInfo) throw new AdminException('商品数据不存在');
        if ($productInfo['is_del'] == 1) {
            $data['is_del'] = 0;
            $res = $this->dao->update($id, $data);
            if (!$res) throw new AdminException('恢复失败,请稍候再试!');
            return '成功恢复商品';
        } else {
            $data['is_del'] = 1;
            $data['is_show'] = 0;
            $res = $this->dao->update($id, $data);
            /** @var StoreProductCateServices $storeProductCateServices */
            $storeProductCateServices = app()->make(StoreProductCateServices::class);
            $storeProductCateServices->update(['product_id' => $id], ['status' => 0]);
            if (!$res) throw new AdminException('删除失败,请稍候再试!');
            return '成功移到回收站';
        }
    }

    /**
     * 获取选择的商品列表
     * @param array $where
     * @return array
     */
    public function searchList(array $where, bool $isStock = false, $is_page = true)
    {
        $store_stock = sys_config('store_stock');
        $where['store_stock'] = $store_stock > 0 ? $store_stock : 2;
        $data = $this->getProductList($where, $isStock, $is_page);
        $cateIds = implode(',', array_column($data['list'], 'cate_id'));

        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        $cateList = $storeCategoryServices->getCateArray($cateIds);

        foreach ($data['list'] as &$item) {
            $cateName = array_filter($cateList, function ($val) use ($item) {
                if (in_array($val['id'], explode(',', $item['cate_id']))) {
                    return $val;
                }
            });
            $item['cate_name'] = implode(',', array_column($cateName, 'cate_name'));
            $item['give_integral'] = floatval($item['give_integral']);
            $item['price'] = floatval($item['price']);
            $item['vip_price'] = floatval($item['vip_price']);
            $item['ot_price'] = floatval($item['ot_price']);
            $item['postage'] = floatval($item['postage']);
            $item['cost'] = floatval($item['cost']);
            $item['is_product_type'] = 1;
        }
        return $data;
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
                '(SELECT SUM(stock) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as stork',
//                '(SELECT SUM(sales) FROM `' . $prefix . 'store_product_attr_value` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `type` = 0) as sales',
                '(SELECT count(*) FROM `' . $prefix . 'store_visit` WHERE `product_id` = `' . $prefix . 'store_product`.`id` AND `product_type` = \'product\') as visitor',
            ];
        } else {
            $field = ['*'];
        }
        [$page, $limit] = $this->getPageValue($is_page);
        $list = $this->dao->getSearchList($where, $page, $limit, $field);
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
        /** @var StoreProductAttrServices $storeProductAttrService */
        $storeProductAttrService = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $productAttr = $storeProductAttrService->getProductAttr(['product_id' => $id, 'type' => 0]);
        if (!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key => $value) {
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        $sukValue = $sukDefaultValue = $storeProductAttrValueServices->getSkuArray(['product_id' => $id, 'type' => 0]);
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            $suk = implode(',', $item['detail']);
            if (!isset($sukDefaultValue[$suk])) continue;
            foreach (array_keys($detail) as $k => $title) {
                $header[$k]['title'] = $title;
                $header[$k]['align'] = 'center';
                $header[$k]['minWidth'] = 80;
            }
            foreach (array_values($detail) as $k => $v) {
                $valueNew[$count]['value' . ($k + 1)] = $v;
                $header[$k]['key'] = 'value' . ($k + 1);
            }
            $valueNew[$count]['detail'] = $detail;
            $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
            $valueNew[$count]['price'] = floatval($sukValue[$suk]['price']);
            if ($type == 2) $valueNew[$count]['min_price'] = 0;
            if ($type == 3) $valueNew[$count]['r_price'] = floatval($sukValue[$suk]['price']);
            $valueNew[$count]['cost'] = floatval($sukValue[$suk]['cost']);
            $valueNew[$count]['ot_price'] = floatval($sukValue[$suk]['ot_price']);
            $valueNew[$count]['stock'] = intval($sukValue[$suk]['stock']);
            $valueNew[$count]['quota'] = intval($sukValue[$suk]['quota']);
            $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'];
            $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
            $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
            $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
            $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
            $count++;
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        if ($type == 1) {
            $header[] = ['title' => '秒杀价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 2) {
            $header[] = ['title' => '砍价起始金额', 'slot' => 'price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '砍价最低价', 'slot' => 'min_price', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 3) {
            $header[] = ['title' => '拼团价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '日常售价', 'key' => 'r_price', 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 4) {
            $header[] = ['title' => '兑换积分', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } elseif ($type == 6) {
            $header[] = ['title' => '预售价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
            $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        if ($type == 2 || $type == 6) {
            $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        } else {
            $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        return ['items' => $attr, 'attrs' => $valueNew, 'header' => $header];
    }

    /**
     * 检查商品是否有活动
     * @param  $id
     * @return bool
     */
    public function checkActivity($id = 0)
    {
        if ($id) {
            /** @var StoreSeckillServices $storeSeckillService */
            $storeSeckillService = app()->make(StoreSeckillServices::class);
            $res1 = $storeSeckillService->count(['product_id' => $id, 'is_del' => 0]);
            if ($res1) {
                throw new AdminException('商品参与秒杀活动开启，无法进行此操作');
            }
            /** @var StoreBargainServices $storeBargainService */
            $storeBargainService = app()->make(StoreBargainServices::class);
            $res2 = $storeBargainService->count(['product_id' => $id, 'is_del' => 0]);
            if ($res2) {
                throw new AdminException('商品参与砍价活动开启，无法进行此操作');
            }
            /** @var StoreCombinationServices $storeCombinationService */
            $storeCombinationService = app()->make(StoreCombinationServices::class);
            $res3 = $storeCombinationService->count(['product_id' => $id, 'is_del' => 0]);
            if ($res3) {
                throw new AdminException('商品参与拼团活动开启，无法进行此操作');
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
        [$page, $limit] = $this->getPageValue();
        /** @var UserSearchServices $userSearchServices */
        $userSearchServices = app()->make(UserSearchServices::class);
        $keyword = $vicword = $where['store_name'] ?? '';
        $ifKeyword = isset($where['store_name']) && $where['store_name'];
        if ($ifKeyword) {
            //分词
            try {
                $scws = new VicWord();
                $vicWordArr = $scws->getAutoWord($keyword);
            } catch (\Throwable $e) {
                $vicWordArr = [];
            }
            if ($vicWordArr) $vicword = array_column($vicWordArr, 0);
            $result = $userSearchServices->getKeywordResult(0, $where['store_name']);
            $ids = [];
            if ($result && isset($result['result']) && $result['result']) {//之前查询结果记录
                $where['ids'] = $ids = $result['result'];
                unset($where['store_name']);
            } else {//分词查询
                $where['store_name'] = $vicword;
            }
            //搜索没有记录
            if (!$ids) {
                //查出所有结果ids存搜索记录表
                $idsArr = $this->dao->getSearchList($where, 0, 0, ['id']);
                if ($idsArr) {
                    $where['ids'] = $ids = array_column($idsArr, 'id');
                    unset($where['store_name']);
                }
                $vicword = is_string($vicword) ? [$vicword] : $vicword;
                $userSearchServices->saveUserSearch($uid, $keyword, $vicword, $ids);
            }
        }
        if ($where['productId'] !== '') {
            $where['ids'] = explode(',', $where['productId']);
            $where['ids'] = array_unique(array_map('intval', $where['ids']));
            unset($where['productId']);
        }
        $list = $this->dao->getSearchList($where, $page, $limit, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,ot_price,spec_type,recommend_image,unit_name,is_vip,vip_price,is_virtual']);
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
        foreach ($list as &$item) {
            if (!$this->vipIsOpen(!!$item['is_vip'], $vipStatus)) {
                $item['vip_price'] = 0;
            }
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
            /** @var StoreProductAttrValueServices $services */
            $services = app()->make(StoreProductAttrValueServices::class);
            $attList = $services->getColumn([
                'product_id' => $productIds,
                'type' => 0
            ], 'count(*)', 'product_id');
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
                if ($item['spec_type']) {
                    $item['is_att'] = isset($attList[$item['id']]) && $attList[$item['id']] ? true : false;
                } else {
                    $item['is_att'] = false;
                }
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
        if ($seckillIdsList === false) {
            /** @var StoreSeckillServices $storeSeckillService */
            $storeSeckillService = app()->make(StoreSeckillServices::class);
            $seckillIdsList = $storeSeckillService->getSeckillIdsArray($productIds, ['id', 'time_id', 'product_id']);
        }
        if ($pinkIdsList === false) {
            /** @var StoreCombinationServices $storeCombinationServices */
            $storeCombinationServices = app()->make(StoreCombinationServices::class);
            $pinkIdsList = $storeCombinationServices->getPinkIdsArray($productIds, ['id']);
        }
        if ($bargrainIdsList === false) {
            /** @var StoreBargainServices $storeBargainServices */
            $storeBargainServices = app()->make(StoreBargainServices::class);
            $bargrainIdsList = $storeBargainServices->getBargainIdsArray($productIds, ['id']);
        }
        foreach ($list as &$item) {
            $seckillId = array_filter($seckillIdsList, function ($val) use ($item) {
                if ($val['product_id'] === $item['id']) {
                    return $val;
                }
            });
            $item['activity'] = $this->activity($item['activity'],
                $item['id'],
                $pinkIdsList[$item['id']] ?? 0,
                $seckillId,
                $bargrainIdsList[$item['id']] ?? 0,
                $status);

            if (isset($item['couponId'])) {
                $item['checkCoupon'] = count($item['couponId']) ? true : false;
                unset($item['couponId']);
            } else {
                $item['checkCoupon'] = false;
            }
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
            $activity = '0,1,2,3';//如果老商品没有活动顺序，默认活动顺序，秒杀-砍价-拼团
        }
        $activity = explode(',', $activity);
        if ($activity[0] == 0 && $status) return [];
        $activityId = [];
        $time = 0;
        if ($seckillId) {
            foreach ($seckillId as $v) {
                $timeInfo = GroupDataService::getDataNumber((int)$v['time_id']);
                if ($timeInfo && isset($timeInfo['time']) && isset($timeInfo['continued'])) {
                    if (date('H') >= $timeInfo['time'] && date('H') < ($timeInfo['time'] + $timeInfo['continued'])) {
                        $activityId[1] = $v['id'];
                        $time = strtotime(date("Y-m-d"), time()) + 3600 * ($timeInfo['time'] + $timeInfo['continued']);
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
                    if ($v == 1) $data['time'] = $time;
                    break;
                } else {
                    if ($v != 0) {
                        $arr['type'] = $v;
                        $arr['id'] = $activityId[$v];
                        if ($v == 1) $arr['time'] = $time;
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
        $list = $this->dao->getSearchList($where, $page, $limit, ['id,store_name,cate_id,image,IFNULL(sales, 0) + IFNULL(ficti, 0) as sales,price,stock,activity,unit_name']);
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
            throw new ValidateException('商品不存在');
        } else {
            $storeInfo = $storeInfo->toArray();
        }
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['video_link'] = empty($storeInfo['video_link']) ? '' : strpos($storeInfo['video_link'],'http') === false ? sys_config('site_url').$storeInfo['video_link'] : $storeInfo['video_link'];
        $storeInfo['fsales'] = $storeInfo['ficti'] + $storeInfo['sales'];

        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);
        $storeInfo['code_base'] = $qrcodeService->getWechatQrcodePath($id . '_product_detail_wap.jpg', '/pages/goods_details/index?id=' . $id);

        /** @var StoreProductRelationServices $storeProductRelationServices */
        $storeProductRelationServices = app()->make(StoreProductRelationServices::class);
        $storeInfo['userCollect'] = $storeProductRelationServices->isProductRelation(['uid' => $uid, 'product_id' => $id, 'type' => 'collect', 'category' => 'product']);
        $storeInfo['userLike'] = false;

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($id, $uid, $type, 0);
        //无属性添加默认属性
        if (empty($productValue)) {
            $attr = [
                [
                    'value' => '规格',
                    'detailValue' => '',
                    'attrHidden' => '',
                    'detail' => ['默认']
                ]
            ];
            $detail[0] = [
                'value1' => '默认',
                'detail' => ['规格' => '默认'],
                'pic' => $storeInfo['image'],
                'price' => $storeInfo['price'],
                'cost' => $storeInfo['cost'],
                'ot_price' => $storeInfo['ot_price'],
                'stock' => $storeInfo['stock'],
                'bar_code' => '',
                'weight' => 0,
                'volume' => 0,
                'brokerage' => 0,
                'brokerage_two' => 0,
            ];
            $skuList = $this->validateProductAttr($attr, $detail, $id);
            $storeProductAttrServices->saveProductAttr($skuList, $id, 0);
        }
        $attrValue = $productValue;
        if (!$storeInfo['spec_type']) {
            $productAttr = [];
            $productValue = [];
        }
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['storeInfo'] = get_thumb_water($storeInfo, 'big', ['image', 'slider_image']);
        $storeInfoNew = get_thumb_water($storeInfo, 'small', ['image']);
        $data['storeInfo']['small_image'] = $storeInfoNew['image'];

        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $data['svip_open'] = $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
        $data['storeInfo']['svip_economize_price'] = bcsub((string)$data['storeInfo']['price'], (string)$data['storeInfo']['vip_price'], 2);
        if (!$this->vipIsOpen(!!$storeInfo['is_vip'], $vipStatus)) {
            $data['storeInfo']['vip_price'] = 0;
        }
        $data['priceName'] = 0;
        if ($uid) {
            $user = $request->user();
            if (!$user->is_promoter) {
                /** @var StoreOrderServices $storeOrderService */
                $storeOrderService = app()->make(StoreOrderServices::class);
                $price = $storeOrderService->sum(['paid' => 1, 'refund_status' => 0, 'uid' => $uid], 'pay_price');
                $status = is_brokerage_statu($price);
                if ($status) {
                    /** @var UserServices $userServices */
                    $userServices = app()->make(UserServices::class);
                    $userServices->update($uid, ['is_promoter' => 1]);
                    $user->is_promoter = 1;
                }
            }
            $data['priceName'] = $this->getPacketPrice($storeInfo, $attrValue, $uid);
            //用户访问事件
            event('user.userVisit', [$uid, $id, 'product', $storeInfo['cate_id'], 'view']);
        }

        /** @var StoreProductReplyServices $storeProductReplyService */
        $storeProductReplyService = app()->make(StoreProductReplyServices::class);
        $data['reply'] = get_thumb_water($storeProductReplyService->getRecProductReply($id), 'small', ['pics']);
        [$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData($id);
        $data['replyChance'] = $replyChance;
        $data['replyCount'] = $replyCount;
        $data['mer_id'] = 0;
        if ($storeInfo['recommend_list'] != '') {
            $recommend_list = explode(',', $storeInfo['recommend_list']);
            $data['good_list'] = get_thumb_water($this->getProducts(['ids' => $recommend_list], 12));
            $recommend_count = 12 - count($data['good_list']);
            if ($recommend_count) $data['good_list'] = array_merge($data['good_list'], get_thumb_water($this->getProducts(['is_good' => 1, 'is_del' => 0, 'is_show' => 1], $recommend_count)));
        } else {
            $data['good_list'] = get_thumb_water($this->getProducts(['is_good' => 1, 'is_del' => 0, 'is_show' => 1], 12));
        }
        $data['mapKey'] = sys_config('tengxun_map_key');
        $data['store_self_mention'] = (int)sys_config('store_self_mention') ?? 0;//门店自提是否开启
        $data['activity'] = $this->getActivityList($data['storeInfo'], false);
        /** @var StoreCouponIssueServices $couponService */
        $couponService = app()->make(StoreCouponIssueServices::class);
        $data['coupons'] = $couponService->getIssueCouponList($uid, ['product_id' => $id, 'type' => -1])['list'];
        $data['routine_contact_type'] = sys_config('routine_contact_type', 0);
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $id]]);
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
        $store_brokerage_ratio = sys_config('store_brokerage_ratio');
        $store_brokerage_ratio = bcdiv((string)$store_brokerage_ratio, '100', 2);
        if (isset($storeInfo['is_sub']) && $storeInfo['is_sub'] == 1) {
            $maxPrice = (int)max(array_column($productValue, 'brokerage'));
            $minPrice = (int)min(array_column($productValue, 'brokerage'));
        } else {
            $maxPrice = max(array_column($productValue, 'price'));
            $minPrice = min(array_column($productValue, 'price'));
            $maxPrice = bcmul((string)$store_brokerage_ratio, (string)$maxPrice, 0);
            $minPrice = bcmul((string)$store_brokerage_ratio, (string)$minPrice, 0);
        }
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
     * @param int $is_vip
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
     * @param int $is_vip
     * @param bool $is_show
     * @return array|float|int|mixed|string
     */
    public function setLevelPrice($price, int $uid, $userInfo, $vipStatus, $discount = 0, $vipPrice = 0.00, $is_vip = 0, $is_show = false)
    {
        if (!(float)$price) return $price;
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
        $productInfoField = 'id,image,price,ot_price,vip_price,postage,give_integral,sales,stock,store_name,unit_name,is_show,is_del,is_postage,cost,is_sub,temp_id';
        if (!empty($ids)) {
            $productAll = $this->dao->idByProductList($ids, $field ?: $productInfoField);
            if (!empty($productAll))
                $productData = array_combine(array_column($productAll, 'id'), $productAll);
        }
        return $productData;
    }

    /**
     * 商品是否存在
     * @param $productId
     * @return bool
     */
    public function isValidProduct(int $productId)
    {
        return $this->dao->getOne(['id' => $productId, 'is_del' => 0, 'is_show' => 1]);
    }

    /**
     * 获取商品库存
     * @param int $productId
     * @param string $uniqueId
     * @return int|mixed
     */
    public function getProductStock(int $productId, string $uniqueId = '')
    {
        /** @var  StoreProductAttrValueServices $StoreProductAttrValue */
        $StoreProductAttrValue = app()->make(StoreProductAttrValueServices::class);
        return $uniqueId == '' ?
            $this->dao->value(['id' => $productId], 'stock') ?: 0
            : $StoreProductAttrValue->uniqueByStock($uniqueId);
    }

    /**
     * 减库存,加销量
     * @param $num
     * @param $productId
     * @param string $unique
     * @return bool
     */
    public function decProductStock(int $num, int $productId, string $unique = '')
    {
        $res = true;
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            $res = $res && $skuValueServices->decProductAttrStock($productId, $unique, $num, 0);
        }
        $res = $res && $this->dao->decStockIncSales(['id' => $productId], $num);
        if ($res) {
            //已经在规格库存里面发过提醒，此处总库存注释
            //$this->workSendStock($productId);
        }
        return $res;
    }

    /**
     * 加销量减库存
     * @param int $num
     * @param int $productId
     * @param string $unique
     * @return bool
     */
    public function incProductStock(int $num, int $productId, string $unique = '')
    {
        $res = true;
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            $res = $res && $skuValueServices->incProductAttrStock($productId, $unique, $num);
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
        $replenishment_num = sys_config('store_stock') ?? 0;//库存预警界限
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
    public function getRecommendProductArr(int $uid, array $fields, bool $is_num = true, string $type = 'small')
    {
        $baseList = $firstList = $benefitList = $hotList = $vipList = [];
        $data = [$baseList, $firstList, $benefitList, $hotList, $vipList];
        if ($fields) {
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
            $seckillIdsList = $pinkIdsList = $bargrainIdsList = false;
            if (count($fields) > 1) {
                /** @var StoreSeckillServices $storeSeckillService */
                $storeSeckillService = app()->make(StoreSeckillServices::class);
                $seckillIdsList = $storeSeckillService->getSeckillIdsArray([], ['id', 'time_id', 'product_id']);
                /** @var StoreCombinationServices $storeCombinationServices */
                $storeCombinationServices = app()->make(StoreCombinationServices::class);
                $pinkIdsList = $storeCombinationServices->getPinkIdsArray([], ['id']);
                /** @var StoreBargainServices $storeBargainServices */
                $storeBargainServices = app()->make(StoreBargainServices::class);
                $bargrainIdsList = $storeBargainServices->getBargainIdsArray([], ['id']);
            }
            [$page, $limit] = $this->getPageValue();
            foreach ($fields as $field) {
                $list = [];
                switch ($field) {
                    case 'is_best'://精品推荐
                        $k = 0;
                        if ($is_num) {
                            $bastNumber = (int)sys_config('bast_number', 0);//TODO 精品推荐个数
                            $list = $bastNumber ? $list = $this->dao->getRecommendProduct($field, $bastNumber, $page, $limit) : [];
                        } else {
                            $list = $this->dao->getRecommendProduct($field, 0, $page, $limit);
                        }
                        break;
                    case 'is_new'://首发新品
                        $k = 1;
                        if ($is_num) {
                            $firstNumber = (int)sys_config('first_number', 0);//TODO 首发新品个数
                            $list = $firstNumber ? $list = $this->dao->getRecommendProduct($field, $firstNumber, $page, $limit) : [];
                        } else {
                            $list = $this->dao->getRecommendProduct($field, 0, $page, $limit);
                        }
                        break;
                    case 'is_benefit'://首页促销单品
                        $k = 2;
                        if ($is_num) {
                            $promotionNumber = (int)sys_config('promotion_number', 0);//TODO 首发新品个数
                            $list = $promotionNumber ? $list = $this->dao->getRecommendProduct($field, $promotionNumber, $page, $limit) : [];
                        } else {
                            $list = $this->dao->getRecommendProduct($field, 0, $page, $limit);
                        }
                        break;
                    case 'is_hot'://热门榜单
                        $k = 3;
                        $hotNumber = $is_num ? 3 : 0;
                        $list = $this->dao->getRecommendProduct($field, $hotNumber, $page, $limit);
                        break;
                    case 'is_vip'://会员
                        $k = 4;
                        $list = $this->dao->getRecommendProduct($field, 0, $page, $limit);
                        break;
                }
                if ($list) {
                    $list = get_thumb_water($list, $type);
                    $list = $this->getActivityList($list, true, $seckillIdsList, $pinkIdsList, $bargrainIdsList);
                    foreach ($list as &$item) {
                        if (!($vipStatus && $item['is_vip'])) {
                            $item['vip_price'] = 0;
                        }
                    }
                }
                if (isset($k)) $data[$k] = $list;
            }
        }
        return $data;
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
    public function getRecommendProduct(int $uid, $field, int $num = 0, string $type = 'small')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getRecommendProduct($field, $num, $page, $limit);
        if ($list) {
            $list = get_thumb_water($list, $type);
            $list = $this->getActivityList($list);
            /** @var MemberCardServices $memberCardService */
            $memberCardService = app()->make(MemberCardServices::class);
            $vipStatus = $memberCardService->isOpenMemberCard('vip_price');
            foreach ($list as &$item) {
                if (!($vipStatus && $item['is_vip'])) {
                    $item['vip_price'] = 0;
                }
            }
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
     * @param int $is_vip
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
}
