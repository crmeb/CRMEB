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

namespace app\services\product\sku;


use app\dao\product\sku\StoreProductAttrValueDao;
use app\models\store\StoreProductAttrValue;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use app\services\product\product\StoreProductServices;
use crmeb\services\CacheService;
use crmeb\services\workerman\ChannelService;

/**
 * Class StoreProductAttrValueService
 * @package app\services\product\sku
 * @method getProductAttrValue(array $where)
 * @method value(array $where, string $field = '') 获取单个键值的数据
 * @method decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存加销量
 * @method count(array $where) 获取指定条件下的数量
 */
class StoreProductAttrValueServices extends BaseServices
{
    /**
     * StoreProductAttrValueServices constructor.
     * @param StoreProductAttrValueDao $dao
     */
    public function __construct(StoreProductAttrValueDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取单规格规格
     * @param array $where
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOne(array $where)
    {
        return $this->dao->getOne($where);
    }

    /**
     * 获取指定条件下的数据以数组返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getColumn(array $where, string $field = '*', string $key = 'suk')
    {
        return $this->dao->getColumn($where, $field, $key);
    }

    /**
     * 删除一条数据
     * @param int $id
     * @param int $type
     */
    public function del(int $id, int $type)
    {
        $this->dao->del($id, $type);
    }

    /**
     * 批量保存
     * @param array $data
     */
    public function saveAll(array $data)
    {
        $res = $this->dao->saveAll($data);
        if (!$res) throw new AdminException(100006);
        return $res;
    }

    /**
     * 获取sku
     * @param array $where
     * @return array
     */
    public function getSkuArray(array $where)
    {
        return $this->dao->getColumn($where, 'bar_code,cost,price,ot_price,stock,image as pic,weight,volume,brokerage,brokerage_two,quota', 'suk');
    }

    /**
     * 交易排行榜
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function purchaseRanking()
    {
        $dlist = $this->dao->attrValue();
        /** @var StoreProductServices $proServices */
        $proServices = app()->make(StoreProductServices::class);
        $slist = $proServices->getProductLimit(['is_del' => 0], $limit = 20, 'id as product_id,store_name,sales * price as val');
        $data = array_merge($dlist, $slist);
        $last_names = array_column($data, 'val');
        array_multisort($last_names, SORT_DESC, $data);
        $list = array_splice($data, 0, 20);
        return $list;
    }

    /**获取商品的属性数量
     * @param $product_id
     * @param $unique
     * @param $type
     * @return int
     */
    public function getAttrvalueCount($product_id, $unique, $type)
    {
        return $this->dao->count(['product_id' => $product_id, 'unique' => $unique, 'type' => $type]);
    }

    /**
     * 获取唯一值下的库存
     * @param string $unique
     * @return int
     */
    public function uniqueByStock(string $unique)
    {
        if (!$unique) return 0;
        return $this->dao->uniqueByStock($unique);
    }

    /**
     * 减销量,加库存
     * @param $productId
     * @param $unique
     * @param $num
     * @param int $type
     * @return mixed
     */
    public function decProductAttrStock($productId, $unique, $num, $type = 0)
    {
        $res = $this->dao->decStockIncSales([
            'product_id' => $productId,
            'unique' => $unique,
            'type' => $type
        ], $num);
        if ($res) {
            $this->workSendStock($productId, $unique, $type);
        }
        return $res;
    }

    /**
     * 减少销量增加库存
     * @param $productId
     * @param $unique
     * @param $num
     * @return bool
     */
    public function incProductAttrStock(int $productId, string $unique, int $num, int $type = 0)
    {
        return $this->dao->incStockDecSales(['unique' => $unique, 'product_id' => $productId, 'type' => $type], $num);
    }

    /**
     * 库存预警消息提醒
     * @param int $productId
     * @param string $unique
     * @param int $type
     */
    public function workSendStock(int $productId, string $unique, int $type)
    {
        $stock = $this->dao->value([
            'product_id' => $productId,
            'unique' => $unique,
            'type' => $type
        ], 'stock');
        $replenishment_num = sys_config('store_stock') ?? 0;//库存预警界限
        if ($replenishment_num >= $stock) {
            try {
                ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * 获取秒杀库存
     * @param int $productId
     * @param string $unique
     * @param bool $isNew
     * @return array|mixed|\think\Model|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSeckillAttrStock(int $productId, string $unique, bool $isNew = false)
    {
        $key = md5('seclkill_attr_stock_' . $productId . '_' . $unique);
        $stock = CacheService::get($key);
        if (!$stock || $isNew) {
            $stock = $this->dao->getOne(['product_id' => $productId, 'unique' => $unique, 'type' => 1], 'suk,quota');
            if ($stock) {
                CacheService::set($key, $stock, 60);
            }
        }
        return $stock;
    }

    /**
     * @param $product_id
     * @param string $suk
     * @param string $unique
     * @param bool $is_new
     * @return int|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getProductAttrStock(int $productId, string $suk = '', string $unique = '', $isNew = false)
    {
        if (!$suk && !$unique) return 0;
        $key = md5('product_attr_stock_' . $productId . '_' . $suk . '_' . $unique);
        $stock = CacheService::get($key);
        if (!$stock || $isNew) {
            $where = ['product_id' => $productId, 'type' => 0];
            if ($suk) {
                $where['suk'] = $suk;
            }
            if ($unique) {
                $where['unique'] = $unique;
            }
            $stock = $this->dao->value($where, 'stock');
            CacheService::set($key, $stock, 60);
        }
        return $stock;
    }
}
