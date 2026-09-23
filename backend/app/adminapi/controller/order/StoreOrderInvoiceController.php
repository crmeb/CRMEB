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
namespace app\adminapi\controller\order;

use app\adminapi\controller\AuthController;
use app\services\order\OrderInvoiceServices;
use app\dao\order\OrderCenterDao;
use app\services\order\OrderCenterServices;
use crmeb\services\invoice\Invoice;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 发票管理
 * Class StoreOrderInvoice
 * @package app\adminapi\controller\order
 */
class StoreOrderInvoiceController extends AuthController
{
    /**
     * @var OrderInvoiceServices
     */
    protected $services;



    /**
     * StoreOrderInvoice constructor.
     * @param App $app
     * @param OrderInvoiceServices $services
     */
    public function __construct(App $app, OrderInvoiceServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取订单类型数量
     * @return mixed
     */
    public function chart()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['real_name', ''],
            ['field_key', ''],
            [['type', 'd'], 0],
        ]);
        $data = $this->services->chart($where);
        return app('json')->success($data);
    }

    /**
     * 查询发票列表
     * @return mixed
     */
    public function list()
    {
        $where = $this->request->getMore([
            ['status', 0],
            ['real_name', ''],
            ['header_type', ''],
            ['type', ''],
            ['data', '', '', 'time'],
            ['field_key', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }


    /**
     * 设置发票状态
     * @param string $id
     * @return mixed
     */
    public function setInvoice($id = '')
    {
        if (empty($id)) return app('json')->fail('参数错误');
        $data = $this->request->postMore([
            ['is_invoice', 0],
            ['invoice_number', 0],
            ['remark', '']
        ]);
        if ($data['is_invoice'] == 1 && !$data['invoice_number']) {
            return app('json')->fail('请填写开票号');
        }
        $this->services->setInvoice((int)$id, $data);
        return app('json')->success('设置成功');
    }

    /**
     * 订单详情
     * @param OrderCenterDao $orderDao
     * @param OrderCenterServices $orderServices
     * @param $id 订单id
     * @return mixed
     */
    public function orderInfo(OrderCenterDao $orderDao, OrderCenterServices $orderServices, $id)
    {
        if (!$id || !($orderInfo = $orderDao->get($id))) {
            return app('json')->fail('订单不存在');
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) {
            return app('json')->fail('用户信息不存在');
        }
        $userInfo = $userInfo->hidden(['pwd', 'add_ip', 'last_ip', 'login_type'])->toArray();
        $userInfo['spread_name'] = '';
        if ($userInfo['spread_uid'])
            $userInfo['spread_name'] = $services->value(['uid' => $userInfo['spread_uid']], 'nickname');
        // 订单中台统一详情，替代旧版 tidyOrder
        $orderInfo = $orderServices->getDetail((int)$id);
        // 会员折扣优惠金额取中台订单级字段
        $orderInfo['vip_true_price'] = $orderInfo['vip_discount'] ?? 0;
        $orderInfo['add_time'] = $orderInfo['add_time'] ? date('Y-m-d H:i:s', (int)$orderInfo['add_time']) : '';
        $invoice = $this->services->getOne(['order_id' => (int)$id]);
        return app('json')->success(compact('orderInfo', 'userInfo', 'invoice'));
    }

    /**
     * 获取电子发票配置信息
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function elecInvoiceConfig()
    {
        $data = [
            'elec_invoice' => (int)sys_config('elec_invoice'),
            'auto_invoice' => (int)sys_config('auto_invoice'),
            'elec_invoice_cate' => (int)sys_config('elec_invoice_cate'),
            'elec_invoice_cate_name' => sys_config('elec_invoice_cate_name'),
            'elec_invoice_tax_rate' => (int)sys_config('elec_invoice_tax_rate')
        ];
        return app('json')->success($data);
    }

    /**
     * 获取发票开具页面iframe地址
     * @param $id
     * @return \think\Response
     * @throws \ReflectionException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/13
     */
    public function invoiceIssuanceUrl($id)
    {
        if (sys_config('elec_invoice', 1) != 1) {
            return app('json')->fail('电子发票功能未开启，请在一号通中开启并且在商城后台一号通配置中开启');
        }
        $info = $this->services->getOne(['id' => $id]);
        // 一号通开票页面的唯一标识使用中台订单号 order_no
        $unique = app()->make(OrderCenterDao::class)->value(['id' => $info['order_id']], 'order_no');
        // 从订单中台 extra_info.items 读取商品快照（旧 eb_store_order_cart_info 已废弃）
        $cartInfo = [];
        $orderCenter = app()->make(OrderCenterDao::class)->get((int)$info['order_id'], ['extra_info']);
        if ($orderCenter) {
            $extra = $orderCenter['extra_info'];
            $extra = is_string($extra) ? json_decode($extra, true) : ($extra ?? []);
            $cartInfo = $extra['items'] ?? [];
        }
        $goods = [];
        foreach ($cartInfo as $item) {
            $goods[] = [
                'store_name' => $item['store_name'] ?? '',
                'unit_price' => bcadd((string)($item['true_price'] ?? '0.00'), (string)($item['postage_price'] ?? '0.00'), 2),
                'num' => $item['cart_num'] ?? 1
            ];
        }
        $data = [];
        $data['unique'] = $unique;
        $data['goods'] = $goods;
        $data['account_name'] = $info['name'];
        $data['email'] = $info['email'];
        if ($info['header_type'] == 1) {
            $data['invoice_type'] = 82;
            $data['is_enterprise'] = 0;
        } else {
            $data['invoice_type'] = $info['type'] == 1 ? 82 : 81;
            $data['tax_id'] = $info['duty_number'];
            $data['is_enterprise'] = 1;
        }
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        return app('json')->success($invoice->invoiceIssuanceUrl($data));
    }

    /**
     * 保存发票信息
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/14
     */
    public function saveInvoiceInfo($id)
    {
        $data = $this->request->postMore([
            ['invoice_num', ''],
            ['invoice_type', ''],
            ['invoice_serial_number', ''],
        ]);
        $info = $this->services->getOne(['id' => $id]);
        if (!$info) return app('json')->fail('发票不存在');
        // 开票回填的唯一标识使用中台订单号 order_no
        $data['unique_num'] = app()->make(OrderCenterDao::class)->value(['id' => $info['order_id']], 'order_no');
        $data['is_invoice'] = 1;
        $data['invoice_time'] = time();
        $this->services->update($id, $data);
        return app('json')->success('保存成功');
    }

    /**
     * 查看发票详情
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/14
     */
    public function invoiceInfo($id)
    {
        $info = $this->services->getOne(['id' => $id]);
        if (!$info) return app('json')->fail('发票不存在');
        // 发票未开具时无需请求一号通
        if (empty($info['invoice_num'])) return app('json')->fail('发票尚未开具');
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        return app('json')->success($invoice->invoiceInfo($info['invoice_num']));
    }

    /**
     * 下载发票
     * @param $id
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/14
     */
    public function downInvoice($id)
    {
        $info = $this->services->getOne(['id' => $id]);
        if (!$info) return app('json')->fail('发票不存在');
        // 发票未开具时无需请求一号通
        if (empty($info['invoice_num'])) return app('json')->fail('发票尚未开具');
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        return app('json')->success($invoice->downloadInvoice($info['invoice_num']));
    }

    /**
     * 电子发票分类
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/15
     */
    public function invoiceCategory()
    {
        $where = $this->request->getMore([
            ['name', ''],
            ['page', 1],
            ['limit', 100],
        ]);
        $invoice = app()->make(Invoice::class, [yihaotong_config()]);
        return app('json')->success($invoice->category($where));
    }

    /**
     * 开具发票
     * @param $id
     * @return \think\Response
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
        $this->services->invoiceIssuance($id);
        return app('json')->success('开票成功');
    }

    /**
     * 负数发票开具
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function redInvoiceIssuance($id)
    {
        $this->services->redInvoiceIssuance($id);
        return app('json')->success('开具负数发票成功');
    }
}
