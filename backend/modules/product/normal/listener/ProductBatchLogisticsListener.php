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
declare(strict_types=1);

namespace modules\product\normal\listener;

use modules\product\normal\service\ProductServices;

/**
 * 商品批量设置物流信息监听器
 * 解耦 app/ 层对 modules/ 层 ProductServices 的直接引用
 */
class ProductBatchLogisticsListener
{
    /**
     * 批量设置商品物流信息
     * @param array $event 事件参数，结构：[$productId, $data]
     * @return void
     */
    public function handle($event)
    {
        [$productId, $data] = $event;
        /** @var ProductServices $productServices */
        $productServices = app()->make(ProductServices::class);
        $productServices->batchSetLogistics([(int)$productId], $data);
    }
}
