<?php


namespace app\listener\user;


use app\services\product\product\StoreVisitServices;
use crmeb\interfaces\ListenerInterface;

/**
 * 写入用户访问
 * Class UserVisit
 * @package app\listener\user
 */
class UserVisit implements ListenerInterface
{
    public function handle($event): void
    {
        [$uid, $product_id, $product_type, $cate, $type] = $event;

        //写入用户访问记录
        /** @var StoreVisitServices $storeVisit */
        $storeVisit = app()->make(StoreVisitServices::class);
        $storeVisit->setView($uid, $product_id, $product_type, $cate, $type);
    }
}