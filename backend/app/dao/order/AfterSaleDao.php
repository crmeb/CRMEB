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
declare(strict_types=1);

namespace app\dao\order;

use crmeb\basic\BaseDao;
use app\model\order\AfterSale;

/**
 * 售后单 DAO
 */
class AfterSaleDao extends BaseDao
{
    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return AfterSale::class;
    }

    public function getByIdForUpdate(int $id)
    {
        return $this->getModel()->where('id', $id)->lock(true)->find();
    }

    /**
     * 获取售后单列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @param string $field
     * @param array $with
     * @return array
     */
    public function getList(array $where = [], int $page = 0, int $limit = 0, string $field = '*', array $with = []): array
    {
        return $this->search($where)->field($field)
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })
            ->with(array_merge(['orderCenter'], $with))
            ->order('id DESC')
            ->select()
            ->toArray();
    }

    /**
     * 按售后单号查询
     * @param string $afterSaleNo
     * @param string $field
     * @return array|null
     */
    public function getByNo(string $afterSaleNo, string $field = '*'): ?array
    {
        $model = $this->getModel()->where('after_sale_no', $afterSaleNo)->field($field)->find();
        return $model ? $model->toArray() : null;
    }

    /**
     * 按订单号查询进行中的售后
     * @param string $orderNo
     * @return array|null
     */
    public function getActiveRefund(string $orderNo): ?array
    {
        $model = $this->getModel()
            ->where('order_no', $orderNo)
            ->where('is_del', 0)
            ->where('status', 'in', ['pending_audit', 'audit_passed', 'refunding', 'pending_return', 'receiving'])
            ->find();
        return $model ? $model->toArray() : null;
    }

    /**
     * 查询订单下所有有效售后单（非取消/非驳回/未删除）
     * 用于按 cart_id 聚合已申请数量，计算剩余可退数量
     * @param string $orderNo 订单号
     * @return array 返回 [{after_sale_no, type, status, cart_ids, refund_num}, ...]
     */
    public function getValidByOrderNo(string $orderNo): array
    {
        // 有效状态：待审核/审核通过/退款中/待退货/退货中/补发中/维修中/维修完成/已完成/已退款
        // 无效状态：已取消/已驳回（不计入已申请数量）
        $validStatuses = [
            'pending_audit', 'audit_passed', 'refunding',
            'pending_return', 'receiving',
            'resending', 'repairing', 'repair_complete',
            'completed', 'refunded',
        ];
        return $this->getModel()
            ->where('order_no', $orderNo)
            ->where('is_del', 0)
            ->whereIn('status', $validStatuses)
            ->field('id, after_sale_no, type, status, cart_ids, refund_num, product_snapshot')
            ->select()
            ->toArray();
    }

    /**
     * 按 cart_id 查询进行中的售后单
     * 用于校验同一 cart_id 是否已有待审核/审核通过的售后（防重复申请）
     * @param string $orderNo 订单号
     * @param int $cartId 购物车明细ID
     * @return array|null 返回进行中的售后单，无则 null
     */
    public function getActiveByCartId(string $orderNo, int $cartId): ?array
    {
        // 进行中状态：待审核/审核通过/退款中/待退货/退货中
        $activeStatuses = ['pending_audit', 'audit_passed', 'refunding', 'pending_return', 'receiving'];
        $list = $this->getModel()
            ->where('order_no', $orderNo)
            ->where('is_del', 0)
            ->whereIn('status', $activeStatuses)
            ->field('after_sale_no, type, status, cart_ids, refund_num')
            ->select()
            ->toArray();
        // 在应用层过滤 cart_ids 包含指定 cartId（MySQL JSON_CONTAINS 在 ThinkPHP ORM 中兼容性差）
        foreach ($list as $row) {
            $cartIds = $row['cart_ids'];
            if (is_string($cartIds)) {
                $cartIds = json_decode($cartIds, true) ?: [];
            }
            if (in_array($cartId, $cartIds, true)) {
                return $row;
            }
        }
        return null;
    }

    /**
     * 按订单号查询最新售后单号
     * 取该订单最近一条售后单（不限状态，含已取消/已完成），用于 viewAfterSale 按钮参数注入
     * @param string $orderNo 订单号
     * @return string 返回售后单号，无记录返回空字符串
     */
    public function getLatestNoByOrderNo(string $orderNo): string
    {
        $model = $this->getModel()
            ->where('order_no', $orderNo)
            ->where('is_del', 0)
            ->order('id DESC')
            ->field('after_sale_no')
            ->find();
        return $model ? (string)($model['after_sale_no'] ?? '') : '';
    }

    /**
     * 批量查询多个订单的最新售后单号
     * 与 getLatestNoByOrderNo 同口径（每单取 id 最大一条），一次查询避免逐单点查
     * @param array $orderNos 订单号数组
     * @return array 返回以 order_no 为键的最新售后单号映射，无售后记录的订单不在结果中
     */
    public function getLatestNoMapByOrderNos(array $orderNos): array
    {
        if (empty($orderNos)) {
            return [];
        }
        $rows = $this->getModel()
            ->whereIn('order_no', $orderNos)
            ->where('is_del', 0)
            ->order('id DESC')
            ->field('order_no, after_sale_no')
            ->select()
            ->toArray();
        $map = [];
        foreach ($rows as $row) {
            $orderNo = (string)($row['order_no'] ?? '');
            // 按 id 倒序遍历，每个订单号首次命中即其最新一条售后单
            if ($orderNo !== '' && !isset($map[$orderNo])) {
                $map[$orderNo] = (string)($row['after_sale_no'] ?? '');
            }
        }
        return $map;
    }

    /**
     * 统计指定订单的售后数量
     * @param string $orderNo
     * @return int
     */
    public function countByOrder(string $orderNo): int
    {
        return $this->getModel()
            ->where('order_no', $orderNo)
            ->where('is_del', 0)
            ->count();
    }

    /**
     * 批量查询订单售后概要
     * 以订单号为维度聚合售后信息，用于订单列表展示是否售后中
     * @param array $orderNos 订单号数组
     * @return array 返回以 order_no 为键的售后概要
     */
    public function getBatchSummary(array $orderNos): array
    {
        if (empty($orderNos)) {
            return [];
        }

        // 售后进行中的状态：待审核、审核通过、退款中、待退货、退货中/待收货、补发中、维修中、维修完成
        $processingStatuses = ['pending_audit', 'audit_passed', 'refunding', 'pending_return', 'receiving', 'resending', 'repairing', 'repair_complete'];

        $rows = $this->getModel()
            ->whereIn('order_no', $orderNos)
            ->where('is_del', 0)
            ->field('order_no, type, status, COUNT(*) as count')
            ->group('order_no, type, status')
            ->select()
            ->toArray();

        $result = [];
        foreach ($rows as $row) {
            $orderNo = $row['order_no'];
            if (!isset($result[$orderNo])) {
                $result[$orderNo] = [
                    'has_after_sale' => true,
                    'count'          => 0,
                    'types'          => [],
                    'statuses'       => [],
                    'is_processing'  => false,
                ];
            }
            $result[$orderNo]['count'] += (int) $row['count'];
            $result[$orderNo]['types'][] = $row['type'];
            $result[$orderNo]['statuses'][] = $row['status'];
        }

        foreach ($result as &$item) {
            $item['types'] = array_values(array_unique($item['types']));
            $item['statuses'] = array_values(array_unique($item['statuses']));
            $item['is_processing'] = (bool) array_intersect($item['statuses'], $processingStatuses);
        }

        return $result;
    }

    /**
     * 获取有售后/售后中的订单号列表
     * 用于订单列表按售后状态筛选
     * @param string $mode 筛选模式：processing=售后中，all=有售后（含已完成），refunded=已退款完成
     * @param array $orderNos 可选的订单号范围（减少扫描量）
     * @return array 返回订单号数组
     */
    public function getOrderNosByAfterSaleStatus(string $mode = 'all', array $orderNos = []): array
    {
        $query = $this->getModel()->where('is_del', 0);

        if (!empty($orderNos)) {
            $query->whereIn('order_no', $orderNos);
        }

        if ($mode === 'processing') {
            // 售后进行中的状态：待审核、审核通过、退款中、待退货、退货中/待收货、补发中、维修中、维修完成
            $processingStatuses = ['pending_audit', 'audit_passed', 'refunding', 'pending_return', 'receiving', 'resending', 'repairing', 'repair_complete'];
            $query->whereIn('status', $processingStatuses);
        } elseif ($mode === 'refunded') {
            // 已退款完成：退款/退货退款类型且已完结
            $query->whereIn('status', ['refunded', 'completed'])
                  ->whereIn('type', ['refund_only', 'return_refund']);
        }
        // all 模式：不追加状态条件，返回所有有售后记录的订单号

        return $query->group('order_no')->column('order_no');
    }
}
