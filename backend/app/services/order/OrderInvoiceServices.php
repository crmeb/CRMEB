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

namespace app\services\order;


use app\jobs\OrderInvoiceJob;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use app\dao\order\OrderCenterDao;
use app\model\order\OrderCenter;
use crmeb\services\invoice\Invoice;
use app\dao\order\OrderInvoiceDao;
use app\services\user\UserInvoiceServices;
use app\services\user\UserServices;
use think\facade\Log;


/**
 * Class OrderInvoiceServices
 * @package app\services\order
 */
class OrderInvoiceServices extends BaseService
{
    /**
     * @var OrderInvoiceDao
     */
    protected $dao;

    /**
     * OrderInvoiceServices constructor.
     * @param OrderInvoiceDao $dao
     */
    public function __construct(OrderInvoiceDao $dao)
    {
        $this->dao = $dao;
    }

    public function chart(array $where)
    {
        $where['is_pay'] = 1;
        $where['is_del'] = 0;
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
        $data['elec_invoice'] = (int)sys_config('elec_invoice', 0);
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
        $field = 'id as invoice_id,order_id,header_type,type,name,duty_number,drawer_phone,email,tell,address,bank,card_number,is_invoice,invoice_number,remark as invoice_reamrk,invoice_time,add_time as invoice_add_time,unique_num,invoice_num,invoice_type,invoice_serial_number,red_invoice_num';
        $where['is_pay'] = 1;
        $where['is_del'] = 0;
        // 订单中台关联查询：仅选择中台表实际存在的字段
        $list = $this->dao->getList($where, $field, ['order' => function ($query) {
            $query->field('id,order_no,uid,pay_price,status_code,refund_status,extra_info,add_time');
        }], 'add_time desc', $page, $list);
        // 批量取下单用户，作为收货人姓名电话的兜底数据
        $uids = array_filter(array_unique(array_column(array_column($list, 'order'), 'uid')));
        $userList = $uids ? app()->make(UserServices::class)->getColumn([['uid', 'in', $uids]], 'uid,real_name,nickname,phone', 'uid') : [];
        foreach ($list as &$item) {
            $order = $item['order'] ?? [];
            $extra = $order['extra_info'] ?? '';
            $extra = is_string($extra) ? (json_decode($extra, true) ?: []) : ($extra ?: []);
            $contact = $extra['contact'] ?? [];
            $user = $userList[$order['uid']] ?? [];
            $item['id'] = $order['id'] ?? 0;
            // 前端契约：列表展示的订单号对应中台 order_no
            $item['order_id'] = $order['order_no'] ?? '';
            $item['pay_price'] = $order['pay_price'] ?? 0.00;
            // 收货人姓名电话优先取订单快照，空串需回退下单用户资料（?? 只拦 null，拦不住空串）
            $contactName = trim((string)($contact['name'] ?? ''));
            $contactPhone = trim((string)($contact['phone'] ?? ''));
            $item['real_name'] = $contactName !== '' ? $contactName : trim((string)($user['real_name'] ?? ($user['nickname'] ?? '')));
            $item['user_phone'] = $contactPhone !== '' ? $contactPhone : trim((string)($user['phone'] ?? ''));
            $item['status'] = $this->statusToLegacy($order['status_code'] ?? '');
            $item['refund_status'] = $order['refund_status'] ?? 0;
            $item['add_time'] = date('Y-m-d H:i:s', $order['add_time'] ?? $item['invoice_add_time'] ?? time());
            $item['invoice_add_time'] = date('Y-m-d H:i:s', $item['invoice_add_time']);
            // 中台关联原始数据不再下发给前端
            unset($item['order']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 订单中台状态码映射为旧版订单状态数值（兼容发票模块前端展示）
     * @param string $statusCode
     * @return int
     */
    public function statusToLegacy(string $statusCode): int
    {
        return match ($statusCode) {
            OrderCenter::STATUS_PROCESSING => 0,
            OrderCenter::STATUS_PENDING_REVIEW => 2,
            OrderCenter::STATUS_COMPLETED => 3,
            OrderCenter::STATUS_REFUNDED => -2,
            default => 0,
        };
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
        $where['is_del'] = 0;
        $list = $this->dao->getList($where, '*', ['order' => function ($query) {
            $query->field('id,order_no,pay_price,extra_info');
        }], 'add_time desc', $page, $list);
        foreach ($list as &$item) {
            $order = $item['order'] ?? [];
            $extra = $order['extra_info'] ?? '';
            $extra = is_string($extra) ? (json_decode($extra, true) ?: []) : ($extra ?: []);
            $order['cartInfo'] = $this->buildLegacyCartInfo($extra);
            // 移动端跳转开票详情携带的订单号对应中台 order_no
            $order['order_id'] = $order['order_no'] ?? '';
            unset($order['extra_info']);
            $item['order'] = $order;
        }
        return $list;
    }

    /**
     * 由订单中台商品快照合成旧版 cartInfo 结构（兼容移动端旧组件）
     * @param array $extra 解析后的 extra_info
     * @return array
     */
    public function buildLegacyCartInfo(array $extra): array
    {
        $cartInfo = [];
        foreach ($extra['items'] ?? [] as $product) {
            $cartInfo[] = [
                'cart_num' => $product['cart_num'] ?? 1,
                'truePrice' => $product['true_price'] ?? 0,
                'productInfo' => [
                    'image' => $product['image'] ?? '',
                    'store_name' => $product['store_name'] ?? '',
                    'attrInfo' => ['suk' => $product['attr_info']['suk'] ?? ($product['sku'] ?? '')],
                ],
            ];
        }
        return $cartInfo;
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
        if (!$order_id) throw new AdminException('参数错误');
        if (!$invoice_id) throw new AdminException('请选择发票');

        /** @var OrderCenterDao $orderCenterServices */
        $orderCenterServices = app()->make(OrderCenterDao::class);
        /** @var UserInvoiceServices $userInvoiceServices */
        $userInvoiceServices = app()->make(UserInvoiceServices::class);
        // 订单中台以 order_no 为全局单号，兼容传订单号或中台ID
        $order = $orderCenterServices->getOne(['order_no|id' => $order_id, 'is_del' => 0]);
        if (!$order) {
            throw new AdminException('订单不存在');
        }
        // 校验订单归属，防止为他人订单申请开票
        if ((int)$order['uid'] !== $uid) {
            throw new AdminException('订单不存在');
        }
        //检测再带查询
        $invoice = $userInvoiceServices->checkInvoice($invoice_id, $uid);

        if ($this->dao->getOne(['order_id' => $order['id'], 'uid' => $uid])) {
            throw new AdminException('发票已申请');
        }
        if ($order['refund_status'] == 2) {
            throw new AdminException('订单已退款');
        }
        if ($order['refund_status'] == 1) {
            throw new AdminException('正在申请退款中');
        }
        // 仅已支付订单可申请开票，未支付订单的开票记录不在列表中展示
        if ($order['pay_status'] == 0) {
            throw new AdminException('订单未支付，无法申请开票');
        }
        // 显式构建申请数据，避免抬头表多余字段（is_default 等）写入开票表
        $data = [];
        $data['uid'] = $uid;
        $data['category'] = 'order';
        $data['order_id'] = (int)$order['id'];
        $data['invoice_id'] = $invoice_id;
        $data['header_type'] = $invoice['header_type'];
        $data['type'] = $invoice['type'];
        $data['name'] = $invoice['name'];
        $data['duty_number'] = $invoice['duty_number'];
        $data['drawer_phone'] = $invoice['drawer_phone'];
        $data['email'] = $invoice['email'];
        $data['tell'] = $invoice['tell'];
        $data['address'] = $invoice['address'];
        $data['bank'] = $invoice['bank'];
        $data['card_number'] = $invoice['card_number'];
        $data['is_pay'] = $order['pay_status'] == 1 ? 1 : 0;
        $data['add_time'] = time();
        if (!$re = $this->dao->save($data)) {
            throw new AdminException('申请失败');
        }
        if (sys_config('elec_invoice', 1) == 1 && sys_config('auto_invoice', 1) == 1 && $data['is_pay'] == 1) {
            //自动开票
            OrderInvoiceJob::dispatchSecs(10, 'autoInvoice', [$re->id]);
        }

        return ['id' => $re->id];
    }

    /**
     * 下单勾选发票时创建开票申请记录
     * 列表/支付回写以 eb_order_invoice 为准；中台 order_center.invoice 只作快照展示
     * @param int $uid 用户uid
     * @param int $orderCenterId 订单中台主键
     * @param array $invoice 用户发票抬头（eb_user_invoice）
     * @param int $isPay 是否已支付 0/1
     * @return int|null 开票记录id
     */
    public function createFromOrder(int $uid, int $orderCenterId, array $invoice, int $isPay = 0): ?int
    {
        if (!$orderCenterId || empty($invoice)) {
            return null;
        }
        // 同一订单已有申请则跳过，避免重复写入
        if ($this->dao->getOne(['order_id' => $orderCenterId, 'is_del' => 0])) {
            return null;
        }
        $data = [];
        $data['uid'] = $uid;
        $data['category'] = 'order';
        $data['order_id'] = $orderCenterId;
        $data['invoice_id'] = (int)($invoice['id'] ?? 0);
        $data['header_type'] = $invoice['header_type'] ?? 1;
        $data['type'] = $invoice['type'] ?? 1;
        $data['name'] = $invoice['name'] ?? '';
        $data['duty_number'] = $invoice['duty_number'] ?? '';
        $data['drawer_phone'] = $invoice['drawer_phone'] ?? '';
        $data['email'] = $invoice['email'] ?? '';
        $data['tell'] = $invoice['tell'] ?? '';
        $data['address'] = $invoice['address'] ?? '';
        $data['bank'] = $invoice['bank'] ?? '';
        $data['card_number'] = $invoice['card_number'] ?? '';
        $data['is_pay'] = $isPay;
        $data['add_time'] = time();
        if (!$re = $this->dao->save($data)) {
            return null;
        }
        return (int)$re->id;
    }

    public function setInvoice(int $id, array $data)
    {
        $orderInvoice = $this->dao->get($id);
        if (!$orderInvoice) {
            throw new AdminException('数据不存在');
        }
        if ($data['is_invoice'] == 1) {
            $data['invoice_time'] = time();
        }
        if (!$this->dao->update($id, $data, 'id')) {
            throw new AdminException('设置失败');
        }
        return true;
    }

    /**
     * 开具发票
     * @param $id
     * @return bool
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
     */
    public function invoiceIssuance($id)
    {
        if (sys_config('elec_invoice', 1) != 1) {
            throw new AdminException('电子发票功能未开启，请在一号通中开启并且在商城后台一号通配置中开启');
        }
        $info = $this->dao->getOne(['id' => $id]);
        if (!$info) {
            throw new AdminException('发票不存在');
        }
        /** @var OrderCenterDao $orderDao */
        $orderDao = app()->make(OrderCenterDao::class);
        $orderInfo = $orderDao->get($info['order_id']);
        if (!$orderInfo) {
            throw new AdminException('订单不存在');
        }
        // 从订单中台 extra_info.items 读取商品快照（旧 eb_store_order_cart_info 已废弃）
        $extra = $orderInfo['extra_info'] ?? null;
        $extra = is_string($extra) ? json_decode($extra, true) : ($extra ?? []);
        $cartInfo = $extra['items'] ?? [];
        $data = [];
        $data['is_tax_inclusive'] = 1;
        $data['account_name'] = $info['name'];
        $data['bank_name'] = '';
        $data['bank_account'] = '';
        $data['telephone'] = $info['drawer_phone'];
        $data['company_address'] = '';
        $data['drawer'] = '';
        $data['amount'] = $orderInfo['pay_price'];
        if ($info['header_type'] == 1) {
            $data['invoice_type'] = 82;
            $data['tax_id'] = '';
            $data['is_enterprise'] = 0;
        } else {
            $data['invoice_type'] = $info['type'] == 1 ? 82 : 81;
            $data['tax_id'] = $info['duty_number'];
            $data['is_enterprise'] = 1;
        }
        $goods = [];
        foreach ($cartInfo as $item) {
            $goods[] = [
                'cate_id' => sys_config('elec_invoice_cate', 1799),
                'store_name' => $item['store_name'] ?? '',
                'unit_price' => bcadd((string)($item['true_price'] ?? '0.00'), (string)($item['postage_price'] ?? '0.00'), 2),
                'num' => $item['cart_num'] ?? 1,
                'tax_rate' => sys_config('elec_invoice_tax_rate', 13),
            ];
        }
        $data['goods'] = $goods;
        // 一号通开票的唯一标识使用中台订单号 order_no
        try {
            $invoice = app()->make(Invoice::class, [yihaotong_config()]);
            $res = $invoice->invoiceIssuance($orderInfo['order_no'], $data);
            if ($res['status'] == 200) {
                $this->dao->update($id, [
                    'is_invoice' => 1,
                    'unique_num' => $orderInfo['order_no'],
                    'invoice_num' => $res['data']['invoice_num'],
                    'invoice_type' => $res['data']['invoice_type'],
                    'invoice_serial_number' => $res['data']['invoice_serial_number'],
                    'invoice_time' => time()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('自动开具发票失败，失败原因：' . $e->getMessage());
        }
        return true;
    }

    /**
     * 未开发票自动开具电子发票
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
     */
    public function autoInvoice()
    {
        if (sys_config('elec_invoice', 1) != 1) {
            return true;
        }
        if (sys_config('auto_invoice', 1) == 1) {
            $list = $this->dao->getColumn([
                ['is_pay', '=', 1],
                ['is_invoice', '=', 1],
                ['unique_num', '=', ''],
                ['is_del', '=', 0],
                ['add_time', '<', time() - 60],
            ], 'id');
            if ($list) {
                foreach ($list as $item) {
                    //自动开票
                    OrderInvoiceJob::dispatchSecs(10, 'autoInvoice', [$item]);
                }
            }
        }
        return true;
    }

    /**
     * 退款订单自动冲红
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
     */
    public function autoInvoiceRed()
    {
        if (sys_config('elec_invoice', 1) != 1) {
            return true;
        }
        if (sys_config('auto_invoice', 1) == 1) {
            $list = $this->dao->getColumn([
                ['is_pay', '=', 1],
                ['is_invoice', '=', 1],
                ['unique_num', '<>', ''],
                ['red_invoice_num', '=', ''],
                ['is_del', '=', 1],
                ['add_time', '<', time() - 60],
            ], 'id');
            if ($list) {
                foreach ($list as $item) {
                    //自动冲红
                    OrderInvoiceJob::dispatchSecs(10, 'autoInvoiceRed', [$item]);
                }
            }
        }
        return true;
    }

    /**
     * 负数发票开具
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function redInvoiceIssuance($id)
    {
        $invoiceInfo = $this->dao->get($id);
        if (!$invoiceInfo) throw new AdminException('发票不存在');
        if ($invoiceInfo['is_pay'] == 0 || $invoiceInfo['is_invoice'] == 0 || $invoiceInfo['unique_num'] === '') {
            throw new AdminException('发票状态有误，请检查');
        }
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        // 驱动契约要求 camelCase 键名 invoiceNum
        $res = $invoice->redInvoiceIssuance(['invoiceNum' => $invoiceInfo['invoice_num'], 'apply_type' => '01']);
        if ($res['status'] != 200) throw new AdminException('开具负数发票失败，请检查');
        $this->dao->update($id, ['red_invoice_num' => 1]);
        return true;
    }
}
