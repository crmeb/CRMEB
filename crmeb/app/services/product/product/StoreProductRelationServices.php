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

namespace app\services\product\product;


use app\dao\product\product\StoreProductRelationDao;
use app\services\BaseServices;
use app\jobs\ProductLogJob;
use crmeb\exceptions\ApiException;

/**
 * Class StoreProductRelationService
 * @package app\services\product\product
 */
class StoreProductRelationServices extends BaseServices
{
    /**
     * StoreProductRelationServices constructor.
     * @param StoreProductRelationDao $dao
     */
    public function __construct(StoreProductRelationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 用户是否点赞或收藏商品
     * @param array $where
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isProductRelation(array $where)
    {
        $res = $this->dao->getOne($where);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户收藏数量
     * @param int $uid
     * @return int
     */
    public function getUserCollectCount(int $uid)
    {
        return $this->dao->count(['uid' => $uid, 'tye' => 'collect']);
    }

    /**
     * @param int $uid
     * @return mixed
     */
    public function getUserCollectProduct(int $uid)
    {
        $where['uid'] = $uid;
        $where['type'] = 'collect';
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, 'product_id,category', $page, $limit);
        foreach ($list as $k => $product) {
            if ($product['product'] && isset($product['product']['id'])) {
                $list[$k]['pid'] = $product['product']['id'] ?? 0;
                $list[$k]['store_name'] = $product['product']['store_name'] ?? 0;
                $list[$k]['price'] = $product['product']['price'] ?? 0;
                $list[$k]['ot_price'] = $product['product']['ot_price'] ?? 0;
                $list[$k]['sales'] = $product['product']['sales'] ?? 0;
                $list[$k]['image'] = get_thumb_water($product['product']['image'] ?? 0);
                $list[$k]['is_del'] = $product['product']['is_del'] ?? 0;
                $list[$k]['is_show'] = $product['product']['is_show'] ?? 0;
                $list[$k]['is_fail'] = $product['product']['is_del'] && $product['product']['is_show'];
            } else {
                unset($list[$k]);
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 添加点赞 收藏
     * @param int $productId
     * @param int $uid
     * @param string $relationType
     * @param string $category
     * @return bool
     */
    public function productRelation(int $productId, int $uid, string $relationType, string $category = 'product')
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $data = ['uid' => $uid, 'product_id' => $productId, 'type' => $relationType, 'category' => $category];
        if ($this->dao->getOne($data)) {
            return true;
        }
        $data['add_time'] = time();
        if (!$this->dao->save($data)) {
            throw new ApiException(100006);
        }
        //收藏记录
        ProductLogJob::dispatch(['collect', ['uid' => $uid, 'product_id' => $productId]]);
        return true;
    }

    /**
     * 取消 点赞 收藏
     * @param array $productId
     * @param int $uid
     * @param string $relationType
     * @param string $category
     * @return bool
     * @throws \Exception
     */
    public function unProductRelation(array $productId, int $uid, string $relationType, string $category = 'product')
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $storeProductRelation = $this->dao->delete([
            ['uid', '=', $uid],
            ['product_id', 'in', $productId],
            ['type', '=', $relationType],
            ['category', '=', $category]
        ]);
        if (!$storeProductRelation) throw new ApiException(100020);
        return true;
    }

    /**
     * 批量 添加点赞 收藏
     * @param array $productIdS
     * @param int $uid
     * @param string $relationType
     * @param string $category
     * @return bool
     */
    public function productRelationAll(array $productIdS, int $uid, string $relationType, string $category = 'product')
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $relationData = [];
        $productIdS = array_unique($productIdS);
        $relationProductIdS = $this->dao->getColumn(['uid' => $uid, 'type' => $relationType, 'category' => $category, 'product_id' => $productIdS], 'product_id');
        foreach ($productIdS as $productId) {
            if (!in_array($productId, $relationProductIdS)) {
                $relationData[] = ['uid' => $uid, 'product_id' => $productId, 'type' => $relationType, 'category' => $category];
            }
        }
        if ($relationData) {
            if (!$this->dao->saveAll($relationData)) {
                throw new ApiException(100022);
            }
        }
        return true;
    }
}
