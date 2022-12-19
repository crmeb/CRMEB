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
declare (strict_types = 1);

namespace app\services\pc;


use app\services\BaseServices;
use app\services\order\StoreCartServices;
use app\services\product\product\StoreProductServices;
use app\services\system\SystemUserLevelServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserServices;

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
        $list = $storeCartServices->getCartList(['uid' => $uid], 0, 0, ['productInfo', 'attrInfo']);
        /** @var MemberCardServices $memberCardService */
        $memberCardService = app()->make(MemberCardServices::class);
        $vipStatus = $memberCardService->isOpenMemberCard('vip_price', false);
        /** @var UserServices $user */
        $user = app()->make(UserServices::class);
        $userInfo = $user->getUserInfo($uid);
        //用户等级是否开启
        $discount = 100;
        if (sys_config('member_func_status', 1)) {
            /** @var SystemUserLevelServices $systemLevel */
            $systemLevel = app()->make(SystemUserLevelServices::class);
            $discount = $systemLevel->value(['id' => $userInfo['level'], 'is_del' => 0, 'is_show' => 1], 'discount') ?: 100;
        }
        $valid = $invalid = [];
        foreach ($list as &$item) {
            $is_valid = $item['attrInfo']['suk'] ?? 0;
            $item['productInfo']['attrInfo'] = $item['attrInfo'] ?? [];
            $item['productInfo']['attrInfo']['image'] = $item['attrInfo']['image'] ?? $item['productInfo']['image'];
            if (isset($item['productInfo']['attrInfo'])) {
                $item['productInfo']['attrInfo'] = get_thumb_water($item['productInfo']['attrInfo']);
            }
            $item['productInfo'] = get_thumb_water($item['productInfo']);
            $productInfo = $item['productInfo'];
            if (isset($productInfo['attrInfo']['product_id']) && $item['product_attr_unique']) {
                $item['costPrice'] = $productInfo['attrInfo']['cost'] ?? 0;
                $item['trueStock'] = $productInfo['attrInfo']['stock'] ?? 0;
                [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($productInfo['attrInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, $discount, $productInfo['attrInfo']['vip_price'] ?? 0, $productInfo['is_vip'] ?? 0, true);
                $item['truePrice'] = $truePrice;
                $item['vip_truePrice'] = $vip_truePrice;
                $item['price_type'] = $type;
            } else {
                $item['costPrice'] = $item['productInfo']['cost'] ?? 0;
                $item['trueStock'] = $item['productInfo']['stock'] ?? 0;
                [$truePrice, $vip_truePrice, $type] = $productServices->setLevelPrice($item['productInfo']['price'] ?? 0, $uid, $userInfo, $vipStatus, $discount, $item['productInfo']['vip_price'] ?? 0, $item['productInfo']['is_vip'] ?? 0);
                $item['truePrice'] = $truePrice;
                $item['vip_truePrice'] = $vip_truePrice;
                $item['price_type'] = $type;
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
