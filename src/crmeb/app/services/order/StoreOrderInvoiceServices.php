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


use app\services\BaseServices;
use FormBuilder\components\Validate;
use think\exception\ValidateException;
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
        //全部
        $data['all'] = (string)$this->dao->count(['is_pay' => 1, 'time' => $where['time']]);
        //待开
        $data['noOpened'] = (string)$this->dao->count(['is_pay' => 1, 'time' => $where['time'], 'type' => 1]);
        //已开
        $data['opened'] = (string)$this->dao->count(['is_pay' => 1, 'time' => $where['time'], 'type' => 2]);
        //退款
        $data['refund'] = (string)$this->dao->count(['is_pay' => 1, 'time' => $where['time'], 'type' => 3]);
        return $data;
    }

    /**
     * 后台获取开票列表
     * @param $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $list] = $this->getPageValue();
        $field = 'id as invoice_id,order_id,header_type,type,name,duty_number,drawer_phone,email,tell,address,bank,card_number,is_invoice,invoice_number,remark as invoice_reamrk,invoice_time,add_time as invoice_add_time';
        $where['is_pay'] = 1;
        $list = $this->dao->getList($where, $field, ['order' => function ($query) {
            $query->field('id,order_id,pay_price,add_time,real_name,user_phone');
        }], 'add_time desc', $page, $list);
        foreach ($list as &$item) {
            $item['id'] = $item['order']['id'] ?? 0;
            $item['order_id'] = $item['order']['order_id'] ?? '';
            $item['pay_price'] = $item['order']['pay_price'] ?? 0.00;
            $item['real_name'] = $item['order']['real_name'] ?? '';
            $item['user_phone'] = $item['order']['user_phone'] ?? '';
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
        $data['category'] = 'order';
        $data['order_id'] = $order['id'];
        $data['invoice_id'] = $invoice_id;
        $data['add_time'] = time();
        $data = array_merge($data, $invoice);
        if (!$re = $this->dao->save($data)) {
            throw new ValidateException('申请失败，请稍后重试');
        }
        return ['id' => $re->id];
    }

    public function setInvoice(int $id, array $data)
    {
        $orderInvoice = $this->dao->get($id);
        if (!$orderInvoice) {
            throw new ValidateException('数据不存在');
        }
        $data['invoice_time'] = time();
        if (!$this->dao->update($id, $data, 'id')) {
            throw new ValidateException('设置失败，请重试');
        }
        return true;
    }
}
