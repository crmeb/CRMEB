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

namespace app\services\order;

use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\BaseServices;
use app\dao\order\StoreOrderDao;
use app\services\message\MessageSystemServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\product\sku\StoreProductVirtualServices;
use app\services\serve\ServeServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use app\services\shipping\ExpressServices;
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
        $orderInfo = $this->dao->get($id, ['*'], ['pink']);
        if (!$orderInfo) {
            throw new AdminException(400470);
        }
        if ($orderInfo->is_del) {
            throw new AdminException(400471);
        }
        if ($orderInfo->status) {
            throw new AdminException(400472);
        }
        if ($orderInfo->shipping_type == 2) {
            throw new AdminException(400473);
        }
        if (isset($orderInfo['pinkStatus']) && $orderInfo['pinkStatus'] != 2) {
            throw new AdminException(400474);
        }

        if ($data['type'] == 1) {
            // 检测快递公司编码
            /** @var ExpressServices $expressServices */
            $expressServices = app()->make(ExpressServices::class);
           if (!$expressServices->be(['code' => $data['delivery_code']])) {
               throw new AdminException(410324);
           }
        }

        /** @var StoreOrderRefundServices $storeOrderRefundServices */
        $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
        if ($storeOrderRefundServices->count(['store_order_id' => $id, 'refund_type' => [1, 2, 4, 5], 'is_cancel' => 0, 'is_del' => 0])) {
            throw new AdminException(400475);
        }
        $this->doDelivery($id, $orderInfo, $data);
        return true;
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliveryGoods(int $id, array $data, $orderInfo, $storeTitle)
    {
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        if (!$data['delivery_name']) {
            throw new AdminException(400007);
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException(400476);
            }
            if (!$data['express_temp_id']) {
                throw new AdminException(400527);
            }
            if (!$data['to_name']) {
                throw new AdminException(400008);
            }
            if (!$data['to_tel']) {
                throw new AdminException(400477);
            }
            if (!$data['to_addr']) {
                throw new AdminException(400478);
            }
            /** @var ServeServices $expressService */
            $expressService = app()->make(ServeServices::class);
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            $expData['siid'] = sys_config('config_export_siid');
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            $expData['cargo'] = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo->id, true);
            $expData['order_id'] = $orderInfo->order_id;
            if (!sys_config('config_export_open', 0)) {
                throw new AdminException(400528);
            }
            $dump = $expressService->express()->dump($expData);
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
                throw new AdminException(400531);
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
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
                throw new AdminException(400529);
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
            throw new AdminException(400523);
        }
        if (!$data['delivery_id']) {
            throw new AdminException(400524);
        }
        if (!$data['delivery_uid']) {
            throw new AdminException(400525);
        }
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
            throw new AdminException(400526);
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
            throw new AdminException(400118);

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
            throw new AdminException(100026);
        }
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new AdminException(400523);
                }
                if (!$data['delivery_id']) {
                    throw new AdminException(400524);
                }
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) {
                    throw new AdminException(400526);
                }
                break;
            case 'express':
                if (!$data['delivery_id']) {
                    throw new AdminException(400531);
                }
                // 检测快递公司编码
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                if ($name = $expressServices->value(['code' => $data['delivery_code']], 'name')) {
                    $data['delivery_name'] = $name;
                } else {
                    throw new AdminException(410324);
                }
                break;
            case 'fictitious':
                throw new AdminException(400479);
                break;
            default:
                throw new AdminException(400480);
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
        if (!$orderId) throw new AdminException(10100);
        /** @var StoreOrderServices $orderService */
        $orderService = app()->make(StoreOrderServices::class);
        $orderInfo = $orderService->getOne(['id' => $orderId]);
        if (!$orderInfo) throw new AdminException(400118);
        if ($orderInfo->shipping_type != 1) throw new AdminException(400481);
        if (!$orderInfo->express_dump) throw new AdminException(400482);
        if (!sys_config('config_export_open', 0)) {
            throw new AdminException(400483);
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

    /**
     * 订单拆单发货
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function splitDelivery(int $id, array $data)
    {
        $orderInfo = $this->dao->get($id, ['*'], ['pink']);
        if (!$orderInfo) {
            throw new AdminException(400470);
        }
        if ($orderInfo->is_del) {
            throw new AdminException(400471);
        }
        if ($orderInfo->shipping_type == 2) {
            throw new AdminException(400473);
        }
        if (isset($orderInfo['pinkStatus']) && $orderInfo['pinkStatus'] != 2) {
            throw new AdminException(400474);
        }
        /** @var StoreOrderRefundServices $storeOrderRefundServices */
        $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
        if ($storeOrderRefundServices->count(['store_order_id' => $id, 'refund_type' => [1, 2, 4, 5], 'is_cancel' => 0, 'is_del' => 0])) {
            throw new AdminException(400475);
        }

        if ($data['type'] == 1) {
            // 检测快递公司编码
            /** @var ExpressServices $expressServices */
            $expressServices = app()->make(ExpressServices::class);
            if (!$expressServices->be(['code' => $data['delivery_code']])) {
                throw new AdminException(410324);
            }
        }

        $cart_ids = $data['cart_ids'];
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        unset($data['cart_ids']);
        $this->transaction(function () use ($id, $cart_ids, $orderInfo, $data, $storeOrderCartInfoServices) {
            /** @var StoreOrderSplitServices $storeOrderSplitServices */
            $storeOrderSplitServices = app()->make(StoreOrderSplitServices::class);
            //订单拆单
            [$splitOrderInfo, $otherOrder] = $storeOrderSplitServices->equalSplit($id, $cart_ids, $orderInfo);
            if ($splitOrderInfo) {
                $splitOrderInfo['refund_status'] = 0;
                //拆分订单执行发货
                $this->doDelivery((int)$splitOrderInfo->id, $splitOrderInfo, $data);
                /** @var StoreOrderStatusServices $services */
                $services = app()->make(StoreOrderStatusServices::class);
                //记录原订单状态
                $status_data = ['oid' => $id, 'change_time' => time()];
                $status_data['change_type'] = 'delivery_split';
                $status_data['change_message'] = '已拆分发货';
                $services->save($status_data);
            } else {
                $this->doDelivery($id, $orderInfo, $data);
            }
        });
        return true;
    }

    /**
     * 具体执行发货
     * @param int $id
     * @param $orderInfo
     * @param array $data
     * @return bool
     */
    public function doDelivery(int $id, $orderInfo, array $data)
    {
        $type = (int)$data['type'];
        unset($data['type']);
        //获取购物车内的商品标题
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $storeName = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo->id);
        switch ($type) {
            case 1://快递发货
                $this->orderDeliverGoods($id, $data, $orderInfo, $storeName);
                event('notice.notice', [['orderInfo' => $orderInfo, 'storeName' => $storeName, 'data' => $data], 'order_postage_success']);
                break;
            case 2://配送
                $this->orderDelivery($id, $data, $orderInfo, $storeName);
                event('notice.notice', [['orderInfo' => $orderInfo, 'storeName' => $storeName, 'data' => $data], 'order_deliver_success']);
                break;
            case 3://虚拟发货
                $this->orderVirtualDelivery($id, $data, $orderInfo, $storeName);
                break;
            default:
                throw new AdminException(400522);
        }
        //到期自动收货
        event('order.orderDelivery', [$orderInfo, $storeName, $data, $type]);
        return true;
    }

    /**
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliverGoods(int $id, array $data, $orderInfo, $storeTitle)
    {
        /** @var StoreOrderCartInfoServices $orderInfoServices */
        $orderInfoServices = app()->make(StoreOrderCartInfoServices::class);
        if (!$data['delivery_name']) {
            throw new AdminException(400007);
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException(400476);
            }
            if (!$data['express_temp_id']) {
                throw new AdminException(400527);
            }
            if (!$data['to_name']) {
                throw new AdminException(400008);
            }
            if (!$data['to_tel']) {
                throw new AdminException(400477);
            }
            if (!$data['to_addr']) {
                throw new AdminException(400478);
            }
            /** @var ServeServices $expressService */
            $expressService = app()->make(ServeServices::class);
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            $expData['siid'] = sys_config('config_export_siid');
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            $expData['weight'] = $this->getOrderSumWeight($id);
            $expData['cargo'] = $orderInfoServices->getCarIdByProductTitle((int)$orderInfo->id, true);
            $expData['order_id'] = $orderInfo->order_id;
            if (!sys_config('config_export_open', 0)) {
                throw new AdminException(400528);
            }
            $dump = $expressService->express()->dump($expData);
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
                throw new AdminException(400531);
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
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
                throw new AdminException(400529);
            }
        });
        return true;
    }

    /**
     * 返回订单商品总重量
     * @param int $id
     * @return int|string
     */
    public function getOrderSumWeight(int $id, $default = false)
    {
        /** @var StoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderCartInfoServices::class);
        $orderGoodInfo = $services->getOrderCartInfo((int)$id);
        $weight = 0;
        foreach ($orderGoodInfo as $cartInfo) {
            $cart = $cartInfo['cart_info'] ?? [];
            if ($cart) {
                $weight = bcadd((string)$weight, (string)bcmul((string)$cart['cart_num'] ?? '0', (string)$cart['productInfo']['attrInfo']['weight'] ?? '0', 4), 2);
            }
        }
        return $weight ?: ($default === false ? 0 : $default);
    }

    /**
     * 虚拟商品自动发货
     * @param $orderInfo
     */
    public function virtualSend($orderInfo)
    {
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        /** @var StoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderCartInfoServices::class);
        $orderInfo['cart_info'] = $services->getOrderCartInfo((int)$orderInfo['id']);
        $activityStatus = $orderInfo['combination_id'] || $orderInfo['seckill_id'] || $orderInfo['bargain_id'];
        if ($orderInfo['virtual_type'] == 1) {
            /** @var StoreOrderServices $orderService */
            $orderService = app()->make(StoreOrderServices::class);
            $sku = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['suk'];
            if ($activityStatus) {
                $product_id = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['product_id'];
                /** @var StoreProductAttrValueServices $attrValue */
                $attrValue = app()->make(StoreProductAttrValueServices::class);
                $disk_info = $attrValue->value(['product_id' => $product_id, 'suk' => $sku, 'type' => 0, 'is_virtual' => 1], 'disk_info');
            } else {
                $disk_info = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['disk_info'];
            }
            if ($disk_info != '') {
                $orderService->update(['id' => $orderInfo['id']], ['status' => 1, 'delivery_type' => 'fictitious', 'virtual_info' => $disk_info, 'remark' => '密钥自动发放：' . $disk_info]);
                $this->SystemSend($orderInfo['uid'], [
                    'mark' => 'virtual_info',
                    'title' => '虚拟密钥发放',
                    'content' => '您购买的密钥商品已支付成功，支付金额' . $orderInfo['pay_price'] . '元，订单号：' . $orderInfo['order_id'] . '，密钥：' . $disk_info . '，感谢您的光临！'
                ]);
            } else {
                if ($activityStatus) {
                    $product_id = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['product_id'];
                    /** @var StoreProductAttrValueServices $attrValue */
                    $attrValue = app()->make(StoreProductAttrValueServices::class);
                    $unique = $attrValue->value(['product_id' => $product_id, 'suk' => $sku, 'type' => 0, 'is_virtual' => 1], 'unique');
                } else {
                    $unique = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['unique'];
                }
                /** @var StoreProductVirtualServices $virtualService */
                $virtualService = app()->make(StoreProductVirtualServices::class);
                $virtual = $virtualService->get(['attr_unique' => $unique, 'uid' => 0]);
                $virtual->order_id = $orderInfo['order_id'];
                $virtual->uid = $orderInfo['uid'];
                $virtual->save();
                $orderService->update(['id' => $orderInfo['id']], ['status' => 1, 'delivery_type' => 'fictitious', 'virtual_info' => $virtual->card_unique, 'remark' => '卡密已自动发放，卡号：' . $virtual->card_no . '；密码：' . $virtual->card_pwd]);
                $this->SystemSend($orderInfo['uid'], [
                    'mark' => 'virtual_info',
                    'title' => '虚拟卡密发放',
                    'content' => '您购买的卡密商品已支付成功，支付金额' . $orderInfo['pay_price'] . '元，订单号：' . $orderInfo['order_id'] . '，卡号：' . $virtual->card_no . '；密码：' . $virtual->card_pwd . '，感谢您的光临！'
                ]);
            }
            $statusService->save([
                'oid' => $orderInfo['id'],
                'change_type' => 'delivery_fictitious',
                'change_message' => '卡密自动发货',
                'change_time' => time()
            ]);
        } elseif ($orderInfo['virtual_type'] == 2) {
            if ($activityStatus) {
                $sku = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['suk'];
                $product_id = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['product_id'];
                /** @var StoreProductAttrValueServices $attrValue */
                $attrValue = app()->make(StoreProductAttrValueServices::class);
                $coupon_id = $attrValue->value(['product_id' => $product_id, 'suk' => $sku, 'type' => 0, 'is_virtual' => 1], 'coupon_id');
            } else {
                $coupon_id = $orderInfo['cart_info'][$orderInfo['cart_id'][0]]['cart_info']['productInfo']['attrInfo']['coupon_id'];
            }
            /** @var StoreCouponIssueServices $issueService */
            $issueService = app()->make(StoreCouponIssueServices::class);
            $coupon = $issueService->get($coupon_id);
            if ($issueService->setCoupon($coupon, [$orderInfo['uid']])) {
                /** @var StoreOrderServices $orderService */
                $orderService = app()->make(StoreOrderServices::class);
                $orderService->update(['id' => $orderInfo['id']], ['status' => 1, 'delivery_type' => 'fictitious', 'virtual_info' => $coupon_id, 'remark' => '优惠券已自动发放']);
                $this->SystemSend($orderInfo['uid'], [
                    'mark' => 'virtual_info',
                    'title' => '购买优惠券发放',
                    'content' => '您购买的优惠券已支付成功，支付金额' . $orderInfo['pay_price'] . '元，订单号' . $orderInfo['order_id'] . '请在个人中心优惠券中查看,感谢您的光临！'
                ]);
            } else {
                throw new ApiException(410323);
            }
            $statusService->save([
                'oid' => $orderInfo['id'],
                'change_type' => 'delivery_fictitious',
                'change_message' => '优惠券自动发货',
                'change_time' => time()
            ]);
        }
    }

    /**
     * 虚拟商品站内信
     * @param int $uid
     * @param array $noticeInfo
     */
    public function SystemSend(int $uid, array $noticeInfo)
    {
        /** @var MessageSystemServices $MessageSystemServices */
        $MessageSystemServices = app()->make(MessageSystemServices::class);
        $data = [];
        $data['mark'] = $noticeInfo['mark'];
        $data['uid'] = $uid;
        $data['title'] = $noticeInfo['title'];
        $data['content'] = $noticeInfo['content'];
        $data['type'] = 1;
        $data['add_time'] = time();
        $MessageSystemServices->save($data);
    }
}
