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

namespace app\services\product\product;

use app\dao\product\CenterProductDao;
use app\jobs\ProductStockJob;
use crmeb\basic\BaseService;
use app\services\order\StoreCartServices;
use app\services\product\CenterProductServices;
use crmeb\exceptions\AdminException;

/**
 * Class OutCenterProductServices
 * @package app\services\product\product
 * @method getOne(array $where) 获取一条数据
 * @method update(int $id, array $data) 更新数据
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
class OutCenterProductServices extends BaseService
{
    /**
     * @var CenterProductDao
     */
    protected $dao;

    public function __construct(CenterProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 新增编辑商品
     * @param int $id
     * @param array $data
     * @param int $validate
     */
    public function save(int $id, array $data)
    {
        if (count($data['cate_id']) < 1) throw new AdminException('请选择商品分类');
        if (!$data['store_name']) throw new AdminException('请输入商品名称');

        if (count($data['slider_image']) < 1) {
            $data['is_show'] = 0;
        }

        $data['brand_id'] = 0;
        $data['logistics'] = 1; // 物流方式

        $detail = $data['attrs'];
        $attr = $data['items'];
        $cate_id = $data['cate_id'];
        $description = $data['description'];

        $data['is_limit'] = intval($data['is_limit']);
        if (!$data['is_limit']) {
            $data['limit_type'] = 0;
            $data['limit_num'] = 0;
        } else {
            if (!in_array($data['limit_type'], [1, 2])) throw new AdminException('请选择限购类型');
            if ($data['limit_num'] <= 0) throw new AdminException('限购数量不能小于1');
        }

        // 固定邮费 0为包邮
        $data['freight'] = 2;
        $data['postage'] = (float)$data['postage'];
        if (bccomp($data['postage'], '0.00', 2) < 0) {
            throw new AdminException('运费格式错误');
        }

        if (count($data['activity']) == 4) {
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
        } else {
            $data['activity'] = '0,1,2,3';
        }

        // is_hot/is_benefit/is_new/is_good/is_best 推荐位字段已删除；is_sub/is_vip/vip_product 已迁移到 eb_product_center 保留
        $data['is_sub'] = intval($data['is_sub'] ?? 0);
        $data['is_vip'] = intval($data['is_vip'] ?? 0);
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

        foreach ($detail as &$item) {
            if (isset($item['stock'])) {
                unset($item['stock']);
            }

            if (empty($item['pic'])) {
                $data['is_show'] = 0;
            }

            $item['cost'] = sprintf("%.2f", $item['cost'] ?? '0.00');
            $item['price'] = sprintf("%.2f", $item['price'] ?? '0.00');
            $item['ot_price'] = sprintf("%.2f", $item['ot_price'] ?? '0.00');
            $item['weight'] = (string)$item['weight'];
            $item['volume'] = (string)$item['volume'];
            $item['pic'] = $item['pic'] ?? '';

            if ($data['is_vip'] == 0) {
                $item['vip_price'] = '0.00';
            } else {
                $item['vip_price'] = sprintf("%.2f", $item['vip_price'] ?? '0.00');
            }

            if ($data['is_sub'] == 0) {
                $item['brokerage'] = '0.00';
                $item['brokerage_two'] = '0.00';
            } else {
                $item['brokerage'] = sprintf("%.2f", $item['brokerage'] ?? '0.00');
                $item['brokerage_two'] = sprintf("%.2f", $item['brokerage_two'] ?? '0.00');
                if (bccomp(bcadd($item['brokerage'], $item['brokerage_two']), $item['price']) == 1) {
                    throw new AdminException('一二级返佣相加不能大于商品售价');
                }
            }
        }

        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['cost'] = min(array_column($detail, 'cost'));
        $data['cate_id'] = implode(',', $data['cate_id']);
        $data['image'] = $data['slider_image'][0] ?? '';
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['give_integral'] = (int)$data['give_integral'];
        unset($data['description'], $data['coupon_ids'], $data['items'], $data['attrs']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        return $this->transaction(function () use ($id, $data, $description, $cate_id, $storeDescriptionServices, $storeProductCateServices, $storeCategoryServices, $detail, $attr, $productServices) {
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
            if ($id) {
                $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription($id, $description, 'normal');
                $cateData = [];
                $time = time();
                $cateGory = $storeCategoryServices->getColumn([['id', 'IN', $cate_id]], 'id,pid', 'id');
                foreach ($cate_id as $cid) {
                    if ($cid && isset($cateGory[$cid]['pid'])) {
                        $cateData[] = ['product_id' => $id, 'cate_id' => $cid, 'cate_pid' => $cateGory[$cid]['pid'], 'status' => $data['is_show'], 'add_time' => $time];
                    }
                }
                $storeProductCateServices->change($id, $cateData);
                $skuList = $productServices->validateProductAttr($attr, $detail, $id, 0, 0);
                $attrRes = $this->saveProductSku($id, $skuList);
                if (!$attrRes) throw new AdminException('添加失败');
            } else {
                $data['add_time'] = time();
                $data['code_path'] = '';
                $data['spu'] = $productServices->createSpu();
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
                $skuList = $productServices->validateProductAttr($attr, $detail, $res->id, 0, 0, true);
                $attrRes = $this->saveProductSku($res->id, $skuList);
                if (!$attrRes) throw new AdminException('添加失败');
                $id = (int)$res->id;
            }
            return $id;
        });
    }

    /**
     * 设置商品上下架
     * @param int $id
     * @param int $is_show
     */
    public function setShow(int $id, int $is_show)
    {
        if (empty($id)) throw new AdminException('参数错误');

        if ($is_show) {
            // 检查商品是否可以上架
            $this->checkShelves($id);
        }

        /** @var StoreCartServices $cartService */
        $cartService = app()->make(StoreCartServices::class);
        $cartService->changeStatus($id, $is_show);
        $this->dao->update($id, ['is_show' => $is_show]);

        /** @var StoreProductCateServices $storeProductCateServices */
        $storeProductCateServices = app()->make(StoreProductCateServices::class);
        $storeProductCateServices->update($id, ['status' => $is_show], 'product_id');
        return true;
    }

    /**
     * 获取商品详情
     * 旧表 eb_store_product_attr_result / eb_store_product_attr_value 已弃用，
     * 统一通过产品类型 Handler 从新 SKU 表读取规格数据。
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        $productInfo = $this->dao->get($id, ['id', 'image', 'slider_image', 'store_name', 'store_info', 'keyword', 'bar_code',
            'cate_id', 'price', 'original_price as ot_price', 'postage', 'unit_name', 'sort_order as sort', 'is_show', 'cost', 'spec_type', 'spu', 'freight',
            'is_vip', 'vip_price', 'is_limit', 'limit_type', 'limit_num', 'give_integral']);
        if ($productInfo) {
            $productInfo = $productInfo->toArray();
        } else {
            throw new AdminException('商品不存在');
        }

        $productInfo['cate_id'] = $productInfo['cate_id'] ? array_map('intval', explode(',', $productInfo['cate_id'])) : [];
        $productInfo['cate_name'] = '';
        if ($productInfo['cate_id']) {
            /** @var StoreCategoryServices $storeCategoryServices */
            $storeCategoryServices = app()->make(StoreCategoryServices::class);
            $cateList = $storeCategoryServices->getCateArray(implode(',', $productInfo['cate_id']));
            $productInfo['cate_name'] = implode(',', array_column($cateList, 'cate_name'));
        }

        $productInfo['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'activity_type' => 'normal']);

        // 通过产品类型 Handler 获取规格数据（不再查询弃用的 eb_store_product_attr_result / eb_store_product_attr_value）
        try {
            $rules = $productServices->getProductRules($id);
            $items = $rules['items'] ?? [];
            $attrs = $rules['attrs'] ?? [];

            if (!empty($items) && !empty($attrs)) {
                // 多规格：还原 value 中 detail 为平铺格式（value1/value2/...），与旧前端格式兼容
                foreach ($attrs as $k => $v) {
                    $detail = $v['detail'] ?? [];
                    $num = 1;
                    foreach ($detail as $dv) {
                        $attrs[$k]['value' . $num] = $dv;
                        $num++;
                    }
                    $attrs[$k]['price'] = sprintf("%.2f", $v['price'] ?? '0.00');
                    $attrs[$k]['cost'] = sprintf("%.2f", $v['cost'] ?? '0.00');
                    $attrs[$k]['ot_price'] = sprintf("%.2f", $v['ot_price'] ?? '0.00');
                    $attrs[$k]['vip_price'] = sprintf("%.2f", $v['vip_price'] ?? '0.00');
                    $attrs[$k]['weight'] = sprintf("%.2f", $v['weight'] ?? '0.00');
                    $attrs[$k]['volume'] = sprintf("%.2f", $v['volume'] ?? '0.00');
                    $attrs[$k]['brokerage'] = sprintf("%.2f", $v['brokerage'] ?? '0.00');
                    $attrs[$k]['brokerage_two'] = sprintf("%.2f", $v['brokerage_two'] ?? '0.00');
                }
                $productInfo['items'] = $items;
                $productInfo['attrs'] = $attrs;
            } else {
                // 无规格数据 -> 从中台表构建默认单规格
                $productInfo['items'] = [];
                $productInfo['attrs'] = [[
                    'pic' => $productInfo['image'] ?? '',
                    'price' => sprintf("%.2f", $productInfo['price'] ?? '0.00'),
                    'cost' => sprintf("%.2f", $productInfo['cost'] ?? '0.00'),
                    'ot_price' => sprintf("%.2f", $productInfo['ot_price'] ?? '0.00'),
                    'bar_code' => '',
                    'bar_code_number' => '',
                    'weight' => '0.00',
                    'volume' => '0.00',
                    'brokerage' => '0.00',
                    'brokerage_two' => '0.00',
                    'vip_price' => sprintf("%.2f", $productInfo['vip_price'] ?? '0.00'),
                ]];
            }
        } catch (\Throwable $e) {
            // 异常时回退到默认单规格
            $productInfo['items'] = [];
            $productInfo['attrs'] = [[
                'pic' => $productInfo['image'] ?? '',
                'price' => sprintf("%.2f", $productInfo['price'] ?? '0.00'),
                'cost' => sprintf("%.2f", $productInfo['cost'] ?? '0.00'),
                'ot_price' => sprintf("%.2f", $productInfo['ot_price'] ?? '0.00'),
                'bar_code' => '',
                'bar_code_number' => '',
                'weight' => '0.00',
                'volume' => '0.00',
                'brokerage' => '0.00',
                'brokerage_two' => '0.00',
                'vip_price' => sprintf("%.2f", $productInfo['vip_price'] ?? '0.00'),
            ]];
        }

        return $productInfo;
    }

    /**
     * 获取选择的商品列表
     * @param array $where
     * @return array
     */
    public function searchList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $field = ['id', 'image', 'slider_image', 'store_name', 'store_info', 'keyword', 'bar_code', 'cate_id', 'price',
            'is_vip', 'vip_price', 'original_price as ot_price', 'postage', 'unit_name', 'sort_order as sort', 'is_show', 'cost', 'spec_type', 'spu', 'freight'];
        $list = $this->dao->getSearchList($where, $page, $limit, $field, ['description']);

        $cateIds = implode(',', array_column($list, 'cate_id'));
        /** @var StoreCategoryServices $storeCategoryServices */
        $storeCategoryServices = app()->make(StoreCategoryServices::class);
        $cateList = $storeCategoryServices->getCateArray($cateIds);

        foreach ($list as &$item) {
            $item['cate_id'] = $item['cate_id'] ? array_map('intval', explode(',', $item['cate_id'])) : [];
            $cateName = array_filter($cateList, function ($val) use ($item) {
                if (in_array($val['id'], $item['cate_id'])) {
                    return $val;
                }
            });

            $item['cate_name'] = implode(',', array_column($cateName, 'cate_name'));
        }
        $count = $this->dao->getCount($where);
        return compact('count', 'list');
    }

    /**
     * 上架检测
     * @param int $id
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function checkShelves(int $id)
    {
        $productInfo = $this->dao->get($id, ['id', 'image', 'slider_image', 'spec_type', 'unit_name', 'cate_id'], []);
        if ($productInfo) {
            $productInfo = $productInfo->toArray();
        } else {
            throw new AdminException('商品不存在');
        }

        if (!$productInfo['image'] || !$productInfo['slider_image']) {
            throw new AdminException('请选择商品轮播图');
        }

        if (!$productInfo['unit_name']) {
            throw new AdminException('请填写单位');
        }

        if (!$productInfo['cate_id']) {
            throw new AdminException('请选择商品分类');
        }

        if ($productInfo['spec_type'] == 1) {
            // 通过产品类型 Handler 读取规格数据验证图片（不再查询弃用的 eb_store_product_attr_result）
            try {
                $rules = app()->make(CenterProductServices::class)->getProductRules($id);
                $attrs = $rules['attrs'] ?? [];
                foreach ($attrs as $v) {
                    if (empty($v['pic'])) {
                        throw new AdminException('请上传商品图片');
                    }
                }
            } catch (\Throwable $e) {
                if ($e instanceof AdminException) throw $e;
                // 其他异常时跳过图片校验
            }
        } else {
            // 单规格检查中台主表 image 字段
            if (empty($productInfo['image'])) {
                throw new AdminException('请上传商品图片');
            }
        }
    }

    /**
     * 同步库存
     * @param array $items
     * @return void
     */
    public function syncStock(array $items)
    {
        return $this->transaction(function () use ($items) {
            $goods = [];
            // 通过事件代理查询并更新新 SKU 表（eb_pt_normal_sku）库存，避免 app 层直接引用 modules
            try {
                $barCodes = array_column($items, 'bar_code');
                $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumn', 'args' => [['bar_code' => $barCodes], 'id, product_id, bar_code, stock', 'bar_code']]);
                $list = proxy_event_result($results, []);

                foreach ($items as $item) {
                    $value = $list[$item['bar_code']] ?? [];
                    if (!$value) continue;
                    if (!isset($goods[$value['product_id']])) $goods[$value['product_id']] = 1;
                    // 逐条更新 SKU 库存
                    event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'update', 'args' => [(int)$value['id'], ['stock' => (int)$item['qty']]]]);
                }

                if ($goods) {
                    ProductStockJob::dispatch('distribute', [$goods]);
                }
            } catch (\Throwable $e) {
                // 旧表已弃用时静默跳过
            }
            return true;
        });
    }

    /**
     * 计算商品库存
     * @param int $id
     * @return void
     */
    public function calcStockByAttrValue(int $id)
    {
        return $this->transaction(function () use ($id) {
            // 通过新 SKU 表（eb_pt_normal_sku）计算总库存（不再查询弃用的 eb_store_product_attr_value）
            try {
                //通过事件代理查询 SKU 库存集合，避免 app 层直接引用 modules
                $results = event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'getColumnByProductId', 'args' => [$id, 'stock']]);
                $stocks = proxy_event_result($results, []);
                $stock = array_sum($stocks);
            } catch (\Throwable $e) {
                $stock = 0;
            }
            $res = $this->dao->update($id, ['stock' => $stock]);
            if (!$res) throw new AdminException('修改失败');
            return true;
        });
    }

    /**
     * 保存实物商品 SKU 到 eb_pt_normal_sku
     * 替代已弃用的 StoreProductAttrServices::saveProductAttr
     * @param int $productId
     * @param array $skuList validateProductAttr 返回的规格数据
     * @return bool
     */
    protected function saveProductSku(int $productId, array $skuList): bool
    {
        //通过事件代理删除商品原有 SKU，避免 app 层直接引用 modules
        event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'deleteByProductId', 'args' => [$productId]]);

        $valueGroup = $skuList['valueGroup'] ?? [];
        if (empty($valueGroup)) {
            return true;
        }

        $index = 0;
        foreach ($valueGroup as $sukText => $item) {
            $unique = $item['unique'] ?? '';
            if (!$unique) {
                $unique = strtolower(md5('normal_' . $productId . '_' . $index));
            }
            $skuData = [
                'product_id'        => $productId,
                'suk'               => $item['suk'] ?? $sukText,
                'price'             => $item['price'] ?? 0,
                'original_price'    => $item['ot_price'] ?? 0,
                'cost'              => $item['cost'] ?? 0,
                'vip_price'         => $item['vip_price'] ?? 0,
                'stock'             => $item['stock'] ?? 0,
                'image'             => $item['image'] ?? '',
                'unique_code'       => $unique,
                'brokerage'         => $item['brokerage'] ?? 0,
                'brokerage_two'     => $item['brokerage_two'] ?? 0,
                'is_default_select' => $item['is_default_select'] ?? ($index === 0 ? 1 : 0),
                'is_show'           => $item['is_show'] ?? 1,
                'bar_code'          => $item['bar_code'] ?? '',
                'bar_code_number'   => $item['bar_code_number'] ?? '',
                'weight'            => $item['weight'] ?? 0,
                'volume'            => $item['volume'] ?? 0,
                'unit_name'         => $item['unit_name'] ?? '',
            ];
            //通过事件代理保存 SKU 数据，避免 app 层直接引用 modules
            event('ProductTypeDataProxy', ['product_type' => 'normal', 'action' => 'save', 'args' => [$skuData]]);
            $index++;
        }
        return true;
    }

    /**
     * 检查商品是否有活动
     * 委托给 CenterProductServices，保持外部接口一致
     * @param int $id
     * @return bool
     */
    public function checkActivity(int $id): bool
    {
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        return $productServices->checkActivity($id);
    }

    /**
     * 删除/恢复商品（放入回收站）
     * 委托给 CenterProductServices，保持外部接口一致
     * @param int $id
     * @return string
     */
    public function del(int $id): string
    {
        /** @var CenterProductServices $productServices */
        $productServices = app()->make(CenterProductServices::class);
        return $productServices->del($id);
    }
}
