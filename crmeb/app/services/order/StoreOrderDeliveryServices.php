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

namespace app\services\order;

use crmeb\utils\Str;

use app\jobs\TakeOrderJob;
use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use app\jobs\RoutineTemplateJob;
use app\services\user\UserServices;
use app\services\serve\ServeServices;
use think\exception\ValidateException;
use crmeb\services\FormBuilder as Form;
use app\services\shipping\ExpressServices;
use app\services\wechat\WechatUserServices;
use app\services\message\sms\SmsSendServices;
use app\jobs\WechatTemplateJob as TemplateJob;
use think\facade\Log;

/**
 * 订单发货
 * Class StoreOrderDeliveryServices
 * @package app\services\order
 */
class StoreOrderDeliveryServices extends BaseServices
{
    /**
     * 构造方法
     * StoreOrderDeliveryServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 订单发货
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function delivery(int $id, array $data)
    {
        $orderInfo = $this->dao->get($id);
        if (!$orderInfo) {
            throw new ValidateException('订单未能查到,不能发货!');
        }
        if ($orderInfo->is_del) {
            throw new ValidateException('订单已删除,不能发货!');
        }
        if ($orderInfo->status) {
            throw new ValidateException('订单已发货请勿重复操作!');
        }
        if ($orderInfo->shipping_type == 2) {
            throw new ValidateException('核销订单不能发货!');
        }
        $type = (int)$data['type'];
        unset($data['type']);

        //获取购物车内的商品标题
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $storeName = $orderInfoServices->getCarIdByProductTitle($orderInfo->cart_id);

        switch ($type) {
            case 1:
                //发货
                $this->orderDeliveryGoods($id, $data, $orderInfo, $storeName);
                break;
            case 2:
                $this->orderDelivery($id, $data, $orderInfo, $storeName);
                break;
            case 3:
                $this->orderVirtualDelivery($id, $data, $orderInfo, $storeName);
                break;
            default:
                throw new ValidateException('暂时不支持其他发货类型');
                break;
        }

        //发货后置事件
        event('order.orderDelivery', [$orderInfo, $storeName, $data, $type]);
        return true;
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliveryGoods(int $id, array $data, $orderInfo, $storeTitle)
    {
        if (!$data['delivery_name']) {
            throw new ValidateException('请选择快递公司');
        }
        $data['delivery_type'] = 'express';
        if (!$data['delivery_id']) {
            throw new ValidateException('请输入快递单号');
        }
        $orderInfo->delivery_id = $data['delivery_id'];
        $data['status'] = 1;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->status = $data['status'];
        /** @var StoreOrderStatusServices $services */
        $services = app()->make(StoreOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $res = $this->dao->update($id, $data);
            $res = $res && $services->save([
                    'oid' => $id,
                    'change_time' => time(),
                    'change_type' => 'delivery_goods',
                    'change_message' => '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']
                ]);
            if (!$res) {
                throw new ValidateException('发货失败：数据保存不成功');
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
        //获取核销码
        /** @var StoreOrderCreateServices $storeOrderCreateService */
        $storeOrderCreateService = app()->make(StoreOrderCreateServices::class);
        $data['verify_code'] = $storeOrderCreateService->getStoreCode();
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['sh_delivery_uid']);
        if (!$data['delivery_name']) {
            throw new ValidateException('请输入送货人姓名');
        }
        if (!$data['delivery_id']) {
            throw new ValidateException('请输入送货人电话号码');
        }
        if (!$data['delivery_uid']) {
            throw new ValidateException('请输入送货人信息');
        }
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
            throw new ValidateException('请输入正确的送货人电话号码');
        }
        $data['status'] = 1;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->delivery_id = $data['delivery_id'];
        $orderInfo->status = $data['status'];
        /** @var StoreOrderStatusServices $services */
        $services = app()->make(StoreOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $this->dao->update($id, $data);
            //记录订单状态
            $services->save([
                'oid' => $id,
                'change_type' => 'delivery',
                'change_time' => time(),
                'change_message' => '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']
            ]);
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
        //保存信息
        /** @var StoreOrderStatusServices $services */
        $services = app()->make(StoreOrderStatusServices::class);
        $this->transaction(function () use ($id, $data, $services) {
            $this->dao->update($id, $data);
            $services->save([
                'oid' => $id,
                'change_type' => 'delivery_fictitious',
                'change_message' => '已虚拟发货',
                'change_time' => time()
            ]);
        });

        //模板消息

    }

    /**
     * 发货发送短信
     * @param $orderInfo
     */
    public function deliverySmsSendAfter($orderInfo, string $store_name)
    {
        $order_id = $orderInfo->order_id;
        $switch = sys_config('deliver_goods_switch') ? true : false;
        $service = app()->make(UserServices::class);
        $nickname = $service->value(['uid' => $orderInfo->uid], 'nickname');

        /** @var SmsSendServices $smsServices */
        $smsServices = app()->make(SmsSendServices::class);
        $smsServices->send($switch, $orderInfo->user_phone, compact('order_id', 'store_name', 'nickname'), 'DELIVER_GOODS_CODE', '用户发货发送短信失败，订单号为：' . $order_id);
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
            throw new ValidateException('订单不存在');

        $f[] = Form::input('order_id', '订单号', $orderInfo->getData('order_id'))->disabled(1);

        switch ($orderInfo['delivery_type']) {
            case 'send':
                $f[] = Form::input('delivery_name', '送货人姓名', $orderInfo->getData('delivery_name'))->required('请输入送货人姓名');
                $f[] = Form::input('delivery_id', '送货人电话', $orderInfo->getData('delivery_id'))->required('请输入送货人电话');
                break;
            case 'express':
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                $f[] = Form::select('delivery_name', '快递公司', (string)$orderInfo->getData('delivery_name'))->setOptions(array_map(function ($item) {
                    $item['value'] = $item['label'];
                    return $item;
                }, $expressServices->expressSelectForm(['is_show' => 1])))->required('请选择快递公司');
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
            throw new ValidateException('数据不存在！');
        }
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new ValidateException('请输入送货人姓名');
                }
                if (!$data['delivery_id']) {
                    throw new ValidateException('请输入送货人电话号码');
                }
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
                    throw new ValidateException('请输入正确的送货人电话号码');
                }
                break;
            case 'express':
                if (!$data['delivery_name']) {
                    throw new ValidateException('请选择快递公司');
                }
                if (!$data['delivery_id']) {
                    throw new ValidateException('请输入快递单号');
                }
                break;
            default:
                throw new ValidateException('未发货，请先发货再修改配送信息');
                break;
        }
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $statusService->save([
            'oid' => $id,
            'change_type' => 'distribution',
            'change_message' => '修改发货信息为' . $data['delivery_name'] . '号' . $data['delivery_id'],
            'change_time' => time()
        ]);
        return $this->dao->update($id, $data);
    }

    /**订单发货后打印电子面单
     * @param $orderId
     * @return bool|mixed
     */
    public function orderDump($orderId)
    {
        if (!$orderId) throw new ValidateException('订单号缺失');
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        $orderInfo = $orderService->getOne(['id' => $orderId]);
        if (!$orderInfo) throw new ValidateException('订单不存在');
        if ($orderInfo->shipping_type != 1) throw new ValidateException('自提订单无法打印');
        if (!$orderInfo->express_dump) throw new ValidateException('请先发货');
        if (!sys_config('config_export_open', 0)) {
            throw new ValidateException('请先在系统设置中打开单子面单打印开关');
        }
        $dumpInfo = json_decode($orderInfo->express_dump, true);
        /** @var ServeServices $expressService */
        $expressService = app()->make(ServeServices::class);
        $expData['com'] = $dumpInfo['com'];
        $expData['to_name'] = $orderInfo->real_name;
        $expData['to_tel'] = $orderInfo->user_phone;
        $expData['to_addr'] = $orderInfo->user_address;
        $expData['from_name'] = $dumpInfo['from_name'];
        $expData['from_tel'] = $dumpInfo['from_tel'];
        $expData['from_addr'] = $dumpInfo['from_addr'];
        $expData['siid'] = sys_config('config_export_siid');
        $expData['temp_id'] = $dumpInfo['temp_id'];
        $expData['cargo'] = $dumpInfo['cargo'];
        $expData['count'] = $orderInfo->total_num;
        $expData['order_id'] = $orderInfo->order_id;

        return $expressService->express()->dump($expData);
    }


}
