<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\controller\admin;

use app\adminapi\controller\AuthController;
use modules\product\virtual\service\ProductSaveServices;
use modules\product\virtual\service\ProductServices;
use think\Request;

/**
 * 网盘商品后台控制器
 * 过渡期：调用内化的 ProductSaveServices
 */
class ProductController extends AuthController
{
    /**
     * 商品列表
     */
    public function list(Request $request, ProductServices $service)
    {
        $params = $request->getMore([
            ['page', 1],
            ['limit', 20],
            ['store_name', ''],
            ['cate_id', ''],
            ['status', ''],
            ['is_del', 0],
            ['price_min', ''],
            ['price_max', ''],
            ['stock_min', ''],
            ['stock_max', ''],
            ['time', ''],
        ]);
        $result = $service->getList($params);
        return app('json')->success($result);
    }

    /**
     * 保存商品
     */
    public function save(Request $request, ProductSaveServices $service)
    {
        $data = $request->postMore($this->getSaveFields());
        $id = (int) $request->param('id', 0);
        $productId = $service->save($id, $data);
        return app('json')->success('保存成功', ['id' => $productId]);
    }

    /**
     * 更新商品
     */
    public function update(Request $request, int $id, ProductSaveServices $service)
    {
        $data = $request->postMore($this->getSaveFields());
        $productId = $service->save($id, $data);
        return app('json')->success('更新成功', ['id' => $productId]);
    }

    /**
     * 上下架
     */
    public function setStatus(int $id, int $status)
    {
        $service = app(\app\services\product\CenterProductServices::class);
        $service->update($id, ['is_show' => $status]);
        return app('json')->success($status ? '上架成功' : '下架成功');
    }

    /**
     * 删除商品
     */
    public function delete(int $id)
    {
        $service = app(\app\services\product\CenterProductServices::class);
        $service->update($id, ['is_del' => 1]);
        return app('json')->success('删除成功');
    }

    /**
     * 商品详情
     */
    public function read(int $id, ProductServices $service)
    {
        $extendInfo = $service->getExtendInfo($id);
        return app('json')->success($extendInfo);
    }

    /**
     * 保存/更新字段集合
     * @return array
     */
    protected function getSaveFields(): array
    {
        return [
            ['store_name', ''],
            ['store_info', ''],
            ['cate_id', []],
            ['label_id', []],
            ['slider_image', []],
            ['price', 0],
            ['ot_price', 0],
            ['cost', 0],
            ['stock', 0],
            ['ficti', 0],
            ['give_integral', 0],
            ['description', ''],
            ['is_show', 0],
            ['is_sub', 0],
            ['is_vip', 0],
            ['vip_product', 0],
            ['spec_type', 0],
            ['presale', 0],
            ['presale_time', []],
            ['is_limit', 0],
            ['min_qty', 1],
            ['limit_type', 0],
            ['limit_num', 0],
            ['logistics', []],
            ['freight', 1],
            ['temp_id', 0],
            ['postage', 0],
            ['weight', 0],
            ['volume', 0],
            ['custom_form', []],
            ['params_list', []],
            ['label_list', []],
            ['protection_list', []],
            ['recommend', []],
            ['recommend_list', []],
            ['activity', []],
            ['coupon_ids', []],
            ['keyword', ''],
            ['sort', 0],
            ['unit_name', '件'],
            ['download_url', ''],
            ['download_password', ''],
            ['is_auto_send', 1],
            ['video_link', ''],
            ['video_open', 0],
            ['seletVideo', 0],
            ['items', []],
            ['attrs', []],
            ['oneFormValidate', []],
            ['manyFormValidate', []],
        ];
    }
}
