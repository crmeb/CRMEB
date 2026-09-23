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

namespace app\services\order;

use app\services\activity\integral\StoreIntegralOrderServices;
use crmeb\basic\BaseService;
use app\dao\order\OrderCenterDao;

use crmeb\exceptions\AdminException;
use crmeb\utils\FormBuilder as Form;
use app\services\shipping\ExpressServices;
use app\services\driver\express\ExpressConfigServices;
use crmeb\services\logistics\Express;
use app\services\driver\express\ExpressDriverServices;
use app\services\user\UserServices;
use app\services\order\AfterSaleServices;
use think\facade\Log;

/**
 * 订单发货
 * Class OrderDeliveryServices
 * @package app\services\order
 */
class OrderDeliveryServices extends BaseService
{
    /**
     * @var OrderCenterDao
     */
    protected $dao;

    /**
     * 构造方法
     * OrderDeliveryServices constructor.
     * @param OrderCenterDao $dao
     */
    public function __construct(OrderCenterDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单发货
     * @param int $id
     * @param array $data
     * @return array
     */
    public function delivery(int $id, array $data)
    {
        $orderInfo = $this->dao->get($id);
        if (!$orderInfo) {
            throw new AdminException('订单未能查到,不能发货');
        }
        $this->hydrateCenterOrderInfo($orderInfo);
        if ($orderInfo->is_del) {
            throw new AdminException('订单已删除,不能发货');
        }
        if ((int)$orderInfo->delivery_time > 0) {
            throw new AdminException('订单已发货请勿重复操作');
        }
        if ($orderInfo->shipping_type == 2) {
            throw new AdminException('核销订单不能发货');
        }
        if (!$this->isCombinationReady($orderInfo)) {
            throw new AdminException('拼团未完成暂不能发货');
        }

        if ($data['type'] == 1) {
            // 检测快递公司编码
            /** @var ExpressServices $expressServices */
            $expressServices = app()->make(ExpressServices::class);
            if (!$expressServices->be(['code' => $data['delivery_code']])) {
                throw new AdminException('请核对快递公司编码');
            }
        }

        // 售后拦截：存在处理中售后单不可发货（售后引擎统一判断）
        if (app()->make(AfterSaleServices::class)->hasActiveByOrderId((int)$id)) {
            throw new AdminException('订单有售后申请请先处理');
        }
        return $this->doDelivery($id, $orderInfo, $data);
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliveryGoods(int $id, array $data, $orderInfo, $storeTitle)
    {
        if (!$data['delivery_name']) {
            throw new AdminException('请选择快递公司');
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException('快递公司编缺失');
            }
            if (!$data['express_temp_id']) {
                throw new AdminException('请选择电子面单模板');
            }
            if (!$data['to_name']) {
                throw new AdminException('请填写寄件人姓名');
            }
            if (!$data['to_tel']) {
                throw new AdminException('请填写寄件人电话');
            }
            if (!$data['to_addr']) {
                throw new AdminException('请填写寄件人地址');
            }
            /** @var ExpressDriverServices $driverServices */
            $driverServices = app()->make(ExpressDriverServices::class);
            /** @var Express $expressService */
            $expressService = $driverServices->createExpressServiceByCode($data['delivery_code']);
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            /** @var ExpressConfigServices $expressConfigService */
            $expressConfigService = app()->make(ExpressConfigServices::class);
            // 优先取公司级云打印机编号，回退全局设置
            $expData['siid'] = $driverServices->getCompanySiid($data['delivery_code']);
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
            $expData['cargo'] = $this->buildCargoFromExtraInfo((string)$orderInfo->order_no, true);
            $expData['order_id'] = $orderInfo->order_no;
            if (!$expressConfigService->getItem('open', 0)) {
                throw new AdminException('电子面单已关闭，请选择其他发货方式');
            }
            $dump = $expressService->dump($expData);
            $orderInfo->delivery_id = $dump['kuaidinum'];
            $data['express_dump'] = json_encode([
                'com' => $expData['com'],
                'from_name' => $expData['from_name'],
                'from_tel' => $expData['from_tel'],
                'from_addr' => $expData['from_addr'],
                'temp_id' => $expData['temp_id'],
                'cargo' => $expData['cargo'],
            ]);
            $data['delivery_id'] = $dump['kuaidinum'];
        } else {
            if (!$data['delivery_id']) {
                throw new AdminException('请输入快递单号');
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
        // 发货落地：中台仅写 delivery_time；物流明细由 OrderDelivery 事件 →
        // 产品模块 OrderDeliveryListener 写入 extra_info.delivery 并 advance 扩展表为待收货
        $this->transaction(function () use ($id) {
            $res = $this->dao->update($id, ['delivery_time' => time()]);
            if (!$res) {
                throw new AdminException('发货失败');
            }
        });
        return true;
    }


    /**
     * 订单配送
     * @param int $id
     * @param array $data
     */
    public function orderDelivery(int $id, array $data, $orderInfo, string $storeTitle)
    {
        $data['delivery_type'] = 'send';
        $data['delivery_name'] = $data['sh_delivery_name'];
        $data['delivery_id'] = $data['sh_delivery_id'];
        $data['delivery_uid'] = $data['sh_delivery_uid'];
        $data['shipping_type'] = 1;
        // 核销码已归属中台表实体列 `eb_order_center.verify_code`，旧表不写入
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['sh_delivery_uid']);
        if (!$data['delivery_name']) {
            throw new AdminException('请输入送货人姓名');
        }
        if (!$data['delivery_id']) {
            throw new AdminException('请输入送货人电话号码');
        }
        if (!$data['delivery_uid']) {
            throw new AdminException('请输入送货人信息');
        }
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
            throw new AdminException('请输入正确的送货人电话号码');
        }
        // 配送落地：中台仅写 delivery_time；配送明细经 OrderDelivery 事件落 extra_info（同快递发货）
        $this->transaction(function () use ($id) {
            $this->dao->update($id, ['delivery_time' => time()]);
        });
        return true;
    }

    /**
     * 虚拟发货
     * @param int $id
     * @param array $data
     */
    public function orderVirtualDelivery(int $id, array $data)
    {
        $data['delivery_type'] = 'fictitious';
        $data['status'] = 1;
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['delivery_name'], $data['delivery_id']);
        //虚拟发货落地：中台仅写 delivery_time，状态推进经 OrderDelivery 事件
        $this->transaction(function () use ($id) {
            $this->dao->update($id, ['delivery_time' => time()]);
        });
    }

