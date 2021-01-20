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

namespace app\services\kefu;


use app\services\BaseServices;
use app\services\product\product\StoreProductCateServices;
use think\exception\ValidateException;
use app\dao\product\product\StoreProductDao;
use app\services\order\StoreOrderStoreOrderCartInfoServices;
use app\services\product\product\StoreProductVisitServices;

/**
 * Class ProductServices
 * @package app\services\kefu
 */
class ProductServices extends BaseServices
{


    /**
     * ProductServices constructor.
     * @param StoreProductDao $dao
     */
    public function __construct(StoreProductDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取用户购买记录
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductCartList(int $uid, string $storeName = '')
    {
        [$page, $limit] = $this->getPageValue();
        /** @var StoreOrderStoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderStoreOrderCartInfoServices::class);
        $where['id'] = $services->getUserCartProductIds(['uid' => $uid]);
        $where['store_name'] = $storeName;
        return $this->dao->getProductCartList($where, $page, $limit, ['id', 'IFNULL(sales,0) + IFNULL(ficti,0) as sales', 'store_name', 'image', 'stock', 'price']);
    }

    /**
     * 获取用户浏览足记
     * @param int $uid
     * @return mixed
     */
    public function getVisitProductList(int $uid, string $storeName = '')
    {
        [$page, $limit] = $this->getPageValue();
        /** @var StoreProductVisitServices $service */
        $service = app()->make(StoreProductVisitServices::class);
        return $service->getUserVisitProductList(['uid' => $uid, 'store_name' => $storeName], $page, $limit);
    }

    /**
     * 获取热销商品前20
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductHotSale(int $uid, string $storeName = '')
    {
        /** @var StoreOrderStoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderStoreOrderCartInfoServices::class);
        $productIds = $services->getUserCartProductIds(['uid' => $uid]);
        /** @var StoreProductCateServices $cateService */
        $cateService = app()->make(StoreProductCateServices::class);
        $where['id'] = $cateService->cateIdByProduct($cateService->productIdByCateId($productIds));
        $where['store_name'] = $storeName;
        return $this->dao->getUserProductHotSale($where);
    }

    /**
     * 获取商品详情
     * @param int $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getProductInfo(int $id)
    {
        $productInfo = $this->dao->get($id, ['store_name', 'IFNULL(sales,0) + IFNULL(ficti,0) as sales', 'image',
            'slider_image', 'price', 'vip_price', 'ot_price', 'stock', 'id'], ['description']);
        if (!$productInfo) {
            throw new ValidateException('商品未查到');
        }
        return $productInfo->toArray();
    }
}
