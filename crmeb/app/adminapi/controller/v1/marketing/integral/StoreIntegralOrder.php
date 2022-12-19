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
namespace app\adminapi\controller\v1\marketing\integral;

use app\adminapi\controller\AuthController;
use app\services\serve\ServeServices;
use app\services\activity\integral\{
    StoreIntegralOrderServices,
    StoreIntegralOrderStatusServices
};
use app\services\order\StoreOrderDeliveryServices;
use app\services\shipping\ExpressServices;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 订单管理
 * Class StoreOrder
 * @package app\controller\admin\v1\order
 */
class StoreIntegralOrder extends AuthController
{
    /**
     * StoreIntegralOrder constructor.
     * @param App $app
     * @param StoreIntegralOrderServices $service
     * @method temp
     */
    public function __construct(App $app, StoreIntegralOrderServices $service)
    {
        parent::__construct($app);
        $this->services = $service;
    }

    /**
     * 获取订单类型数量
     * @return mixed
     */
    public function chart()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['product_id', '']
        ]);
        $data = $this->services->orderCount($where);
        return app('json')->success($data);
    }

    /**
     * 获取订单列表
     * @return mixed
     */
    public function lst()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['data', '', '', 'time'],
            ['order', ''],
            ['field_key', ''],
            ['product_id', '']
        ]);
        $where['is_system_del'] = 0;
        return app('json')->success($this->services->getOrderList($where, ['*']));
    }

    /**
     * 获取快递公司
     * @return mixed
     */
    public function express(ExpressServices $services)
    {
        [$status] = $this->request->getMore([
            ['status', ''],
        ], true);
        if ($status != '') $data['status'] = $status;
        $data['is_show'] = 1;
        return app('json')->success($services->express($data));
    }

    /**
     * 批量删除用户已经删除的订单
     * @return mixed
     */
    public function del_orders()
    {
        [$ids, $all, $where] = $this->request->postMore([
            ['ids', []],
            ['where', []],
        ], true);
        if ($this->services->delOrders($ids)) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 删除订单
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        if ($this->services->delOrder($id)) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 订单发送货
     * @param $id
     * @return mixed
     */
    public function update_delivery($id)
    {
        $data = $this->request->postMore([
            ['type', 1],
            ['delivery_name', ''],//快递公司名称
            ['delivery_id', ''],//快递单号
            ['delivery_code', ''],//快递公司编码

            ['express_record_type', 2],//发货记录类型
            ['express_temp_id', ""],//电子面单模板
            ['to_name', ''],//寄件人姓名
            ['to_tel', ''],//寄件人电话
            ['to_addr', ''],//寄件人地址

            ['sh_delivery_name', ''],//送货人姓名
            ['sh_delivery_id', ''],//送货人电话
            ['sh_delivery_uid', ''],//送货人ID

            ['fictitious_content', '']//虚拟发货内容
        ]);
        $this->services->delivery((int)$id, $data);
        return app('json')->success(100010);
    }

    /**
     * 确认收货
     * @param $id
     * @return mixed
     */
    public function take_delivery($id)
    {
        if (!$id) return app('json')->fail(100100);
        $order = $this->services->get($id);
        if (!$order)
            return app('json')->fail(100026);
        if ($order['status'] == 3)
            return app('json')->fail(400114);
        if ($order['status'] == 2)
            $data['status'] = 3;
        else
            return app('json')->fail(400115);

        if (!$this->services->update($id, $data)) {
            return app('json')->fail(400116);
        } else {
            //增加收货订单状态
            /** @var StoreIntegralOrderStatusServices $statusService */
            $statusService = app()->make(StoreIntegralOrderStatusServices::class);
            $statusService->save([
                'oid' => $order['id'],
                'change_type' => 'take_delivery',
                'change_message' => '已收货',
                'change_time' => time()
            ]);
            return app('json')->success(400117);
        }
    }

    /**
     * 订单详情
     * @param $id 订单id
     * @return mixed
     */
    public function order_info($id)
    {
        if (!$id || !($orderInfo = $this->services->get($id))) {
            return app('json')->fail(400118);
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) return app('json')->fail(400119);
        $userInfo = $userInfo->hidden(['pwd', 'add_ip', 'last_ip', 'login_type']);
        $orderInfo = $this->services->tidyOrder($orderInfo->toArray());
        $userInfo = $userInfo->toArray();
        return app('json')->success(compact('orderInfo', 'userInfo'));
    }

    /**
     * 查询物流信息
     * @param $id 订单id
     * @return mixed
     */
    public function get_express($id, ExpressServices $services)
    {
        if (!$id || !($orderInfo = $this->services->get($id)))
            return app('json')->fail(400118);
        if ($orderInfo['delivery_type'] != 'express' || !$orderInfo['delivery_id'])
            return app('json')->fail(400120);

        $cacheName = 'integral' . $orderInfo['order_id'] . $orderInfo['delivery_id'];

        $data['delivery_name'] = $orderInfo['delivery_name'];
        $data['delivery_id'] = $orderInfo['delivery_id'];
        $data['result'] = $services->query($cacheName, $orderInfo['delivery_id'], $orderInfo['delivery_code'] ?? null, $orderInfo['user_phone']);
        return app('json')->success($data);
    }

    /**
     * 获取修改配送信息表单结构
     * @param $id 订单id
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function distribution($id)
    {
        if (!$id) {
            return app('json')->fail(400118);
        }
        return app('json')->success($this->services->distributionForm((int)$id));
    }

    /**
     * 修改配送信息
     * @param $id  订单id
     * @return mixed
     */
    public function update_distribution($id)
    {
        $data = $this->request->postMore([['delivery_name', ''], ['delivery_code', ''], ['delivery_id', '']]);
        if (!$id) return app('json')->fail(100100);
        $this->services->updateDistribution($id, $data);
        return app('json')->success(100001);
    }


    /**
     * 修改备注
     * @param $id
     * @return mixed
     */
    public function remark($id)
    {
        $data = $this->request->postMore([['remark', '']]);
        if ($this->services->remark($id, $data['remark'])) {
            return app('json')->success(100024);
        } else {
            return app('json')->fail(100025);
        }
    }

    /**
     * 获取订单状态列表并分页
     * @param $id
     * @return mixed
     */
    public function status(StoreIntegralOrderStatusServices $services, $id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($services->getStatusList(['oid' => $id])['list']);
    }

    /**
     * 易联云打印机打印
     * @param $id
     * @return mixed
     */
    public function order_print($id)
    {
        if (!$id) return app('json')->fail(100100);
        $order = $this->services->get($id);
        if (!$order) {
            return app('json')->fail(400118);
        }
        $res = $this->services->orderPrint($order);
        if ($res) {
            return app('json')->success(400121);
        } else {
            return app('json')->fail(400122);
        }
    }

    /**
     * 电子面单模板
     * @param $com
     * @return mixed
     */
    public function expr_temp(ServeServices $services, $com)
    {
        if (!$com) {
            return app('json')->fail(400123);
        }
        $list = $services->express()->temp($com);
        return app('json')->success($list);
    }

    /**
     * 获取模板
     */
    public function express_temp(ServeServices $services)
    {
        $data = $this->request->getMore([['com', '']]);
        $tpd = $services->express()->temp($data['com']);
        return app('json')->success($tpd['data']);
    }

    /**
     * 订单发货后打印电子面单
     * @param $order_id
     * @param StoreOrderDeliveryServices $storeOrderDeliveryServices
     * @return mixed
     */
    public function order_dump($order_id, StoreOrderDeliveryServices $storeOrderDeliveryServices)
    {
        return app('json')->success($storeOrderDeliveryServices->orderDump($order_id));

    }

    /**
     * 获取配置信息
     * @return mixed
     */
    public function getDeliveryInfo()
    {
        return app('json')->success([
            'express_temp_id' => sys_config('config_export_temp_id'),
            'id' => sys_config('config_export_id'),
            'to_name' => sys_config('config_export_to_name'),
            'to_tel' => sys_config('config_export_to_tel'),
            'to_add' => sys_config('config_export_to_address'),
            'export_open' => (bool)((int)sys_config('config_export_open'))
        ]);
    }

}
