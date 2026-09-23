<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\adminapi\controller\order;

use app\adminapi\controller\AuthController;
use app\dao\driver\pay\PayChannelDao;
use app\services\order\OrderCenterServices;
use crmeb\trade\mode\TradeModeRegistry;
use crmeb\trade\order\type\OrderTypeRegistry;
use crmeb\trade\product\ProductTypeRegistry;

/**
 * 订单中台控制器
 * 仅提供统一只读查询接口：列表/统计/详情/备注/删除/分类/筛选选项
 * 订单的增删改操作由各产品类型扩展端控制器处理（modules/product/{type}/controller/admin/OrderController）
 * 旧版 StoreOrder 操作方法已全部迁移至扩展端
 */
class OrderCenterController extends AuthController
{
    /**
     * 获取订单中台列表
     * 支持多维度筛选和搜索，返回分页数据
     * @param OrderCenterServices $services 订单中台服务实例
     * @return mixed 返回JSON响应
     */
    public function index(OrderCenterServices $services)
    {
        $where = [
            'order_category' => $this->request->param('order_category', ''),
            'product_type'   => $this->request->param('product_type', ''),
            'activity_type'  => $this->request->param('activity_type', ''),
            'status_code'    => $this->request->param('status_code', ''),
            'pay_type'       => $this->request->param('pay_type', ''),
            'keyword'        => $this->request->param('keyword', ''),
            'date_range'     => $this->request->param('date_range', ''),
            'uid'            => (int) $this->request->param('uid', 0),
            'after_sale'     => $this->request->param('after_sale', ''),
        ];
        $page = (int) $this->request->param('page', 1);
        $limit = (int) $this->request->param('limit', 20);
        return app('json')->success($services->getAdminList($where, $page, $limit));
    }

    /**
     * 获取订单统计数据
     * 包含订单总数、金额统计、状态分布、分类统计
     * @param OrderCenterServices $services 订单中台服务实例
     * @return mixed 返回JSON响应
     */
    public function stat(OrderCenterServices $services)
    {
        $where = [
            'date_range'     => $this->request->param('date_range', ''),
            'order_category' => $this->request->param('order_category', ''),
        ];
        return app('json')->success($services->getStatistics($where));
    }

    /**
     * 获取订单详情
     * 通过事件机制允许扩展模块注入详情数据
     * @param OrderCenterServices $services 订单中台服务实例
     * @param int $id 订单中台记录ID
     * @return mixed 返回JSON响应
     */
    public function detail(OrderCenterServices $services, int $id)
    {
        return app('json')->success($services->getDetail($id));
    }

    /**
     * 更新订单备注
     * @param OrderCenterServices $services 订单中台服务实例
     * @return mixed 返回JSON响应
     */
    public function remark(OrderCenterServices $services)
    {
        $id = (int) $this->request->param('id');
        $remark = $this->request->param('remark', '');
        $services->updateRemark($id, $remark);
        return app('json')->success('修改成功');
    }

    /**
     * 删除订单（软删除）
     * @param OrderCenterServices $services 订单中台服务实例
     * @return mixed 返回JSON响应
     */
    public function delete(OrderCenterServices $services)
    {
        $id = (int) $this->request->param('id');
        $services->delete($id);
        return app('json')->success('删除成功');
    }

    /**
     * 获取订单大类列表
     * 动态从已注册的适配器中获取订单大类，供前端下拉筛选使用
     * @return mixed 返回JSON响应，包含[分类标识 => 中文标签]的映射
     */
    public function categories()
    {
        $categoryMap = OrderTypeRegistry::getCategoryMap();
        $categories = [];
        foreach ($categoryMap as $key => $label) {
            $categories[] = [
                'key'   => $key,
                'label' => $label,
            ];
        }
        return app('json')->success($categories);
    }

    /**
     * 获取筛选下拉选项
     * 动态返回产品类型、活动类型、支付方式的下拉选项，避免前端硬编码
     * @return mixed 返回JSON响应
     */
    public function filterOptions()
    {
        // 产品类型：从产品类型注册中心动态获取
        $productTypes = [];
        try {
            /** @var ProductTypeRegistry $productRegistry */
            $productRegistry = $this->app->make(ProductTypeRegistry::class);
            foreach ($productRegistry->allInfo() as $item) {
                $productTypes[] = [
                    'key'   => $item['key'],
                    'label' => $item['name'],
                ];
            }
        } catch (\Throwable $e) {
            // 产品类型注册中心不可用时返回空数组
        }

        // 活动类型：从活动注册中心动态获取
        $activityTypes = [];
        try {
            /** @var TradeModeRegistry $activityRegistry */
            $activityRegistry = $this->app->make(TradeModeRegistry::class);
            $activityTypes = $activityRegistry->allInfo();
        } catch (\Throwable $e) {
            // 活动注册中心不可用时返回空数组
        }

        // 支付方式：仅获取 channel_type='payment' 的渠道，自动排除功能型渠道（如好友代付）
        $payTypes = [];
        try {
            /** @var PayChannelDao $payChannelDao */
            $payChannelDao = $this->app->make(PayChannelDao::class);
            $channels = $payChannelDao->getPaymentTypes();
            foreach ($channels as $ch) {
                $payTypes[] = [
                    'key'   => $ch['channel_key'],
                    'label' => $ch['channel_name'],
                ];
            }
        } catch (\Throwable $e) {
            // 支付渠道不可用时返回空数组
        }

        return app('json')->success([
            'product_types'  => $productTypes,
            'activity_types' => $activityTypes,
            'pay_types'      => $payTypes,
        ]);
    }
}
