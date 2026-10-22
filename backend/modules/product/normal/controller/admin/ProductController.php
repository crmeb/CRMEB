<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\controller\admin;

use app\adminapi\controller\AuthController;
use modules\product\normal\service\ProductSaveServices;
use modules\product\normal\service\ProductServices;

/**
 * 实物商品后台控制器
 * 所有数据操作走 ProductServices，不直接操作 DAO 或 CenterProductServices
 */
class ProductController extends AuthController
{
    /**
     * 实物商品列表
     * @return \think\Response
     */
    public function list(ProductServices $service)
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['spec_type', ''],
            ['is_gift', ''],
            ['vip_product', ''],
            ['price_s', []],
            ['stock_s', []],
            ['sales_s', []],
            ['time', ''],
            ['page', 1],
            ['limit', 20],
            ['type', '1'],
        ]);
        return app('json')->success($service->getList($where));
    }

    /**
     * tab 头部数量统计
     */
    public function typeHeader(ProductServices $service)
    {
        return app('json')->success($service->getTypeHeader());
    }

    /**
     * 保存商品
     */
    public function save(ProductSaveServices $service)
    {
        $data = $this->request->postMore($this->getSaveFields());
        $id = (int) $this->request->param('id', 0);
        $productId = $service->save($id, $data);
        return app('json')->success('保存成功', ['id' => $productId]);
    }

    /**
     * 更新商品
     */
    public function update(int $id, ProductSaveServices $service)
    {
        $data = $this->request->postMore($this->getSaveFields());
        $productId = $service->save($id, $data);
        return app('json')->success('更新成功', ['id' => $productId]);
    }

    /**
     * 上下架
     */
    public function setStatus(int $id, int $status, ProductServices $service)
    {
        $exist = $service->getExtendInfo($id);
        if (!$exist) {
            return app('json')->fail('商品不存在');
        }
        $service->setShow($id, $status);
        return app('json')->success($status ? '上架成功' : '下架成功');
    }

    /**
     * 软删除商品（中台主表 + 扩展表同步）
     */
    public function delete(int $id, ProductServices $service)
    {
        $product = $service->getExtendInfo($id);
        if (!$product) {
            return app('json')->fail('商品不存在');
        }
        $service->softDelete($id);
        return app('json')->success('删除成功');
    }

    /**
     * 批量软删除商品
     */
    public function batchDelete(ProductServices $service)
    {
        $ids = $this->request->param('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return app('json')->fail('请选择要删除的商品');
        }
        $service->batchSoftDelete($ids);
        return app('json')->success('批量删除成功');
    }

    /**
     * 批量恢复商品（中台主表 + 扩展表同步）
     */
    public function batchRecover(ProductServices $service)
    {
        $ids = $this->request->param('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return app('json')->fail('请选择要恢复的商品');
        }
        $service->batchRecover($ids);
        return app('json')->success('批量恢复成功');
    }

    /**
     * 批量上下架
     */
    public function batchSetStatus(ProductServices $service)
    {
        [$ids, $status] = $this->request->postMore([
            ['ids', []],
            ['status', 1],
        ], true);
        if (!is_array($ids) || empty($ids)) {
            return app('json')->fail('请选择商品');
        }
        $service->batchSetShow($ids, (int)$status);
        return app('json')->success($status ? '批量上架成功' : '批量下架成功');
    }

    /**
     * 批量设置（分类/物流/积分/优惠券/标签/推荐/送礼）
     */
    public function batchSetting(ProductServices $service)
    {
        $data = $this->request->postMore([
            ['ids', []],
            ['type', 0],
            ['cate_id', []],
            ['delivery_methods', []],
            ['freight_config', [
                'express'      => ['type' => 'template', 'postage' => 0, 'temp_id' => 0],
                'door_service' => ['type' => 'free', 'postage' => 0],
            ]],
            ['give_integral', 0],
            ['coupon_ids', []],
            ['label_id', []],
            ['label_list', []],
            ['recommend', []],
            ['is_gift', 0],
            ['gift_price', 0],
        ]);
        $ids = $data['ids'];
        if (!is_array($ids) || empty($ids)) {
            return app('json')->fail('请选择商品');
        }

        switch ($data['type']) {
            case 1: // 修改分类
                if (empty($data['cate_id'])) {
                    return app('json')->fail('请选择分类');
                }
                $service->batchSetCategory($ids, $data['cate_id']);
                break;

            case 2: // 物流设置
                $service->batchSetLogistics($ids, $data);
                break;

            case 3: // 购买送积分
                $service->batchSetGiveIntegral($ids, (float)$data['give_integral']);
                break;

            case 4: // 购买送优惠券
                $service->batchSetGiveCoupons($ids, $data['coupon_ids']);
                break;

            case 5: // 关联用户标签
                $service->batchSetLabel($ids, $data['label_id']);
                break;

            case 9: // 设置商品标签
                $service->batchSetProductLabel($ids, $data['label_list']);
                break;

            case 10: // 送礼设置
                $service->batchSetGift($ids, (int)$data['is_gift'], (float)$data['gift_price']);
                break;

            default:
                return app('json')->fail('未知批量设置类型');
        }

        return app('json')->success('设置成功');
    }

    /**
     * 彻底删除商品（物理删除：中台主表 + 扩展表 + SKU表）
     */
    public function fullDel(int $id, ProductServices $service)
    {
        $product = $service->getExtendInfo($id);
        if (!$product) {
            return app('json')->fail('商品不存在');
        }
        $service->fullDelete($id);
        return app('json')->success('删除成功');
    }

    /**
     * 商品详情
     */
    public function read(int $id, ProductServices $service)
    {
        $product = $service->getExtendInfo($id);
        if (!$product) {
            return app('json')->fail('商品不存在');
        }
        return app('json')->success($product);
    }

    /**
     * 保存/更新字段集合
     * @return array
     */
    protected function getSaveFields(): array
    {
        return [
            ['store_name', ''],
            ['intro', ''],
            ['cate_id', []],
            ['slider_image', []],
            ['price', 0],
            ['original_price', 0],
            ['cost', 0],
            ['stock', 0],
            ['ficti', 0],
            ['give_integral', 0],
            ['spec_type', 0],
            ['items', []],
            ['attrs', []],
            ['description', ''],
            ['delivery_methods', ['express']],
            ['freight_config', [
                'express'      => ['type' => 'template', 'postage' => 0, 'temp_id' => 0],
                'door_service' => ['type' => 'free', 'postage' => 0],
            ]],
            ['weight', 0],
            ['volume', 0],
            ['is_show', 0],
            ['is_limit', 0],
            ['min_qty', 1],
            ['limit_type', 0],
            ['limit_num', 0],
            ['is_sub', 0],
            ['is_vip', 0],
            ['vip_product', 0],
            ['presale', 0],
            ['presale_time', []],
            ['presale_day', 1],
            ['product_type', 'normal'],
            ['custom_form', []],
            ['params_list', []],
            ['label_list', []],
            ['protection_list', []],
            ['recommend', []],
            ['recommend_list', []],
            ['is_gift', 0],
            ['gift_price', 0],
            ['video_link', ''],
            ['recommend_image', ''],
            ['command_word', ''],
            ['activity', []],
            ['coupon_ids', []],
            ['label_id', []],
            ['keyword', ''],
            ['sort', 0],
            ['unit_name', '件'],
        ];
    }
}
