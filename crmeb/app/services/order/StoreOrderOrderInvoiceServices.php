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

namespace app\services\order;


use app\dao\order\StoreOrderOrderInvoiceDao;
use app\services\activity\combination\StorePinkServices;
use app\services\BaseServices;
use think\exception\ValidateException;
use app\services\user\UserInvoiceServices;


/**
 * Class StoreOrderOrderInvoiceServices
 * @package app\services\order
 */
class StoreOrderOrderInvoiceServices extends BaseServices
{
    /**
     * StoreOrderOrderInvoiceServices constructor.
     * @param StoreOrderOrderInvoiceDao $dao
     */
    public function __construct(StoreOrderOrderInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    public function orderCount(array $where)
    {
        //全部订单
        $data['all'] = (string)$this->dao->getCount(['time' => $where['time'], 'is_system_del' => 0]);
        //普通订单
        $data['general'] = (string)$this->dao->getCount(['type' => 1, 'is_system_del' => 0]);
        //拼团订单
        $data['pink'] = (string)$this->dao->getCount(['type' => 2, 'is_system_del' => 0]);
        //秒杀订单
        $data['seckill'] = (string)$this->dao->getCount(['type' => 3, 'is_system_del' => 0]);
        //砍价订单
        $data['bargain'] = (string)$this->dao->getCount(['type' => 4, 'is_system_del' => 0]);
        switch ($where['type']) {
            case 0:
                $data['statusAll'] = $data['all'];
                break;
            case 1:
                $data['statusAll'] = $data['general'];
                break;
            case 2:
                $data['statusAll'] = $data['pink'];
                break;
            case 3:
                $data['statusAll'] = $data['seckill'];
                break;
            case 4:
                $data['statusAll'] = $data['bargain'];
                break;
        }
        //未支付
        $data['unpaid'] = (string)$this->dao->getCount(['status' => 0, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //未发货
        $data['unshipped'] = (string)$this->dao->getCount(['status' => 1, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待收货
        $data['untake'] = (string)$this->dao->getCount(['status' => 2, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待核销
        $data['write_off'] = (string)$this->dao->getCount(['status' => 5, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待评价
        $data['unevaluate'] = (string)$this->dao->getCount(['status' => 3, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //交易完成
        $data['complete'] = (string)$this->dao->getCount(['status' => 4, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //退款中
        $data['refunding'] = (string)$this->dao->getCount(['status' => -1, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //已退款
        $data['refund'] = (string)$this->dao->getCount(['status' => -2, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //删除订单
        $data['del'] = (string)$this->dao->getCount(['status' => -4, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        return $data;
    }
    /**
     * 获取开票列表
     * @param $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $list] = $this->getPageValue();
        $list = $this->dao->getList($where, 'o.*,i.id as invoice_id,i.header_type,i.type,i.name,i.duty_number,i.drawer_phone,i.email,i.tell,i.address,i.bank,i.card_number,i.is_invoice,invoice_number,i.remark as invoice_reamrk,i.invoice_time,i.add_time as invoice_add_time', 'add_time desc', $page, $list);
        /** @var StorePinkServices $pinkService */
        $pinkService = app()->make(StorePinkServices::class);
        foreach ($list as &$item){
            $item['add_time'] = strtotime($item['add_time']);
            $item['invoice_add_time'] = date('Y-m-d H:i:s', $item['invoice_add_time']);
            $pinkStatus = $pinkService->value(['order_id_key' => $item['id']], 'status');
            $item['pinkStatus'] = $pinkStatus;
        }
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $list = $storeOrderServices->tidyOrderList($list);
        $count = $this->dao->getCount($where);
        return compact('list', 'count');
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
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        /** @var UserInvoiceServices $userInvoiceServices */
        $userInvoiceServices = app()->make(UserInvoiceServices::class);
        $order = $storeOrderServices->getOne(['order_id|id' => $order_id, 'is_del' => 0]);
        if (!$order) {
            throw new ValidateException('订单不存在');
        }
        //检测再带查询
        $invoice = $userInvoiceServices->checkInvoice($invoice_id, $uid);

        if ($this->dao->getOne(['order_id' => $order['id'], 'uid' => $uid])) {
            throw new ValidateException('发票已申请，正在审核打印中');
        }
        if ($order['refund_status'] == 2) {
            throw new ValidateException('订单已退款');
        }
        if ($order['refund_status'] == 1) {
            throw new ValidateException('正在申请退款中');
        }
        unset($invoice['id'], $invoice['add_time']);
        $data = [];
        $data['order_id'] = $order['id'];
        $data['invoice_id'] = $invoice_id;
        $data['add_time'] = time();
        $data = array_merge($data, $invoice);
        if (!$re = $this->dao->save($data)) {
            throw new ValidateException('申请失败，请稍后重试');
        }
        return ['id' => $re->id];
    }

    public function setInvoice(int $id)
    {
        $orderInvoice = $this->dao->get($id);
        if(!$orderInvoice){
            throw new ValidateException('数据不存在');
        }
        if($orderInvoice->is_invoice){
            return true;
        }
        $data = [];
        $data['is_invoice'] = 1;
        $data['invoice_time'] = time();
        if(!$this->dao->update($id,$data,'id')){
            throw new ValidateException('设置失败，请重试');
        }
        return true;
    }
}
