<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\order;


use app\services\BaseServices;
use crmeb\exceptions\ApiException;
use app\dao\order\StoreOrderInvoiceDao;
use app\services\user\UserInvoiceServices;


/**
 * Class StoreOrderInvoiceServices
 * @package app\services\order
 */
class StoreOrderInvoiceServices extends BaseServices
{
    /**
     * LiveAnchorServices constructor.
     * @param StoreOrderInvoiceDao $dao
     */
    public function __construct(StoreOrderInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    public function chart(array $where)
    {
        $where['is_pay'] = 1;
        //全部
        $data['all'] = (string)$this->dao->count($where);
        //待开
        $where['type'] = 1;
        $data['noOpened'] = (string)$this->dao->count($where);
        //已开
        $where['type'] = 2;
        $data['opened'] = (string)$this->dao->count($where);
        //退款
        $where['type'] = 3;
        $data['refund'] = (string)$this->dao->count($where);
        return $data;
    }

    /**
     * 后台获取开票列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where)
    {
        [$page, $list] = $this->getPageValue();
        $field = 'id as invoice_id,order_id,header_type,type,name,duty_number,drawer_phone,email,tell,address,bank,card_number,is_invoice,invoice_number,remark as invoice_reamrk,invoice_time,add_time as invoice_add_time';
        $where['is_pay'] = 1;
        $list = $this->dao->getList($where, $field, ['order' => function ($query) {
            $query->field('id,order_id,pay_price,add_time,real_name,user_phone,status,refund_status');
        }], 'add_time desc', $page, $list);
        foreach ($list as &$item) {
            $item['id'] = $item['order']['id'] ?? 0;
            $item['order_id'] = $item['order']['order_id'] ?? '';
            $item['pay_price'] = $item['order']['pay_price'] ?? 0.00;
            $item['real_name'] = $item['order']['real_name'] ?? '';
            $item['user_phone'] = $item['order']['user_phone'] ?? '';
            $item['status'] = $item['order']['status'] ?? '';
            $item['refund_status'] = $item['order']['refund_status'] ?? 0;
            $item['add_time'] = date('Y-m-d H:i:s', $item['order']['add_time'] ?? $item['invoice_add_time'] ?? time());
            $item['invoice_add_time'] = date('Y-m-d H:i:s', $item['invoice_add_time']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 前端获取开票列表（带商品信息）
     * @param $where
     * @return array
     */
    public function getOrderInvoiceList(array $where)
    {
        [$page, $list] = $this->getPageValue();
        $where['is_pay'] = 1;
        $list = $this->dao->getList($where, '*', ['order'], 'add_time desc', $page, $list);
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        foreach ($list as &$item) {
            if (isset($item['order']) && $item['order']) {
                $item['order'] = $storeOrderServices->tidyOrder($item['order'], true);
                if (isset($item['order']['_status']['_type']) && $item['order']['_status']['_type'] == 3) {
                    foreach ($item['order']['cartInfo'] ?: [] as $key => $product) {
                        $item['order']['cartInfo'][$key]['add_time'] = isset($product['add_time']) ? date('Y-m-d H:i', (int)$product['add_time']) : '时间错误';
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 订单申请开票
     * @param int $uid
     * @param $order_id
     * @param int $invoice_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function makeUp(int $uid, $order_id, int $invoice_id)
    {
        if (!$order_id) throw new ApiException(100100);
        if (!$invoice_id) throw new ApiException(410325);

        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        /** @var UserInvoiceServices $userInvoiceServices */
        $userInvoiceServices = app()->make(UserInvoiceServices::class);
        $order = $storeOrderServices->getOne(['order_id|id' => $order_id, 'is_del' => 0]);
        if (!$order) {
            throw new ApiException(410173);
        }
        //检测再带查询
        $invoice = $userInvoiceServices->checkInvoice($invoice_id, $uid);

        if ($this->dao->getOne(['order_id' => $order['id'], 'uid' => $uid])) {
            throw new ApiException(410249);
        }
        if ($order['refund_status'] == 2) {
            throw new ApiException(410226);
        }
        if ($order['refund_status'] == 1) {
            throw new ApiException(410250);
        }
        unset($invoice['id'], $invoice['add_time']);
        $data = [];
        $data['category'] = 'order';
        $data['order_id'] = $order['id'];
        $data['invoice_id'] = $invoice_id;
        $data['add_time'] = time();
        $data['is_pay'] = $order['paid'] == 1 ? 1 : 0;
        $data = array_merge($data, $invoice);
        if (!$re = $this->dao->save($data)) {
            throw new ApiException(410251);
        }
        return ['id' => $re->id];
    }

    public function setInvoice(int $id, array $data)
    {
        $orderInvoice = $this->dao->get($id);
        if (!$orderInvoice) {
            throw new ApiException(100026);
        }
        if ($data['is_invoice'] == 1) {
            $data['invoice_time'] = time();
        }
        if (!$this->dao->update($id, $data, 'id')) {
            throw new ApiException(100015);
        }
        return true;
    }

    /**
     * 拆分订单同步拆分申请开票记录
     * @param int $oid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function splitOrderInvoice(int $oid)
    {
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $orderInfo = $storeOrderServices->getOne(['id' => $oid, 'is_del' => 0]);
        if (!$orderInfo) {
            throw new ApiException(410173);
        }
        $pid = $orderInfo['pid'] > 0 ? $orderInfo['pid'] : $orderInfo['id'];
        //查询开票记录
        $orderInvoice = $this->dao->get(['order_id' => $oid]);
        //查询子订单
        $spliteOrder = $storeOrderServices->getColumn(['pid' => $pid, 'is_system_del' => 0], 'id,order_id');
        if ($spliteOrder && $orderInvoice) {
            $data = $orderInvoice->toArray();
            unset($data['id']);
            $data['add_time'] = strtotime($data['add_time']);
            $data_all = [];
            foreach ($spliteOrder as $order) {
                if (!$this->dao->count(['order_id' => $order['id']])) {
                    $data['order_id'] = $order['id'];
                    $data_all[] = $data;
                }
            }
            if ($data_all) {
                $this->transaction(function () use ($data_all, $orderInvoice, $orderInfo) {
                    $this->dao->saveAll($data_all);
                    if ($orderInfo['pid'] <= 0) {
                        $this->dao->delete(['id' => $orderInvoice['id']]);
                    }
                });
            }
        }
        return true;
    }
}
