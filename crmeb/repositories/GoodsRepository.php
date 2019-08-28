<?php
namespace crmeb\repositories;

use app\models\user\UserAddress;

class GoodsRepository
{


    /**
     * 订单创建成功后
     * @param $order
     * @param $group
     */
    public static function storeProductOrderCreateEbApi($order,$group)
    {
        if(!UserAddress::be(['is_default'=>1,'uid'=>$order['uid']])) UserAddress::setDefaultAddress($group['addressId'],$order['uid']);
    }

}