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
declare (strict_types=1);

namespace app\services\live;


use app\dao\live\LiveGoodsDao;
use app\services\BaseServices;
use app\services\product\product\StoreProductServices;
use crmeb\exceptions\AdminException;
use crmeb\services\MiniProgramService;
use crmeb\services\DownloadImageService;
use crmeb\utils\Str;
use think\facade\Log;

/**
 * Class LiveGoodsServices
 * @package app\services\live
 */
class LiveGoodsServices extends BaseServices
{
    /**
     * LiveGoodsServices constructor.
     * @param LiveGoodsDao $dao
     */
    public function __construct(LiveGoodsDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, '*', ['product'], $page, $limit);
        $count = $this->dao->count($where);
        return compact('count', 'list');
    }

    public function create(array $product_ids)
    {
        /** @var StoreProductServices $product */
        $productServices = app()->make(StoreProductServices::class);
        $products = $productServices->getColumn([['id', 'IN', $product_ids], ['is_del', '=', 0], ['is_show', '=', 1]], 'id,image,store_name,price,price as cost_price,stock', 'id');
        if (count($product_ids) != count($products)) {
            throw new AdminException('已选商品中包括已下架或移入回收站商品');
        }
        $checkGoods = $this->dao->getCount([['product_id', 'IN', $product_ids], ['is_del', '=', 0], ['audit_status', '<>', 3]]);
        if ($checkGoods > 0) {
            throw new AdminException('其中有商品已经添加');
        }
        return array_merge($products);
    }

    /**
     * @param array $data
     * @return bool|mixed
     * @throws \EasyWeChat\Core\Exceptions\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(array $goods_info)
    {
        $product_ids = array_column($goods_info, 'id');
        $this->create($product_ids);
        $miniUpload = MiniProgramService::materialTemporaryService();
        /** @var DownloadImageService $download */
        $download = app()->make(DownloadImageService::class);
        $dataAll = $data = [];
        $time = time();
        foreach ($goods_info as $product) {
            $data = [
                'product_id' => $product['id'],
                'name' => Str::substrUTf8($product['store_name'], 12, 'UTF-8', ''),
                'cover_img' => $product['image'] ?? '',
                'price_type' => 1,
                'cost_price' => $product['cost_price'] ?? 0.00,
                'price' => $product['price'] ?? 0.00,
                'url' => 'pages/goods_details/index?id=' . $product['id'],
                'sort' => $product['sort'] ?? 0,
                'add_time' => $time
            ];
            try {
                $path = $download->thumb(true)->downloadImage($data['cover_img'])['path'];
                $coverImgUrl = $miniUpload->uploadImage($path)->media_id;
                @unlink($path);
            } catch (\Throwable $e) {
                Log::error('添加直播商品图片错误，原因：' . $e->getMessage());
                $coverImgUrl = $data['cover_img'];
            }
            $res = MiniProgramService::addGoods($coverImgUrl, $data['name'], $data['price_type'], $data['url'], floatval($data['price']));
            $data['goods_id'] = $res['goodsId'];
            $data['audit_id'] = $res['auditId'];
            $data['audit_status'] = 1;
            $dataAll[] = $data;
        }
        if (!$goods = $this->dao->saveAll($dataAll)) {
            throw new AdminException('添加商品失败');
        }
        return true;
    }

    /**
     * 同步商品
     * @return bool
     * @throws \EasyWeChat\Core\Exceptions\InvalidArgumentException
     */
    public function syncGoods()
    {
        $liveGoods = $this->dao->getColumn([['goods_id', '>', 0]], '*', 'id');
        if ($liveGoods) {
            foreach ($liveGoods as $good) {
                $path = app()->make(DownloadImageService::class)->thumb(true)->downloadImage($good['cover_img'])['path'];

                $coverImgUrl = MiniProgramService::materialTemporaryService()->uploadImage($path)->media_id;
                @unlink($path);
                $res = MiniProgramService::addGoods($coverImgUrl, $good['name'], $good['price_type'], $good['url'], floatval($good['price']));
                $data['goods_id'] = $res['goodsId'];
                $data['audit_id'] = $res['auditId'];
                $data['audit_status'] = 1;
                if (!$this->dao->update($good['id'], $data, 'id')) {
                    throw new AdminException('同步失败');
                }
            }
        }
        return true;
    }

    public function wxCreate($goods)
    {
        if ($goods['goods_id'])
            throw new AdminException('商品已创建');

        $goods = $goods->toArray();
        $path = app()->make(DownloadImageService::class)->thumb(true)->downloadImage($goods['cover_img'])['path'];

        $url = 'pages/goods_details/index?id=' . $goods['product_id'];
        $coverImgUrl = MiniProgramService::materialTemporaryService()->uploadImage($path)->media_id;
        @unlink($path);
        return MiniProgramService::addGoods($coverImgUrl, $goods['name'], 1, $url, floatval($goods['price']));
    }

    public function isShow(int $id, $is_show)
    {
        $goods = $this->dao->get(['id' => $id, 'audit_status' => 2]);
        if (!$goods) {
            throw new AdminException('审核中或审核失败不允许此操作');
        }
        $this->dao->update($id, ['is_show' => $is_show]);
        return $is_show == 1 ? '显示成功' : '隐藏成功';
    }

    /**
     * 重新提交审核
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function audit(int $id)
    {
        $goods = $this->dao->get($id);
        if (!$goods) {
            throw new AdminException('数据不存在');
        }
        if ($goods['audit_status'] != 0) {
            throw new AdminException('在审核中或已经审核通过');
        }
        if (!$this->dao->update($id, ['audit_status' => 1])) {
            throw new AdminException('修改审核状态失败');
        }
        return MiniProgramService::auditGoods((int)$goods['good_id']);
    }

    /**
     * 撤回审核
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function resetAudit(int $id)
    {
        $goods = $this->dao->get($id);
        if (!$goods) {
            throw new AdminException('数据不存在');
        }
        if ($goods['audit_status'] == 0) {
            return true;
        }
        if ($goods['audit_status'] != 1) {
            throw new AdminException('审核通过或失败');
        }
        if (!$this->dao->update($id, ['audit_status' => 0])) {
            throw new AdminException('修改审核状态失败');
        }
        return MiniProgramService::resetauditGoods((int)$goods['good_id'], $goods['audit_id']);
    }

    /**
     * 删除商品
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete(int $id)
    {
        $goods = $this->dao->get(['id' => $id, 'is_del' => 0]);
        if ($goods) {
            if (in_array($goods['audit_status'], [0, 1])) {
                throw new AdminException('商品审核中，无法删除');
            }
            if (!$this->dao->update($id, ['is_del' => 1])) {
                throw new AdminException('删除失败');
            }
            if (MiniProgramService::deleteGoods((int)$goods->goods_id)) {
                /** @var LiveRoomGoodsServices $liveRoomGoods */
                $liveRoomGoods = app()->make(LiveRoomGoodsServices::class);
                $liveRoomGoods->delete(['live_goods_id' => $id]);
            }
        }
        return true;
    }

    /**
     * 同步直播商品审核状态
     * @return bool
     */
    public function syncGoodStatus()
    {
        $goodsIds = $this->dao->goodsStatusAll();
        if (!count($goodsIds)) return true;
        $res = MiniProgramService::getGooodsInfo(array_keys($goodsIds));
        foreach ($res as $item) {
            if (isset($goodsIds[$item['goods_id']]) && $item['audit_status'] != $goodsIds[$item['goods_id']]) {
                $data = ['audit_status' => $item['audit_status']];
                //TODO 同步商品审核状态
                $this->dao->update((int)$goodsIds[$item['goods_id']]['id'], $data);
            }
        }
        return true;
    }

}
