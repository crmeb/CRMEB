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
declare (strict_types=1);

namespace app\dao\order;


use crmeb\basic\BaseDao;
use app\model\order\OrderInvoice;

/**
 * Class OrderInvoiceDao
 * @package app\dao\order
 */
class OrderInvoiceDao extends BaseDao
{
    /**
     * 限制精确查询字段
     * @var string[]
     */
    protected $withField = ['uid', 'order_id', 'real_name', 'user_phone'];

    protected function setModel(): string
    {
        return OrderInvoice::class;
    }

    /**
     * 发票搜索
     * @param array $where
     * @param bool $search
     * @return \crmeb\basic\BaseModel
     * @throws \ReflectionException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/20
     */
    public function search(array $where = [], bool $search = false)
    {
        $realName = $where['real_name'] ?? '';
        $fieldKey = $where['field_key'] ?? '';
        $fieldKey = $fieldKey == 'all' ? '' : $fieldKey;
        $type = $where['type'] ?? '';
        unset($where['type']);
        return parent::search($where, $search)->when($type, function ($query) use ($type) {
            switch ($type) {
                case 1://待开
                    $query->where('is_invoice', 0)->where('is_refund', 0);
                    break;
                case 2://已开
                    $query->where('is_invoice', 1);
                    break;
                case 3://退款
                    $query->where('is_refund', 1);
                    break;
                case 4://未开
                    $query->where('is_invoice', 0)->where('invoice_time', 0)->where('is_refund', 0);
                    break;
            }
        })->when($realName && $fieldKey && in_array($fieldKey, $this->withField), function ($query) use ($realName, $fieldKey) {
            $realName = trim($realName);
            if ($fieldKey == 'order_id') {
                // 按订单号精确查询订单中台单号
                $query->where('order_id', 'IN', function ($que) use ($realName) {
                    $que->name('order_center')->where('order_no', $realName)->field(['id'])->select();
                });
            } else {
                // 用户姓名/电话在用户表：先解析用户，再经订单中台关联到开票记录
                $query->where('order_id', 'IN', function ($que) use ($realName, $fieldKey) {
                    $que->name('order_center')->where('uid', 'IN', function ($user) use ($realName, $fieldKey) {
                        $columns = $fieldKey == 'user_phone' ? ['phone'] : ['real_name', 'nickname'];
                        $user->name('user')->where(function ($q) use ($columns, $realName) {
                            foreach ($columns as $column) {
                                $q->whereOr($column, 'LIKE', "%{$realName}%");
                            }
                        })->field(['uid'])->select();
                    })->field(['id'])->select();
                });
            }
        })->when($realName && !$fieldKey, function ($query) use ($where) {
            $query->where(function ($que) use ($where) {
                $que->whereLike('order_id|invoice_id|name|drawer_phone|email|tell|address|bank', "%{$where['real_name']}%")
                    ->whereOr('order_id', 'IN', function ($order) use ($where) {
                        // 订单号模糊查询订单中台单号
                        $order->name('order_center')->whereLike('order_no', "%{$where['real_name']}%")->field('id')->select();
                    })
                    ->whereOr('uid', 'in', function ($q) use ($where) {
                        $q->name('user')->whereLike('nickname|uid|phone', '%' . $where['real_name'] . '%')->field(['uid'])->select();
                    });
            });
        });
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
    public function getList(array $where = [], string $field = '*', array $with = ['order'], string $order = '', int $page = 0, int $limit = 0)
    {
        return $this->search($where)->field($field)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->when($order !== '', function ($query) use ($order) {
            $query->order($order);
        })->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }
}