    /**
     * 获取修改配送信息表单结构
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function distributionForm(int $id)
    {
        if (!$orderInfo = $this->dao->get($id))
            throw new AdminException('订单不存在');
        $this->hydrateCenterOrderInfo($orderInfo);

        $f[] = Form::input('order_id', '订单号', $orderInfo->getData('order_id'))->disabled(1);

        switch ($orderInfo['delivery_type']) {
            case 'send':
                $f[] = Form::input('delivery_name', '送货人姓名', $orderInfo->getData('delivery_name'))->required('请输入送货人姓名');
                $f[] = Form::input('delivery_id', '送货人电话', $orderInfo->getData('delivery_id'))->required('请输入送货人电话');
                break;
            case 'express':
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                $f[] = Form::select('delivery_code', '快递公司', (string)$orderInfo->getData('delivery_code'))->setOptions($expressServices->expressSelectForm(['is_show' => 1]))->required('请选择快递公司')->filterable(true);
                $f[] = Form::input('delivery_id', '快递单号', $orderInfo->getData('delivery_id'))->required('请填写快递单号');
                break;
        }
        return create_form('配送信息', $f, $this->url('/order/distribution/' . $id), 'PUT');
    }

    /**
     * 修改配送信息
     * @param int $id 订单id
     * @return mixed
     */
    public function updateDistribution(int $id, array $data)
    {
        $order = $this->dao->get($id);
        if (!$order) {
            throw new AdminException('数据不存在');
        }
        $this->hydrateCenterOrderInfo($order);
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new AdminException('请输入送货人姓名');
                }
                if (!$data['delivery_id']) {
                    throw new AdminException('请输入送货人电话号码');
                }
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
                    throw new AdminException('请输入正确的送货人电话号码');
                }
                break;
            case 'express':
                if (!$data['delivery_id']) {
                    throw new AdminException('请输入快递单号');
                }
                // 检测快递公司编码
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                if ($name = $expressServices->value(['code' => $data['delivery_code']], 'name')) {
                    $data['delivery_name'] = $name;
                } else {
                    throw new AdminException('请核对快递公司编码');
                }
                break;
            case 'fictitious':
                throw new AdminException('虚拟发货，无需修改发货信息');
                break;
            default:
                throw new AdminException('未发货，请先发货再修改配送信息');
                break;
        }
        return $this->dao->update($id, $data);
    }

    /**订单发货后打印电子面单
     * @param $orderId
     * @return bool|mixed
     */
    public function orderDump($orderId, $type = 'order')
    {
        if (!$orderId) throw new AdminException('订单不存在');
        if ($type == 'order') {
            /** @var OrderCenterDao $orderService */
            $orderService = app()->make(OrderCenterDao::class);
            $orderInfo = $orderService->getOne(['id' => $orderId]);
            if ($orderInfo) {
                $this->hydrateCenterOrderInfo($orderInfo);
            }
        } else {
            /** @var StoreIntegralOrderServices $integralOrderService */
            $integralOrderService = app()->make(StoreIntegralOrderServices::class);
            $orderInfo = $integralOrderService->getOne(['id' => $orderId]);
        }
        if (!$orderInfo) throw new AdminException('订单不存在');
        if ($orderInfo->shipping_type != 1) throw new AdminException('自提订单无法打印');
        if (!$orderInfo->express_dump) throw new AdminException('请先发货');
        if (!app()->make(ExpressConfigServices::class)->getItem('open', 0)) {
            throw new AdminException('请先在系统设置中打开单子面单打印开关');
        }
        $dumpInfo = json_decode($orderInfo->express_dump, true);
        /** @var ExpressDriverServices $driverServices */
        $driverServices = app()->make(ExpressDriverServices::class);
        // 根据发货时记录的快递公司编码动态选择驱动
        $deliveryCode = $dumpInfo['com'] ?? '';
        /** @var Express $expressService */
        $expressService = $driverServices->createExpressServiceByCode($deliveryCode);
        $expData['com'] = $deliveryCode;
        $expData['to_name'] = $orderInfo->real_name;
        $expData['to_tel'] = $orderInfo->user_phone;
        $expData['to_addr'] = $orderInfo->user_address;
        $expData['from_name'] = $dumpInfo['from_name'];
        $expData['from_tel'] = $dumpInfo['from_tel'];
        $expData['from_addr'] = $dumpInfo['from_addr'];
        // 优先取公司级云打印机编号，回退全局设置
        $expData['siid'] = $driverServices->getCompanySiid($deliveryCode);
        $expData['temp_id'] = $dumpInfo['temp_id'];
        $expData['cargo'] = $dumpInfo['cargo'];
        $expData['count'] = $orderInfo->total_num;
        $expData['order_id'] = $orderInfo->order_no;
        $expData['weight'] = 1;

        return $expressService->dump($expData);
    }

    /**
     * 具体执行发货
     * @param int $id
     * @param $orderInfo
     * @param array $data
     * @return array
     */
    public function doDelivery(int $id, $orderInfo, array $data)
    {
        $type = (int)$data['type'];
        unset($data['type']);
        // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
        $storeName = $this->buildCargoFromExtraInfo((string)$orderInfo->order_no, false);

        if (isset($data['pickup_time']) && count($data['pickup_time']) == 2) {
            $data['pickup_start_time'] = $data['pickup_time'][0];
            $data['pickup_end_time'] = $data['pickup_time'][1];
        } else {
            $data['pickup_start_time'] = '';
            $data['pickup_end_time'] = '';
        }

        // 发货信息录入
        $res = [];
        switch ($type) {
            case 1://快递发货
                $res = $this->orderDeliverGoods($id, $data, $orderInfo, $storeName);
                break;
            case 2://配送
                $this->orderDelivery($id, $data, $orderInfo, $storeName);
                break;
            case 3://虚拟发货
                $this->orderVirtualDelivery($id, $data, $orderInfo, $storeName);
                break;
            default:
                throw new AdminException('暂时不支持其他发货类型');
        }
        if (!$data['delivery_id'] && !empty($res['kuaidinum'])) {
            $data['delivery_id'] = $res['kuaidinum'];
        }
        if (!$data['delivery_id']) {
            $data['delivery_id'] = uniqid('', true);
        }
        // 小程序订单管理
        event('OrderShipping', ['product', $orderInfo, $type, $data['delivery_id'], $data['delivery_name']]);
        // 订单发货后置事件
        event('OrderDelivery', [$orderInfo, $storeName, $data, [1 => 'postage', 2 => 'deliver', 3 => 'virtual'][$type]]);

        return $res;
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliverGoods(int $id, array $data, $orderInfo, $storeTitle)
    {
        if (!$data['delivery_name']) {
            throw new AdminException('请选择快递公司');
        }
        $dump = [];
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException('快递公司编缺失');
            }
            if (!$data['express_temp_id']) {
                throw new AdminException('请选择电子面单模板');
            }
            if (!$data['to_name']) {
                throw new AdminException('请填写寄件人姓名');
            }
            if (!$data['to_tel']) {
                throw new AdminException('请填写寄件人电话');
            }
            if (!$data['to_addr']) {
                throw new AdminException('请填写寄件人地址');
            }
            /** @var ExpressDriverServices $driverServices */
            $driverServices = app()->make(ExpressDriverServices::class);
            /** @var Express $expressService */
            $expressService = $driverServices->createExpressServiceByCode($data['delivery_code']);
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            // 优先取公司级云打印机编号，回退全局设置
            $expData['siid'] = $driverServices->getCompanySiid($data['delivery_code']);
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            $expData['weight'] = $this->getOrderSumWeight($id);
            // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
            $expData['cargo'] = $this->buildCargoFromExtraInfo((string)$orderInfo->order_no, true);
            $expData['order_id'] = $orderInfo->order_no;
            if (!app()->make(ExpressConfigServices::class)->getItem('open', 0)) {
                throw new AdminException('电子面单已关闭，请选择其他发货方式');
            }
            $dump = $expressService->dump($expData);
            $orderInfo->delivery_id = $dump['kuaidinum'];
            $data['express_dump'] = json_encode([
                'com' => $expData['com'],
                'from_name' => $expData['from_name'],
                'from_tel' => $expData['from_tel'],
                'from_addr' => $expData['from_addr'],
                'temp_id' => $expData['temp_id'],
                'cargo' => $expData['cargo'],
            ]);
            $data['delivery_id'] = $dump['kuaidinum'];
            if (!empty($dump['label'])) {
                $data['kuaidi_label'] = $dump['label'];
            }
        } else if ($data['express_record_type'] == 3) {
            //商家寄件
            if (!$data['delivery_code']) {
                throw new AdminException('快递公司编缺失');
            }
            if (!$data['express_temp_id']) {
                throw new AdminException('请选择电子面单模板');
            }
            if (!$data['to_name']) {
                throw new AdminException('请填写寄件人姓名');
            }
            if (!$data['to_tel']) {
                throw new AdminException('请填写寄件人电话');
            }
            if (!$data['to_addr']) {
                throw new AdminException('请填写寄件人地址');
            }
            /** @var ExpressDriverServices $driverServices */
            $driverServices = app()->make(ExpressDriverServices::class);
            /** @var Express $expressService */
            $expressService = $driverServices->createExpressServiceByCode($data['delivery_code']);
            $expData['kuaidicom'] = $data['delivery_code'];
            $expData['man_name'] = $orderInfo->real_name;
            $expData['phone'] = $orderInfo->user_phone;
            $expData['address'] = $orderInfo->user_address;
            $expData['send_real_name'] = $data['to_name'];
            $expData['send_phone'] = $data['to_tel'];
            $expData['send_address'] = $data['to_addr'];
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['weight'] = $this->getOrderSumWeight($id);
            // 从订单中台 extra_info.items 组装商品标题（旧 cart_info 表已废弃）
            $expData['cargo'] = $this->buildCargoFromExtraInfo((string)$orderInfo->order_no, true);
            $expData['day_type'] = $data['day_type'];
            $expData['pickup_start_time'] = $data['pickup_start_time'];
            $expData['pickup_end_time'] = $data['pickup_end_time'];
//            if (!sys_config('config_shippment_open', 0)) {
//                throw new AdminException('商家寄件未开启无法寄件');
//            }
            $dump = $expressService->shippmentCreateOrder($expData);
            Log::error('商家寄件返回数据：' . json_encode($dump));
            $orderInfo->delivery_id = $dump['kuaidinum'] ?? '';
            $data['express_dump'] = json_encode([
                'com' => $expData['kuaidicom'],
                'from_name' => $expData['send_real_name'],
                'from_tel' => $expData['send_phone'],
                'from_addr' => $expData['send_address'],
                'temp_id' => $expData['temp_id'],
                'cargo' => $expData['cargo'],
            ]);
            $data['delivery_id'] = $dump['kuaidinum'] ?? '';
            $data['kuaidi_label'] = $dump['label'] ?? '';
            $data['kuaidi_task_id'] = $dump['task_id'] ?? '';
            $data['kuaidi_order_id'] = $dump['order_id'] ?? '';
        } else {
            if (!$data['delivery_id']) {
                throw new AdminException('请输入快递单号');
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
        // 发货落地：中台仅写 delivery_time；物流明细（delivery_name/id/type/面单数据）由
        // doDelivery 尾部的 OrderDelivery 事件 → OrderDeliveryListener 写入 extra_info.delivery 并 advance 扩展表
        $this->transaction(function () use ($id) {
            $res = $this->dao->update($id, ['delivery_time' => time()]);
            if (!$res) {
                throw new AdminException('发货失败');
            }
        });
        if ($data['status'] ?? 0) {
            return true;
        }
        return $data;
    }

    protected function hydrateCenterOrderInfo($orderInfo): void
    {
        $extraInfo = $orderInfo['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $contact = $extraInfo['contact'] ?? [];
        $delivery = $extraInfo['delivery'] ?? [];

        $orderInfo['order_id'] = (string)($orderInfo['order_no'] ?? '');
        $orderInfo['real_name'] = $contact['real_name'] ?? ($contact['name'] ?? '');
        $orderInfo['user_phone'] = $contact['user_phone'] ?? ($contact['phone'] ?? '');
        // 收件人地址：兼容字符串 user_address 与下单时写入的结构化 address 对象，保证输出为字符串
        if (!empty($contact['user_address']) && is_string($contact['user_address'])) {
            $orderInfo['user_address'] = $contact['user_address'];
        } elseif (is_array($contact['address'] ?? null)) {
            $addr = $contact['address'];
            $orderInfo['user_address'] = implode('', array_filter([
                (string)($addr['province'] ?? ''),
                (string)($addr['city'] ?? ''),
                (string)($addr['district'] ?? ''),
                (string)($addr['detail'] ?? ''),
            ]));
        } else {
            $orderInfo['user_address'] = is_string($contact['address'] ?? null) ? $contact['address'] : '';
        }
        $orderInfo['delivery_name'] = $delivery['delivery_name'] ?? '';
        $orderInfo['delivery_id'] = $delivery['delivery_id'] ?? '';
        $orderInfo['delivery_type'] = $delivery['delivery_type'] ?? '';
        $orderInfo['delivery_code'] = $delivery['delivery_code'] ?? '';
        $orderInfo['express_dump'] = $delivery['express_dump'] ?? '';
    }

    protected function isCombinationReady($orderInfo): bool
    {
        if (($orderInfo['activity_type'] ?? '') !== 'combination') {
            return true;
        }

        $extraInfo = $orderInfo['extra_info'] ?? [];
        if (is_string($extraInfo)) {
            $extraInfo = json_decode($extraInfo, true) ?: [];
        }
        $pinkId = (int)($extraInfo['pink_id'] ?? 0);
        if (!$pinkId) {
            return false;
        }

        $results = event('ActivityDataProxy', [
            'mode' => 'combination',
            'sub_service' => 'pink',
            'action' => 'get',
            'args' => [$pinkId],
        ]);
        $pinkInfo = proxy_event_result($results);
        if (is_object($pinkInfo) && method_exists($pinkInfo, 'toArray')) {
            $pinkInfo = $pinkInfo->toArray();
        }

        return is_array($pinkInfo) && (int)($pinkInfo['status'] ?? 0) === 2;
    }

    /**
     * 返回订单商品总重量
     * @param int $id
     * @return int|string
     */
    public function getOrderSumWeight(int $id, $default = false)
    {
        // 从订单中台 extra_info.items 累加商品重量（旧 cart_info 表已废弃）
        /** @var OrderCenterDao $centerDao */
        $centerDao = app()->make(OrderCenterDao::class);
        $centerOrder = $centerDao->get((int)$id, ['extra_info']);
        $weight = 0;
        if ($centerOrder) {
            $extra = $centerOrder['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                $num = (string)($item['cart_num'] ?? 0);
                $itemWeight = (string)($item['attr_info']['weight'] ?? 0);
                $weight = bcadd((string)$weight, (string)bcmul($num, $itemWeight, 4), 2);
            }
        }
        return $weight ?: ($default === false ? 0 : $default);
    }

    /**
     * 从订单中台 extra_info.items 组装商品标题字符串
     * 用于电子面单 cargo 字段和发货后商品标题展示（旧 cart_info 表已废弃）
     * @param string $orderNo 订单号
     * @param bool $withNum 是否包含购买数量（true: "name * num | name * num"; false: "name|name"）
     * @return string 商品标题字符串，无商品时返回空字符串
     */
    protected function buildCargoFromExtraInfo(string $orderNo, bool $withNum = false): string
    {
        /** @var OrderCenterDao $centerDao */
        $centerDao = app()->make(OrderCenterDao::class);
        $centerOrder = $centerDao->get(['order_no' => $orderNo], ['extra_info']);
        $title = '';
        if ($centerOrder) {
            $extra = $centerOrder['extra_info'] ?? [];
            if (is_string($extra)) {
                $extra = json_decode($extra, true) ?: [];
            }
            foreach ($extra['items'] ?? [] as $item) {
                $name = $item['store_name'] ?? '';
                if ($name === '') {
                    continue;
                }
                if ($withNum) {
                    $num = $item['cart_num'] ?? 1;
                    $title .= $name . ' * ' . $num . ' | ';
                } else {
                    $title .= $name . '|';
                }
            }
            // 去掉末尾分隔符
            if ($title !== '') {
                $title = $withNum ? substr($title, 0, -3) : substr($title, 0, -1);
            }
        }
        return $title;
    }

    /**
     * 虚拟商品站内信（写入 eb_notice + notice_read）
     * @param int $uid
     * @param array $noticeInfo
     */
    public function SystemSend(int $uid, array $noticeInfo)
    {
        /** @var \app\services\message\NoticeServices $noticeService */
        $noticeService = app()->make(\app\services\message\NoticeServices::class);
        $noticeService->createNoticeForUser(
            $uid,
            $noticeInfo['title'] ?? '',
            $noticeInfo['content'] ?? '',
            $noticeInfo['mark'] ?? ''
        );
    }
}
