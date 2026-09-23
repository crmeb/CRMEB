<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\listener\product;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;
use app\jobs\ProductLogJob;

/**
 * 商品事件监听器
 * 统一处理商品相关的各类事件
 * Class ProductListener
 * @package app\listener\product
 */
class ProductListener implements ListenerInterface
{
    /**
     * 商品事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        [$type, $data] = $event;

        try {
            switch ($type) {
                // ============ 商品基础事件 ============
                case 'create':
                    // 商品创建
                    $this->handleCreate($data);
                    break;
                case 'update':
                    // 商品更新
                    $this->handleUpdate($data);
                    break;
                case 'delete':
                    // 商品删除
                    $this->handleDelete($data);
                    break;
                case 'status_change':
                    // 商品状态变更
                    $this->handleStatusChange($data);
                    break;

                // ============ 商品库存事件 ============
                case 'stock_change':
                    // 商品库存变动
                    $this->handleStockChange($data);
                    break;
                case 'stock_warning':
                    // 商品库存预警
                    $this->handleStockWarning($data);
                    break;

                // ============ 商品价格事件 ============
                case 'price_change':
                    // 商品价格变动
                    $this->handlePriceChange($data);
                    break;

                // ============ 商品互动事件 ============
                case 'visit':
                    // 商品访问
                    $this->handleVisit($data);
                    break;
                case 'collect':
                    // 商品收藏
                    $this->handleCollect($data);
                    break;
                case 'collect_cancel':
                    // 取消收藏
                    $this->handleCollectCancel($data);
                    break;
                case 'reply':
                    // 商品评价
                    $this->handleReply($data);
                    break;
                case 'reply_reply':
                    // 评价回复
                    $this->handleReplyReply($data);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('商品事件监听失败,类型:' . $type . ',失败原因:' . $e->getMessage());
        }
    }

    // ==================== 商品基础事件 ====================

    /**
     * 商品创建
     * @param array $data
     */
    protected function handleCreate(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'product_info' => 商品信息,
        // ];
    }

    /**
     * 商品更新
     * @param array $data
     */
    protected function handleUpdate(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'old_info' => 修改前的信息,
        //     'new_info' => 修改后的信息,
        // ];
    }

    /**
     * 商品删除
     * @param array $data
     */
    protected function handleDelete(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'delete_type' => 删除类型,
        // ];
    }

    /**
     * 商品状态变更
     * @param array $data
     */
    protected function handleStatusChange(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'old_status' => 原状态,
        //     'new_status' => 新状态,
        // ];
    }

    // ==================== 商品库存事件 ====================

    /**
     * 商品库存变动
     * @param array $data
     */
    protected function handleStockChange(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'sku_id' => SKU ID,
        //     'change_type' => 变动类型,
        //     'amount' => 变动数量,
        //     'old_stock' => 原库存,
        //     'new_stock' => 新库存,
        // ];
    }

    /**
     * 商品库存预警
     * @param array $data
     */
    protected function handleStockWarning(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'sku_id' => SKU ID,
        //     'current_stock' => 当前库存,
        //     'warning_threshold' => 预警阈值,
        // ];
    }

    // ==================== 商品价格事件 ====================

    /**
     * 商品价格变动
     * @param array $data
     */
    protected function handlePriceChange(array $data)
    {
        // $data = [
        //     'product_id' => 商品ID,
        //     'sku_id' => SKU ID,
        //     'old_price' => 原价格,
        //     'new_price' => 新价格,
        //     'change_type' => 变更类型,
        // ];
    }

    // ==================== 商品互动事件 ====================

    /**
     * 商品访问
     * @param array $data [productId, uid, visitTime, &data]
     */
    protected function handleVisit(array $data)
    {
        [$productId, $uid, $visitTime, &$returnData] = $data;

        try {
            // 1. 异步记录商品访问日志
            ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $productId]]);

            // ==================== 二开扩展区域 ====================
            // 在此处可以直接修改商品详情返回数据 $returnData

            // 示例1：增加自定义字段
            // $returnData['custom_field'] = '自定义内容';

            // 示例2：修改现有字段
            // $returnData['storeInfo']['store_name'] = $returnData['storeInfo']['store_name'] . ' (限时特惠)';

            // 示例3：根据用户身份调整数据
            // if ($uid) {
            //     $returnData['user_type'] = 'login';
            // } else {
            //     $returnData['user_type'] = 'guest';
            // }

        } catch (\Throwable $e) {
            Log::error('商品访问事件处理失败：' . $e->getMessage(), [
                'productId' => $productId,
                'uid' => $uid,
                'visitTime' => $visitTime,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * 商品收藏
     * @param array $data
     */
    protected function handleCollect(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'product_id' => 商品ID,
        //     'collect_time' => 收藏时间,
        // ];
    }

    /**
     * 取消收藏
     * @param array $data
     */
    protected function handleCollectCancel(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'product_id' => 商品ID,
        //     'cancel_time' => 取消时间,
        // ];
    }

    /**
     * 商品评价
     * @param array $data
     */
    protected function handleReply(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'product_id' => 商品ID,
        //     'order_id' => 订单ID,
        //     'reply_id' => 评价ID,
        //     'star' => 评分,
        //     'content' => 评价内容,
        // ];
    }

    /**
     * 评价回复
     * @param array $data
     */
    protected function handleReplyReply(array $data)
    {
        // $data = [
        //     'reply_id' => 原评价ID,
        //     'content' => 回复内容,
        //     'reply_type' => 回复类型(merchant/user),
        // ];
    }
}
