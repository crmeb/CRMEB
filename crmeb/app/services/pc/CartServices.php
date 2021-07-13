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

namespace app\services\pc;


use app\services\BaseServices;
use app\services\order\StoreCartServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserLevelServices;

class CartServices extends BaseServices
{
    /**
     * PC端购物车列表
     * @param int $uid
     * @return array[]
     */
    public function getCartList(int $uid)
    {
        /** @var StoreCartServices $storeCartServices */
        $storeCartServices = app()->make(StoreCartServices::class);
        /** @var StoreProductServices $productServices */
        $productServices = app()->make(StoreProductServices::class);
        /** @var UserLevelServices $userLevelServices */
        $userLevelServices = app()->make(UserLevelServices::class);

        $list = $storeCartServices->getCartList(['uid' => $uid], 0, 0, ['productInfo', 'attrInfo']);
        $discount = $userLevelServices->getDiscount($uid, 'discount');
        $valid = $invalid = [];
        foreach ($list as &$item) {
            $is_valid = $item['attrInfo']['suk'] ?? 0;
            $item['productInfo']['attrInfo'] = $item['attrInfo'] ?? [];
            $item['productInfo']['attrInfo']['image'] = $item['attrInfo']['image'] ?? $item['productInfo']['image'];
            $productInfo = $item['productInfo'];
            if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                $item['truePrice'] = $productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid, true, $discount, $item['attrInfo']['vip_price'] ?? 0, $productInfo['is_vip'] ?? 0, true);
                $item['vip_truePrice'] = (float)$productServices->setLevelPrice($productInfo['attrInfo']['price'], $uid, false, $discount, $item['attrInfo']['vip_price'] ?? 0, $productInfo['is_vip'] ?? 0, true);
            } else {
                $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                $item['truePrice'] = $productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid, true, $discount, $item['attrInfo']['vip_price'] ?? 0, $item['productInfo']['is_vip'] ?? 0);
                $item['vip_truePrice'] = (float)$productServices->setLevelPrice($item['productInfo']['price'], $uid, false, $discount, $item['attrInfo']['vip_price'] ?? 0, $item['productInfo']['is_vip'] ?? 0);
            }
            unset($item['attrInfo']);
            if ($item['status'] == 1 && $is_valid && $item['trueStock'] > 0) {
                $valid[] = $item;
            } else {
                $invalid[] = $item;
            }
        }
        return ['valid' => $valid, 'invalid' => $invalid];
    }
}
