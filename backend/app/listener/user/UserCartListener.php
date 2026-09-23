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

namespace app\listener\user;

use crmeb\interfaces\ListenerInterface;
use think\facade\Log;

/**
 * 购物车事件监听器
 * Class UserCartListener
 * @package app\listener\user
 */
class UserCartListener implements ListenerInterface
{
    /**
     * 购物车事件处理
     * @param $event
     * @return void
     */
    public function handle($event): void
    {
        [$type, $data] = $event;

        try {
            switch ($type) {
                case 'add':
                    // 加入购物车
                    $this->handleCartAdd($data);
                    break;
                case 'delete':
                    // 删除购物车商品
                    $this->handleCartDelete($data);
                    break;
                case 'update':
                    // 更新购物车商品数量
                    $this->handleCartUpdate($data);
                    break;
                case 'clear':
                    // 清空购物车
                    $this->handleCartClear($data);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('购物车事件监听失败,类型:' . $type . ',失败原因:' . $e->getMessage());
        }
    }

    /**
     * 加入购物车
     * @param array $data
     */
    protected function handleCartAdd(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'cart_id' => 购物车记录ID,
        //     'product_id' => 商品ID,
        //     'sku_id' => SKU ID,
        //     'cart_num' => 购买数量,
        //     'add_time' => 加入时间,
        // ];
    }

    /**
     * 删除购物车商品
     * @param array $data
     */
    protected function handleCartDelete(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'cart_ids' => 购物车记录ID数组,
        //     'delete_type' => 删除类型(user/system),
        //     'delete_time' => 删除时间,
        // ];
    }

    /**
     * 更新购物车商品数量
     * @param array $data
     */
    protected function handleCartUpdate(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'cart_id' => 购物车记录ID,
        //     'old_num' => 原数量,
        //     'new_num' => 新数量,
        //     'update_time' => 更新时间,
        // ];
    }

    /**
     * 清空购物车
     * @param array $data
     */
    protected function handleCartClear(array $data)
    {
        // $data = [
        //     'uid' => 用户ID,
        //     'cart_type' => 购物车类型(0普通/1秒杀/2砍价/3拼团/4积分/5优惠券),
        //     'clear_time' => 清空时间,
        // ];
    }
}
