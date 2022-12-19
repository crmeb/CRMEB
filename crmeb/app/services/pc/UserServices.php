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
declare (strict_types=1);

namespace app\services\pc;


use app\services\BaseServices;
use app\services\product\product\StoreProductRelationServices;
use app\services\user\UserBillServices;

class UserServices extends BaseServices
{
    /**
     * PC端用户记录明细
     * @param int $uid
     * @param int $type
     * @return array
     */
    public function getBalanceRecord(int $uid, int $type)
    {
        /** @var UserBillServices $userBill */
        $userBill = app()->make(UserBillServices::class);
        $where = [];
        $where['uid'] = $uid;
        $where['category'] = 'now_money';
        switch ((int)$type) {
            case 0:
                $where['type'] = ['recharge', 'pay_money', 'system_add', 'pay_product_refund', 'system_sub'];
                break;
            case 1:
                $where['type'] = ['pay_money'];
                break;
            case 2:
                $where['type'] = ['recharge', 'system_add'];
                break;
            case 3:
                $where['type'] = ['brokerage', 'brokerage_user'];
                break;
            case 4:
                $where['type'] = ['extract'];
                break;
        }
        [$page, $limit] = $this->getPageValue();
        $list = $userBill->getBalanceRecord($where, $page, $limit);
        foreach ($list as &$item) {
            $item['time'] = date('Y-m', strtotime($item['add_time']));
        }
        $count = $userBill->count($where);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 获取收藏商品
     * @param int $uid
     * @return array
     */
    public function getCollectList(int $uid)
    {
        /** @var StoreProductRelationServices $relation */
        $relation = app()->make(StoreProductRelationServices::class);
        $where['uid'] = $uid;
        $where['type'] = 'collect';
        [$page, $limit] = $this->getPageValue();
        $count = $relation->count($where);
        $list = $relation->getList($where, 'product_id,category', $page, $limit);
        foreach ($list as $k => $product) {
            if ($product['product'] && isset($product['product']['id'])) {
                $list[$k]['pid'] = $product['product']['id'] ?? 0;
                $list[$k]['store_name'] = $product['product']['store_name'] ?? 0;
                $list[$k]['price'] = $product['product']['price'] ?? 0;
                $list[$k]['ot_price'] = $product['product']['ot_price'] ?? 0;
                $list[$k]['sales'] = $product['product']['sales'] ?? 0;
                $list[$k]['image'] = get_thumb_water($product['product']['image'] ?? 0, 'mid');
                $list[$k]['is_del'] = $product['product']['is_del'] ?? 0;
                $list[$k]['is_show'] = $product['product']['is_show'] ?? 0;
                $list[$k]['is_fail'] = $product['product']['is_del'] && $product['product']['is_show'];
            } else {
                unset($list[$k]);
            }
        }
        return compact('list', 'count');
    }
}
