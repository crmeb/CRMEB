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
declare (strict_types=1);

namespace app\services\activity\integral;

use app\Request;
use app\services\BaseServices;
use app\dao\activity\integral\StoreIntegralDao;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductServices;
use app\services\product\product\StoreVisitServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\jobs\ProductLogJob;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 *
 * Class StoreIntegralServices
 * @package app\services\activity
 * @method getOne(array $where, ?string $field = '*', array $with = []) 根据条件获取一条数据
 * @method get(int $id, ?array $field) 获取一条数据
 */
class StoreIntegralServices extends BaseServices
{
    const THODLCEG = 'ykGUKB';

    /**
     * StoreIntegralServices constructor.
     * @param StoreIntegralDao $dao
     */
    public function __construct(StoreIntegralDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取指定条件下的条数
     * @param array $where
     */
    public function getCount(array $where)
    {
        $this->dao->count($where);
    }

    /**
     * 积分商品添加
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id, array $data)
    {
        if ($data['num'] < $data['once_num']) {
            throw new AdminException(400500);
        }
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        unset($data['section_time'], $data['description'], $data['attrs'], $data['items']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        /** @var StoreProductServices $storeProductServices */
        $storeProductServices = app()->make(StoreProductServices::class);
        if ($data['quota'] > $storeProductServices->value(['id' => $data['product_id']], 'stock')) {
            throw new AdminException(400090);
        }
        $this->transaction(function () use ($id, $data, $description, $detail, $items, $storeDescriptionServices, $storeProductAttrServices, $storeProductServices) {
            if ($id) {
                $res = $this->dao->update($id, $data);
                $storeDescriptionServices->saveDescription((int)$id, $description, 4);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$id, 4);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 4);
                if (!$res) throw new AdminException(100007);
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException(400091);
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, 4);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$res->id, 4);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$res->id, 4);
                if (!$res) throw new AdminException(100022);
            }
            $res = true;
            foreach ($valueGroup->toArray() as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 4);
            }
            if (!$res) {
                throw new AdminException(400092);
            }
        });
    }

    /**
     * 批量添加商品
     * @param array $data
     */
    public function saveBatchData(array $data)
    {

        /** @var StoreProductServices $service */
        $service = app()->make(StoreProductServices::class);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        if (!$data) {
            throw new AdminException(400337);
        }
        if(!$data['attrs']) throw new AdminException(400337);
        $attrs = [];
        foreach ($data['attrs'] as $k => $v) {
            $attrs[$v['product_id']][] = $v;
        }
        foreach ($attrs as $k => $v) {
            $productInfo = $service->getOne(['id' => $k]);
            $productInfo = is_object($productInfo) ? $productInfo->toArray() : [];
            if ($productInfo) {
                $product = [];
                $result = $storeProductAttrResultServices->getResult(['product_id' => $productInfo['id'], 'type' => 0]);
                $product['product_id'] = $productInfo['id'];
                $product['description'] = $storeDescriptionServices->getDescription(['product_id' => $productInfo['id'], 'type' => 0]);
                $product['attrs'] = $v;
                $product['items'] = $result['attr'];
                $product['is_show'] = $data['is_show'] ?? 0;
                $product['title'] = $productInfo['store_name'];
                $product['unit_name'] = $productInfo['unit_name'];
                $product['image'] = $productInfo['image'];
                $product['images'] = $productInfo['slider_image'];
                $product['num'] = 0;
                $product['is_host'] = 0;
                $product['once_num'] = 0;
                $product['sort'] = 0;
                $this->saveData(0, $product);
            }
        }
        return true;
    }

    /**
     * 积分商品列表
     * @param array $where
     * @return array
     */
    public function systemPage(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(400533);
        }
        if ($info->is_del) {
            throw new AdminException(400534);
        }
        $info['price'] = floatval($info['price']);
        /** @var StoreDescriptionServices $storeDescriptionServices */
        $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
        $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 4]);
        $info['attrs'] = $this->attrList($id, $info['product_id']);
        return $info;
    }

    /**
     * 获取规格
     * @param int $id
     * @param int $pid
     * @return mixed
     */
    public function attrList(int $id, int $pid)
    {
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        $combinationResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 4], 'result');
        $items = json_decode($combinationResult, true)['attr'];
        $productAttr = $this->getAttr($items, $pid, 0);
        $combinationAttr = $this->getAttr($items, $id, 4);
        foreach ($productAttr as $pk => $pv) {
            foreach ($combinationAttr as &$sv) {
                if ($pv['detail'] == $sv['detail']) {
                    $productAttr[$pk] = $sv;
                }
            }
            $productAttr[$pk]['detail'] = json_decode($productAttr[$pk]['detail']);
        }
        $attrs['items'] = $items;
        $attrs['value'] = $productAttr;
        foreach ($items as $key => $item) {
            $header[] = ['title' => $item['value'], 'key' => 'value' . ($key + 1), 'align' => 'center', 'minWidth' => 80];
        }
        $header[] = ['title' => '图片', 'slot' => 'pic', 'align' => 'center', 'minWidth' => 120];
        $header[] = ['title' => '兑换积分', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '兑换次数', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获得规格
     * @param $attr
     * @param $id
     * @param $type
     * @return array
     */
    public function getAttr($attr, $id, $type)
    {
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        list($value, $head) = attr_format($attr);
        $valueNew = [];
        $count = 0;
        foreach ($value as $suk) {
            $detail = explode(',', $suk);
            $sukValue = $storeProductAttrValueServices->getColumn(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota,quota_show', 'suk');
            if (count($sukValue)) {
                foreach ($detail as $k => $v) {
                    $valueNew[$count]['value' . ($k + 1)] = $v;
                }
                $valueNew[$count]['detail'] = json_encode(array_combine($head, $detail));
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'] ?? '';
                $valueNew[$count]['price'] = $sukValue[$suk]['price'] ? floatval($sukValue[$suk]['price']) : 0;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'] ? floatval($sukValue[$suk]['cost']) : 0;
                $valueNew[$count]['ot_price'] = isset($sukValue[$suk]['ot_price']) ? floatval($sukValue[$suk]['ot_price']) : 0;
                $valueNew[$count]['stock'] = $sukValue[$suk]['stock'] ? intval($sukValue[$suk]['stock']) : 0;
//                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
                $valueNew[$count]['quota'] = isset($sukValue[$suk]['quota_show']) && $sukValue[$suk]['quota_show'] ? intval($sukValue[$suk]['quota_show']) : 0;
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['weight'] = $sukValue[$suk]['weight'] ? floatval($sukValue[$suk]['weight']) : 0;
                $valueNew[$count]['volume'] = $sukValue[$suk]['volume'] ? floatval($sukValue[$suk]['volume']) : 0;
                $valueNew[$count]['brokerage'] = $sukValue[$suk]['brokerage'] ? floatval($sukValue[$suk]['brokerage']) : 0;
                $valueNew[$count]['brokerage_two'] = $sukValue[$suk]['brokerage_two'] ? floatval($sukValue[$suk]['brokerage_two']) : 0;
                $valueNew[$count]['_checked'] = $type != 0;
                $count++;
            }
        }
        return $valueNew;
    }

    /**
     * 积分商品详情
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function integralDetail(Request $request, int $id)
    {
        $storeInfo = $this->dao->getOne(['id' => $id], '*', ['getPrice']);
        if (!$storeInfo) {
            throw new AdminException(400533);
        } else {
            $storeInfo = $storeInfo->toArray();
        }
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['sale_stock'] = 0;
        if ($storeInfo['stock'] > 0) $storeInfo['sale_stock'] = 1;
        $uid = $request->uid();
        /** @var StoreDescriptionServices $storeDescriptionService */
        $storeDescriptionService = app()->make(StoreDescriptionServices::class);
        $storeInfo['description'] = $storeDescriptionService->getDescription(['product_id' => $id, 'type' => 4]);
        $data['storeInfo'] = get_thumb_water($storeInfo, 'big', ['image', 'images']);
        $storeInfoNew = get_thumb_water($storeInfo, 'small');
        $data['storeInfo']['small_image'] = $storeInfoNew['image'];

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($id, $uid, 0, 4, $storeInfo['product_id']);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        /** @var StoreVisitServices $storeVisit */
        $storeVisit = app()->make(StoreVisitServices::class);
        $storeVisit->setView($uid, $id, 'combination', $storeInfo['product_id'], 'view');
        $data['routine_contact_type'] = sys_config('routine_contact_type', 0);
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $storeInfo['product_id']]]);
        return $data;
    }

    /**
     * 修改销量和库存
     * @param $num
     * @param $integralId
     * @return bool
     */
    public function decIntegralStock(int $num, int $integralId, string $unique)
    {
        $product_id = $this->dao->value(['id' => $integralId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去积分商品的sku库存增加销量
            $res = false !== $skuValueServices->decProductAttrStock($integralId, $unique, $num, 4);
            //减去积分商品库存
            $res = $res && $this->dao->decStockIncSales(['id' => $integralId, 'type' => 4], $num);
            //获取拼团的sku
            $sku = $skuValueServices->value(['product_id' => $integralId, 'unique' => $unique, 'type' => 4], 'suk');
            //减去当前普通商品sku的库存增加销量
            $res = $res && $skuValueServices->decStockIncSales(['product_id' => $product_id, 'suk' => $sku, 'type' => 0], $num);
        } else {
            $res = false !== $this->dao->decStockIncSales(['id' => $integralId, 'type' => 4], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减去普通商品库存
        $res = $res && $services->decProductStock($num, $product_id);
        return $res;
    }

    /**
     * 获取一条积分商品
     * @param $id
     * @return mixed
     */
    public function getIntegralOne($id)
    {
        return $this->dao->validProduct($id, '*');
    }

    /**
     * 验证积分商品下单库存限量
     * @param int $uid
     * @param int $integralId
     * @param int $num
     * @param string $unique
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkoutProductStock(int $uid, int $integralId, int $num = 1, string $unique = '')
    {
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $integralId, 'type' => 4], 'unique');
        }
        $StoreIntegralInfo = $this->getIntegralOne($integralId);
        if (!$StoreIntegralInfo) {
            throw new ApiException(400093);
        }
        /** @var StoreIntegralOrderServices $orderServices */
        $orderServices = app()->make(StoreIntegralOrderServices::class);
        $userBuyCount = $orderServices->getBuyCount($uid, $integralId);
        if ($StoreIntegralInfo['once_num'] < $num && $StoreIntegralInfo['once_num'] != -1) {
            throw new ApiException(410313, ['num' => $StoreIntegralInfo['once_num']]);
        }
        if ($StoreIntegralInfo['num'] < ($userBuyCount + $num) && $StoreIntegralInfo['num'] != -1) {
            throw new ApiException(410298, ['num' => $StoreIntegralInfo['num']]);
        }
        $res = $attrValueServices->getOne(['product_id' => $integralId, 'unique' => $unique, 'type' => 4]);
        if ($num > $res['quota']) {
            throw new ApiException(410297, ['num' => $num]);
        }
        $product_stock = $attrValueServices->value(['product_id' => $StoreIntegralInfo['product_id'], 'suk' => $res['suk'], 'type' => 0], 'stock');
        if ($product_stock < $num) {
            throw new ApiException(410297, ['num' => $num]);
        }
        if (!CacheService::checkStock($unique, $num, 4)) {
            throw new ApiException(410297, ['num' => $num]);
        }
        return $unique;
    }

    /**
     * 获取推荐积分商品
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getIntegralList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $page, $limit, 'id,image,title,price,sales');
        return $list;
    }
}
