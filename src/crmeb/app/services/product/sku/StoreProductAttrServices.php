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

namespace app\services\product\sku;


use app\dao\product\sku\StoreProductAttrDao;
use app\services\BaseServices;
use app\services\order\StoreCartServices;
use app\services\product\product\StoreProductServices;
use crmeb\exceptions\AdminException;
use think\console\command\Make;

/**
 * Class StoreProductAttrService
 * @package app\services\product\sku
 */
class StoreProductAttrServices extends BaseServices
{
    /**
     * StoreProductAttrServices constructor.
     * @param StoreProductAttrDao $dao
     */
    public function __construct(StoreProductAttrDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存商品规格
     * @param array $data
     * @param int $id
     * @param int $type
     * @return bool
     */
    public function saveProductAttr(array $data, int $id, int $type = 0, $is_vip = 0)
    {
        /** @var StoreProductAttrResultServices $storeProductAttrResultServices */
        $storeProductAttrResultServices = app()->make(StoreProductAttrResultServices::class);
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        /** @var StoreProductServices $storeProductService */
        $storeProductService = app()->make(StoreProductServices::class);
        $this->dao->del($id, $type);
        $storeProductAttrResultServices->del($id, $type);
        $storeProductAttrValueServices->del($id, $type);
        $this->dao->saveAll($data['attrGroup']);
        $storeProductAttrResultServices->setResult($data['result'], $id, $type);
        $proudctVipPrice = 0;
        if (isset($data['valueGroup']) && $data['valueGroup']) {
            $detailTemp = array_column($data['valueGroup'], 'vip_price');
            if ($detailTemp) {
                if ($is_vip && in_array(0, $detailTemp)) throw new AdminException('会员价格不能为0');
                $detailTemp = array_diff($detailTemp, [0]);
                if ($detailTemp) {

                    $proudctVipPrice = min($detailTemp);
                }
            }
            $storeProductService->update($id, ['vip_price' => $proudctVipPrice]);
        }

        return $storeProductAttrValueServices->saveAll($data['valueGroup']);
    }

    /**
     * 获取商品规格
     * @param array $where
     * @return array
     */
    public function getProductAttr(array $where)
    {
        return $this->dao->getProductAttr($where);
    }

    /**
     * 获取商品规格详情
     * @param int $id
     * @param int $uid
     * @param int $type
     * @param int $typeId
     * @param int $productId
     * @return array
     */
    public function getProductAttrDetail(int $id, int $uid, int $type, int $typeId = 0, int $productId = 0)
    {
        $attrDetail = $this->dao->getProductAttr(['product_id' => $id, 'type' => $typeId]);
        /** @var StoreProductAttrValueServices $storeProductAttrValueService */
        $storeProductAttrValueService = app()->make(StoreProductAttrValueServices::class);
        /** @var StoreProductServices $storeProductService */
        $storeProductService = app()->make(StoreProductServices::class);
        $_values = $storeProductAttrValueService->getProductAttrValue(['product_id' => $id, 'type' => $typeId]);
        if ($productId == 0) $productId = $id;
        $is_vip = $storeProductService->get($productId, ['is_vip']);
        $stock = $storeProductAttrValueService->getColumn(['product_id' => $productId, 'type' => 0], 'stock', 'suk');
        $values = [];
        $cartNumList = [];
        if ($uid) {
            /** @var StoreCartServices $storeCartService */
            $storeCartService = app()->make(StoreCartServices::class);
            $cartNumList = $storeCartService->getUserCartNums(array_column($_values, 'unique'), $id, $uid);
        }
        foreach ($_values as $value) {
            if ($type) {
                if ($uid)
                    $value['cart_num'] = $cartNumList[$value['unique']];
                else
                    $value['cart_num'] = 0;
                if (is_null($value['cart_num'])) $value['cart_num'] = 0;
            }
            $value['vip_price'] = 0;
            $value['product_stock'] = $stock[$value['suk']] ?? 0;
            $values[$value['suk']] = $value;
        }
        foreach ($attrDetail as $k => $v) {
            $attr = $v['attr_values'];
            foreach ($attr as $kk => $vv) {
                $attrDetail[$k]['attr_value'][$kk]['attr'] = $vv;
                $attrDetail[$k]['attr_value'][$kk]['check'] = false;
            }
        }
        return [$attrDetail, $values];
    }

}
