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

namespace app\dao\order;


use app\dao\BaseDao;
use app\model\order\StoreOrder;
use app\model\order\StoreOrderInvoice;

/**
 * Class StoreOrderOrderInvoiceDao
 * @package app\dao\order
 */
class StoreOrderOrderInvoiceDao extends BaseDao
{
    /**
     * 限制精确查询字段
     * @var string[]
     */
    protected $withField = ['uid', 'order_id', 'real_name', 'user_phone', 'title'];

    /**
     * @var string
     */
    protected $alias = '';

    /**
     * @var string
     */
    protected $join_alis = '';

    protected function setModel(): string
    {
        return StoreOrderInvoice::class;
    }

    public function joinModel(): string
    {
        return StoreOrder::class;
    }

    /**
     * 关联模型
     * @param string $alias
     * @param string $join_alias
     * @return \crmeb\basic\BaseModel
     */
    public function getModel(string $alias = 'i', string $join_alias = 'o', $join = 'left')
    {
        $this->alias = $alias;
        $this->join_alis = $join_alias;
        /** @var StoreOrder $storeOrder */
        $storeOrder = app()->make($this->joinModel());
        $table = $storeOrder->getName();
        return parent::getModel()->alias($alias)->join($table . ' ' . $join_alias, $alias . '.order_id = ' . $join_alias . '.id', $join);
    }

    public function getSearch($where)
    {
        $realName = $where['real_name'] ?? '';
        $fieldKey = $where['field_key'] ?? '';
        $fieldKey = $fieldKey == 'all' ? '' : $fieldKey;
        return $this->getModel()->when(isset($where['data']) && $where['data'], function ($query) use ($where) {
            if (strstr($where['data'], '-') !== false) {
                [$startTime, $endTime] = explode('-', $where['data']);
                $query->whereBetween($this->alias . '.add_time', [strtotime($startTime), strtotime($endTime) + 86400]);
            }
        })->when(isset($where['status']) && $where['status'] !== '', function ($query) use ($where) {
            switch ((int)$where['status']) {
                case 0://未支付
                    $query->where($this->join_alis . '.paid', 0)->where($this->join_alis . '.status', 0)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.is_del', 0);
                    break;
                case 1://已支付 未发货
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 0)->where($this->join_alis . '.refund_status', 0)->when(isset($where['shipping_type']), function ($query) {
                        $query->where($this->join_alis . '.shipping_type', 1);
                    })->where($this->join_alis . '.is_del', 0);
                    break;
                case 2://已支付  待收货
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 1)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.is_del', 0);
                    break;
                case 3:// 已支付  已收货  待评价
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 2)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.is_del', 0);
                    break;
                case 4:// 交易完成
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 3)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.is_del', 0);
                    break;
                case 5://已支付  待核销
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 0)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.shipping_type', 2)->where($this->join_alis . '.is_del', 0);
                    break;
                case 6://已支付 已核销 没有退款
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.status', 2)->where($this->join_alis . '.refund_status', 0)->where($this->join_alis . '.shipping_type', 2)->where($this->join_alis . '.is_del', 0);
                    break;
                case -1://退款中
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.refund_status', 1)->where($this->join_alis . '.is_del', 0);
                    break;
                case -2://已退款
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.refund_status', 2)->where($this->join_alis . '.is_del', 0);
                    break;
                case -3://退款
                    $query->where($this->join_alis . '.paid', 1)->where($this->join_alis . '.refund_status', 'in', '1,2')->where($this->join_alis . '.is_del', 0);
                    break;
                case -4://已删除
                    $query->where($this->join_alis . '.is_del', 1);
                    break;
            }
        })->when(isset($where['type']), function ($query) use ($where) {
            switch ($where['type']) {
                case 1:
                    $query->where($this->join_alis . '.combination_id', 0)->where($this->join_alis . '.seckill_id', 0)->where($this->join_alis . '.bargain_id', 0);
                    break;
                case 2:
                    $query->where($this->join_alis . '.pink_id|' . $this->join_alis . '.combination_id', ">", 0);
                    break;
                case 3:
                    $query->where($this->join_alis . '.seckill_id', ">", 0);
                    break;
                case 4:
                    $query->where($this->join_alis . '.bargain_id', ">", 0);
                    break;
            }
        })->when($realName && $fieldKey && in_array($fieldKey, $this->withField), function ($query) use ($where, $realName, $fieldKey) {
            if ($fieldKey !== 'title') {
                $query->where($this->join_alis . '.' . trim($fieldKey), trim($realName));
            } else {
                $query->where($this->join_alis . '.id', 'in', function ($que) use ($where) {
                    $que->name('store_order_cart_info')->whereIn('product_id', function ($q) use ($where) {
                        $q->name('store_product')->whereLike('store_name|keyword', '%' . $where['real_name'] . '%')->field(['id'])->select();
                    })->field(['oid'])->select();
                });
            }
        })->when($realName && !$fieldKey, function ($query) use ($where) {
            $query->where(function ($que) use ($where) {
                $que->whereLike($this->alias . '.order_id|' . $this->alias . '.invoice_id|' . $this->alias . '.name|' . $this->alias . '.drawer_phone|' . $this->alias . '.email|' . $this->alias . '.tell|' . $this->alias . '.address|' . $this->alias . '.bank', "%{$where['real_name']}%")
                    ->whereOr($this->join_alis . '.uid|' . $this->join_alis . '.real_name|' . $this->join_alis . '.order_id', 'LIKE', "%{$where['real_name']}%")
                    ->whereOr($this->join_alis . '.uid', 'in', function ($q) use ($where) {
                        $q->name('user')->whereLike('nickname|uid|phone', '%' . $where['real_name'] . '%')->field(['uid'])->select();
                    })->whereOr($this->join_alis . '.id', 'in', function ($que) use ($where) {
                        $que->name('store_order_cart_info')->whereIn('product_id', function ($q) use ($where) {
                            $q->name('store_product')->whereLike('store_name|keyword', '%' . $where['real_name'] . '%')->field(['id'])->select();
                        })->field(['oid'])->select();
                    });
            });
        });
    }

    public function getCount(array $where)
    {
        return $this->getSearch($where)->count();
    }

    /**
     * @param array $where
     * @param string $field
     * @param array|string[] $with
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', string $order = '', int $page = 0, int $limit = 0)
    {
        return $this->getSearch($where)->field($field)->when($order != '', function ($query) use ($order) {
            $query->order($order);
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }
}
