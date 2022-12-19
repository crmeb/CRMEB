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

namespace app\services\activity\advance;

use app\dao\activity\advance\StoreAdvanceDao;
use app\jobs\ProductLogJob;
use app\Request;
use app\services\BaseServices;
use app\services\order\StoreOrderServices;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreDescriptionServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\product\product\StoreProductServices;
use app\services\product\sku\StoreProductAttrResultServices;
use app\services\product\sku\StoreProductAttrServices;
use app\services\product\sku\StoreProductAttrValueServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\CacheService;

/**
 * 商品预售
 * Class StoreAdvanceServices
 * @package app\services\activity
 * @method get(int $id, array $field) 获取一条数据
 * @method getAdvanceStatus(array $ids) 获取预售商品是否开启
 */
class StoreAdvanceServices extends BaseServices
{
    /**
     * StoreAdvanceServices constructor.
     * @param StoreAdvanceDao $dao
     */
    public function __construct(StoreAdvanceDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 后台获取预售列表
     * @param $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, $page, $limit);
        $count = $this->dao->getCount($where);
        return compact('list', 'count');
    }

    /**
     * 保存预售数据
     * @param $id
     * @param $data
     */
    public function saveData($id, $data)
    {
        $description = $data['description'];
        $detail = $data['attrs'];
        $items = $data['items'];
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        $data['images'] = json_encode($data['images']);
        $data['price'] = min(array_column($detail, 'price'));
        $data['ot_price'] = min(array_column($detail, 'ot_price'));
        $data['cost'] = min(array_column($detail, 'cost'));
        $data['quota'] = $data['quota_show'] = array_sum(array_column($detail, 'quota'));
        $data['stock'] = array_sum(array_column($detail, 'stock'));
        if ($data['type']) {
            $data['pay_start_time'] = strtotime($data['pay_time'][0]);
            $data['pay_stop_time'] = strtotime($data['pay_time'][1]);
        }
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
                $storeDescriptionServices->saveDescription((int)$id, $description, 6);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$id, 6);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$id, 6);
                if (!$res) throw new AdminException(100007);
            } else {
                if (!$storeProductServices->getOne(['is_show' => 1, 'is_del' => 0, 'id' => $data['product_id']])) {
                    throw new AdminException(400091);
                }
                $data['add_time'] = time();
                $res = $this->dao->save($data);
                $storeProductServices->update($data['product_id'], ['is_show' => 0]);
                $storeDescriptionServices->saveDescription((int)$res->id, $description, 6);
                $skuList = $storeProductServices->validateProductAttr($items, $detail, (int)$res->id, 6);
                $valueGroup = $storeProductAttrServices->saveProductAttr($skuList, (int)$res->id, 6);
                if (!$res) throw new AdminException(100022);
            }
            $res = true;
            foreach ($valueGroup->toArray() as $item) {
                $res = $res && CacheService::setStock($item['unique'], (int)$item['quota_show'], 6);
            }
            if (!$res) {
                throw new AdminException(400092);
            }
        });
    }

    /**
     * 获取预售详情
     * @param int $id
     * @return array|\think\Model|null
     */
    public function getInfo(int $id)
    {
        $info = $this->dao->get($id);
        if ($info) {
            if ($info['start_time'] && $info['stop_time']) {
                $start_time = date('Y-m-d H:i', (int)$info['start_time']);
                $stop_time = date('Y-m-d H:i', (int)$info['stop_time']);
            }
            if (isset($start_time) && isset($stop_time)) {
                $info['section_time'] = [$start_time, $stop_time];
            } else {
                $info['section_time'] = [];
            }
            if ($info['pay_start_time'] && $info['pay_stop_time']) {
                $start_time = date('Y-m-d H:i', (int)$info['pay_start_time']);
                $stop_time = date('Y-m-d H:i', (int)$info['pay_stop_time']);
            }
            if (isset($start_time) && isset($stop_time)) {
                $info['pay_time'] = [$start_time, $stop_time];
            } else {
                $info['pay_time'] = [];
            }
            $info['price'] = floatval($info['price']);
            $info['ot_price'] = floatval($info['ot_price']);
            /** @var StoreDescriptionServices $storeDescriptionServices */
            $storeDescriptionServices = app()->make(StoreDescriptionServices::class);
            $info['description'] = $storeDescriptionServices->getDescription(['product_id' => $id, 'type' => 6]);
            $info['attrs'] = $this->attrList($id, $info['product_id']);
        }
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
        $advanceResult = $storeProductAttrResultServices->value(['product_id' => $id, 'type' => 6], 'result');
        $items = json_decode($advanceResult, true)['attr'];
        $productAttr = $this->getAttr($items, $pid, 0);
        $advanceAttr = $this->getAttr($items, $id, 6);
        foreach ($productAttr as $pk => $pv) {
            foreach ($advanceAttr as &$sv) {
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
        $header[] = ['title' => '预售价', 'key' => 'price', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '成本价', 'key' => 'cost', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '原价', 'key' => 'ot_price', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '库存', 'key' => 'stock', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '限量', 'key' => 'quota', 'type' => 1, 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '重量(KG)', 'key' => 'weight', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '体积(m³)', 'key' => 'volume', 'align' => 'center', 'minWidth' => 80];
        $header[] = ['title' => '商品编号', 'key' => 'bar_code', 'align' => 'center', 'minWidth' => 80];
        $attrs['header'] = $header;
        return $attrs;
    }

    /**
     * 获取规格
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
            $sukValue = $storeProductAttrValueServices->getColumn(['product_id' => $id, 'type' => $type, 'suk' => $suk], 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
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
                $valueNew[$count]['quota'] = $sukValue[$suk]['quota'] ? intval($sukValue[$suk]['quota']) : 0;
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
     * 商品详情
     * @param Request $request
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAdvanceinfo(Request $request, int $id)
    {
        $uid = (int)$request->uid();
        $storeInfo = $this->dao->getOne(['id' => $id], '*', ['description']);
        if (!$storeInfo) {
            throw new ApiException(410294);
        } else {
            $storeInfo = $storeInfo->toArray();
        }
        $siteUrl = sys_config('site_url');
        $storeInfo['image'] = set_file_url($storeInfo['image'], $siteUrl);
        $storeInfo['image_base'] = set_file_url($storeInfo['image'], $siteUrl);
        if (time() < $storeInfo['start_time']) {
            $data['pay_status'] = 1;
        } elseif (time() > $storeInfo['stop_time']) {
            $data['pay_status'] = 3;
        } else {
            $data['pay_status'] = 2;
        }
        $storeInfo['start_time'] = date('Y-m-d H:i:s', (int)$storeInfo['start_time']);
        $storeInfo['stop_time'] = date('Y-m-d H:i:s', (int)$storeInfo['stop_time']);

        /** @var StoreProductServices $storeProductService */
        $storeProductService = app()->make(StoreProductServices::class);
        $productInfo = $storeProductService->get($storeInfo['product_id']);
        $storeInfo['total'] = $productInfo['sales'] + $productInfo['ficti'];
        $storeInfo['store_name'] = $storeInfo['title'];
        $storeInfo['store_info'] = $storeInfo['info'];

        /** @var QrcodeServices $qrcodeService */
        $qrcodeService = app()->make(QrcodeServices::class);
        $storeInfo['code_base'] = $qrcodeService->getWechatQrcodePath($id . '_product_advance_detail_wap.jpg', 'pages/activity/presell_details/index?id=' . $id);

        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $data['buy_num'] = $storeOrderServices->getBuyCount($uid, 'advance_id', $id);

        /** @var StoreProductRelationServices $storeProductRelationServices */
        $storeProductRelationServices = app()->make(StoreProductRelationServices::class);
        $storeInfo['userCollect'] = $storeProductRelationServices->isProductRelation(['uid' => $uid, 'product_id' => $storeInfo['product_id'], 'type' => 'collect', 'category' => 'product']);
        $storeInfo['userLike'] = false;
        $storeInfo['uid'] = $uid;

        if ($storeInfo['quota'] > 0) {
            $percent = (int)(($storeInfo['quota_show'] - $storeInfo['quota']) / $storeInfo['quota_show'] * 100);
            $storeInfo['percent'] = $percent;
            $storeInfo['stock'] = $storeInfo['quota'];
        } else {
            $storeInfo['percent'] = 100;
            $storeInfo['stock'] = 0;
        }
        //商品详情
        $data['storeInfo'] = get_thumb_water($storeInfo, 'big', ['image', 'images']);

        /** @var StoreProductReplyServices $storeProductReplyService */
        $storeProductReplyService = app()->make(StoreProductReplyServices::class);
        $data['reply'] = get_thumb_water($storeProductReplyService->getRecProductReply($storeInfo['product_id']), 'small', ['pics']);
        [$replyCount, $goodReply, $replyChance] = $storeProductReplyService->getProductReplyData((int)$storeInfo['product_id']);
        $data['replyChance'] = $replyChance;
        $data['replyCount'] = $replyCount;

        /** @var StoreProductAttrServices $storeProductAttrServices */
        $storeProductAttrServices = app()->make(StoreProductAttrServices::class);
        list($productAttr, $productValue) = $storeProductAttrServices->getProductAttrDetail($id, $uid, 0, 6, $storeInfo['product_id']);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        $data['routine_contact_type'] = sys_config('routine_contact_type', 0);
        //用户访问事件
        event('user.userVisit', [$uid, $id, 'advance', $storeInfo['product_id'], 'view']);
        //浏览记录
        ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $storeInfo['product_id']]]);
        return $data;
    }

    /**
     * 修改预售库存
     * @param int $num
     * @param int $advanceId
     * @return bool
     */
    public function decAdvanceStock(int $num, int $advanceId, string $unique = '')
    {
        $product_id = $this->dao->value(['id' => $advanceId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去预售商品的sku库存增加销量
            $res = false !== $skuValueServices->decProductAttrStock($advanceId, $unique, $num, 6);
            //减去预售库存
            $res = $res && $this->dao->decStockIncSales(['id' => $advanceId, 'type' => 6], $num);
            //获取预售的sku
            $sku = $skuValueServices->value(['product_id' => $advanceId, 'unique' => $unique, 'type' => 6], 'suk');
            //减去当前普通商品sku的库存增加销量
            $res = $res && $skuValueServices->decStockIncSales(['product_id' => $product_id, 'suk' => $sku, 'type' => 0], $num);
        } else {
            $res = false !== $this->dao->decStockIncSales(['id' => $advanceId, 'type' => 6], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减去普通商品库存
        $res = $res && $services->decProductStock($num, $product_id);
        return $res;
    }

    /**
     * 减销量加库存
     * @param int $num
     * @param int $advanceId
     * @param string $unique
     * @return bool
     */
    public function incAdvanceStock(int $num, int $advanceId, string $unique = '')
    {
        $product_id = $this->dao->value(['id' => $advanceId], 'product_id');
        if ($unique) {
            /** @var StoreProductAttrValueServices $skuValueServices */
            $skuValueServices = app()->make(StoreProductAttrValueServices::class);
            //减去砍价商品sku的销量,增加库存和限购数量
            $res = false !== $skuValueServices->incProductAttrStock($advanceId, $unique, $num, 6);
            //减去砍价商品的销量,增加库存
            $res = $res && $this->dao->incStockDecSales(['id' => $advanceId, 'type' => 6], $num);
            //减掉普通商品sku的销量,增加库存
            $suk = $skuValueServices->value(['unique' => $unique, 'product_id' => $advanceId, 'type' => 6], 'suk');
            $productUnique = $skuValueServices->value(['suk' => $suk, 'product_id' => $product_id, 'type' => 0], 'unique');
            if ($productUnique) {
                $res = $res && $skuValueServices->incProductAttrStock($product_id, $productUnique, $num);
            }
        } else {
            //减去砍价商品的销量,增加库存
            $res = false !== $this->dao->incStockDecSales(['id' => $advanceId, 'type' => 6], $num);
        }
        /** @var StoreProductServices $services */
        $services = app()->make(StoreProductServices::class);
        //减掉普通商品的库存加销量
        $res = $res && $services->incProductStock($num, $product_id);
        return $res;
    }

    /**
     * 验证预售下单库存限量
     * @param int $uid
     * @param int $combinationId
     * @param int $cartNum
     * @param string $unique
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function checkAdvanceStock(int $uid, int $advanceId, int $cartNum = 1, string $unique = '')
    {
        $productInfo = $this->dao->getOne(['id' => $advanceId, 'status' => 1, 'is_del' => 0], '*,title as store_name');
        if (!$productInfo) throw new ApiException(400093);
        /** @var StoreProductAttrValueServices $attrValueServices */
        $attrValueServices = app()->make(StoreProductAttrValueServices::class);
        if ($unique == '') {
            $unique = $attrValueServices->value(['product_id' => $advanceId, 'type' => 6], 'unique');
        }
        $attrInfo = $attrValueServices->getOne(['product_id' => $advanceId, 'unique' => $unique, 'type' => 6]);
        if (!$attrInfo || $attrInfo['product_id'] != $advanceId) {
            throw new ApiException(400094);
        }
        /** @var StoreOrderServices $orderServices */
        $orderServices = app()->make(StoreOrderServices::class);
        $userBuyCount = $orderServices->getBuyCount($uid, 'advance_id', $advanceId);
        if ($productInfo['num'] < ($userBuyCount + $cartNum)) {
            throw new ApiException(410298, ['num' => $productInfo['num']]);
        }
        if ($productInfo['start_time'] > time()) throw new ApiException(410321);
        if ($productInfo['stop_time'] < time()) throw new ApiException(410322);
        if ($cartNum > $attrInfo['quota']) {
            throw new ApiException(410297, ['num' => $cartNum]);
        }
        return [$attrInfo, $unique, $productInfo];
    }
}
