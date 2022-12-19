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

namespace app\services\product\product;


use app\dao\product\product\StoreProductLogDao;
use app\services\order\StoreOrderCartInfoServices;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;

/**
 * 商品访问记录日志
 * Class StoreProductLogServices
 * @package app\services\product\product
 * @method getProductTrend($time, $timeType, $str) 商品趋势
 */
class StoreProductLogServices extends BaseServices
{
    /**
     * StoreProductLogServices constructor.
     * @param StoreProductLogDao $dao
     */
    public function __construct(StoreProductLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 创建各种访问日志
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function createLog(string $type, array $data)
    {
        if (!in_array($type, ['order', 'pay', 'refund']) && (!isset($data['product_id']) || !$data['product_id'])) {
            throw new AdminException(400562);
        }
        if ($type != 'visit' && (!isset($data['uid']) || !$data['uid'])) {
            throw new AdminException(400563);
        }
        $log_data = $log_data_all = [];
        $log_data['type'] = $type;
        $log_data['product_id'] = $data['product_id'] ?? 0;
        $log_data['uid'] = $data['uid'] ?? 0;
        $log_data['add_time'] = time();
        switch ($type) {
            case 'visit'://访问
                $log_data['visit_num'] = isset($data['visit_num']) && $data['visit_num'] ? $data['visit_num'] : 1;
                break;
            case 'cart'://加入购物车
                $log_data['cart_num'] = isset($data['cart_num']) && $data['cart_num'] ? $data['cart_num'] : 1;
                break;
            case 'collect'://收藏
                $log_data['collect_num'] = isset($data['collect_num']) && $data['collect_num'] ? $data['collect_num'] : 1;
                break;
            case 'order'://下单
                if (!isset($data['order_id']) || !$data['order_id']) {
                    throw new AdminException(400564);
                }
                /** @var StoreOrderCartInfoServices $cartInfoServices */
                $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cartInfo = $cartInfoServices->getOrderCartInfo($data['order_id']);
                foreach ($cartInfo as $value) {
                    $product = $value['cart_info'];
                    $log_data['product_id'] = $product['product_id'] ?? 0;
                    $log_data['order_num'] = $product['cart_num'] ?? 1;
                    $log_data_all[] = $log_data;
                }
                break;
            case 'pay'://支付
                if (!isset($data['order_id']) || !$data['order_id']) {
                    throw new AdminException(400564);
                }
                /** @var StoreOrderCartInfoServices $cartInfoServices */
                $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cartInfo = $cartInfoServices->getOrderCartInfo($data['order_id']);
                foreach ($cartInfo as $value) {
                    $product = $value['cart_info'];
                    $log_data['product_id'] = $product['product_id'] ?? 0;
                    $log_data['pay_num'] = $product['cart_num'] ?? 0;
                    $log_data['cost_price'] = $product['costPrice'] ?? 0;
                    $log_data['pay_price'] = $product['truePrice'] ?? 0;
                    $log_data['pay_uid'] = $data['uid'] ?? 0;
                    $log_data_all[] = $log_data;
                }
                break;
            case 'refund'://退款
                if (!isset($data['order_id']) || !$data['order_id']) {
                    throw new AdminException(400564);
                }
                /** @var StoreOrderCartInfoServices $cartInfoServices */
                $cartInfoServices = app()->make(StoreOrderCartInfoServices::class);
                $cartInfo = $cartInfoServices->getOrderCartInfo($data['order_id']);
                foreach ($cartInfo as $value) {
                    $product = $value['cart_info'];
                    $log_data['product_id'] = $product['product_id'] ?? 0;
                    $log_data['uid'] = $data['uid'] ?? 0;
                    $log_data['pay_uid'] = $data['uid'] ?? 0;
                    $log_data['refund_num'] = $product['cart_num'] ?? 0;
                    $log_data['refund_price'] = $product['truePrice'] ?? 0;
                    $log_data_all[] = $log_data;
                }
                break;
            default:
                throw new AdminException(400565);
        }
        if ($log_data_all) {
            $res = $this->dao->saveAll($log_data_all);
        } else {
            $res = $this->dao->save($log_data);
        }
        if (!$res) {
            throw new AdminException(400566);
        }
        return true;
    }

    /**
     * 查找购买商品排行
     * @param $where
     * @return mixed
     */
    public function getRanking(array $where)
    {
        $list = $this->dao->getRanking($where);
        foreach ($list as &$item) {
            if ($item['profit'] == null) $item['profit'] = 0;
            if ($item['changes'] == null) $item['changes'] = 0;
            if ($item['repeats'] == null) {
                $item['repeats'] = 0;
            } else {
                $item['repeats'] = bcdiv($this->dao->getRepeats($where, $item['product_id']), $item['repeats'], 2);
            }
        }
        return $list;
    }

    /**
     * 浏览商品列表
     * @param array $where
     * @param string $group
     * @param string $field
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $group = '', string $field = '*')
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, $field, $page, $limit, $group);
        if ($group) {
            $count = $this->dao->getDistinctCount($where, $group, true);
        } else {
            $count = $this->dao->count($where);
        }
        return compact('list', 'count');
    }
}
