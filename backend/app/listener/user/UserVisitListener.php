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


use app\services\product\product\StoreVisitServices;
use crmeb\interfaces\ListenerInterface;
use app\jobs\ProductLogJob;

/**
 * 写入用户访问
 * Class UserVisitListener
 * @package app\listener\user
 */
class UserVisitListener implements ListenerInterface
{
    /**
     * @param array $event 用户 ID、业务 ID、类型、分类或商品 ID、访问类型
     * @return void
     */
    public function handle($event): void
    {
        [$uid, $product_id, $product_type, $cate, $type] = $event;
        // 普通商品和砍价已有商品访问日志，避免分类 ID 误记与重复计数。
        if (!in_array($product_type, ['product', 'bargain'], true)) {
            ProductLogJob::dispatch(['visit', ['uid' => $uid, 'product_id' => $cate]]);
        }
        //写入商品用户访问记录
        /** @var StoreVisitServices $storeVisit */
        $storeVisit = app()->make(StoreVisitServices::class);
        $storeVisit->setView($uid, $product_id, $product_type, $cate, $type);
    }
}
