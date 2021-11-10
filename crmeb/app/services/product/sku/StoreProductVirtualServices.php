<?php


namespace app\services\product\sku;


use app\dao\product\sku\StoreProductVirtualDao;
use app\services\BaseServices;

class StoreProductVirtualServices extends BaseServices
{
    public function __construct(StoreProductVirtualDao $dao)
    {
        $this->dao = $dao;
    }

    public function getArr($unique, $product_id)
    {
        $res = $this->dao->getColumn(['attr_unique' => $unique, 'product_id' => $product_id], 'card_no,card_pwd');
        $data = [];
        foreach ($res as $item) {
            $data[] = ['key' => $item['card_no'], 'value' => $item['card_pwd']];
        }
        return $data;
    }
}
