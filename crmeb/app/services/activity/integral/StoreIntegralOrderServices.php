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

namespace app\services\activity\integral;

use app\dao\activity\integral\StoreIntegralOrderDao;
use app\services\BaseServices;
use app\services\product\sku\StoreProductAttrValueServices;
use app\services\serve\ServeServices;
use app\services\shipping\ExpressServices;
use app\services\user\UserServices;
use app\services\user\UserAddressServices;
use app\services\user\UserBillServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\services\FormBuilder as Form;
use crmeb\services\printer\Printer;

/**
 * Class StoreIntegralOrderServices
 * @package app\services\order
 * @method getOrderIdsCount(array $ids) 获取订单id下没有删除的订单数量
 * @method getUserOrderDetail(string $key, int $uid) 获取订单详情
 * @method getBuyCount($uid, $type) 获取用户已购买此活动商品的个数
 */
class StoreIntegralOrderServices extends BaseServices
{

    /**
     * 发货类型
     * @var string[]
     */
    public $deliveryType = ['send' => '商家配送', 'express' => '快递配送', 'fictitious' => '虚拟发货'];

    /**
     * StoreIntegralOrderServices constructor.
     * @param StoreIntegralOrderDao $dao
     */
    public function __construct(StoreIntegralOrderDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderList(array $where, array $field = ['*'], array $with = [])
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getOrderList($where, $field, $page, $limit, $with);
        $count = $this->dao->count($where);
        $data = $this->tidyOrderList($data);
        $batch_url = "file/upload/1";
        return compact('data', 'count', 'batch_url');
    }

    /**
     * 获取导出数据
     * @param array $where
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportList(array $where, int $limit = 0)
    {
        if ($limit) {
            [$page] = $this->getPageValue();
        } else {
            [$page, $limit] = $this->getPageValue();
        }
        $data = $this->dao->getOrderList($where, ['*'], $page, $limit);
        $data = $this->tidyOrderList($data);
        return $data;
    }

    /**
     * 前端订单列表
     * @param array $where
     * @param array|string[] $field
     * @param array $with
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOrderApiList(array $where, array $field = ['*'], array $with = [])
    {
        [$page, $limit] = $this->getPageValue();
        $data = $this->dao->getOrderList($where, $field, $page, $limit, $with);
        return $this->tidyOrderList($data);
    }

    /**
     * 订单详情数据格式化
     * @param $order
     * @return mixed
     */
    public function tidyOrder($order)
    {
        $order['add_time'] = date('Y-m-d H:i:s', $order['add_time']);
        if ($order['status'] == 1) {
            $order['status_name'] = '未发货';
        } else if ($order['status'] == 2) {
            $order['status_name'] = '待收货';
        } else if ($order['status'] == 3) {
            $order['status_name'] = '已完成';
        }
        return $order;
    }

    /**
     * 数据转换
     * @param array $data
     * @return array
     */
    public function tidyOrderList(array $data)
    {
        foreach ($data as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            if ($item['status'] == 1) {
                $item['status_name'] = '未发货';
            } else if ($item['status'] == 2) {
                $item['status_name'] = '待收货';
            } else if ($item['status'] == 3) {
                $item['status_name'] = '已完成';
            }
        }
        return $data;
    }

    /**
     * 创建订单
     * @param $uid
     * @param $addressId
     * @param string $mark
     * @param $user
     * @param $num
     * @param $productInfo
     * @throws \Exception
     */
    public function createOrder($uid, $addressId, $mark = '', $userInfo, $num, $productInfo)
    {
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        if (!$addressId) {
            throw new ApiException(410045);
        }
        if (!$addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0]))
        throw new ApiException(410046);
        $addressInfo = $addressInfo->toArray();
        $total_price = bcmul($productInfo['price'], $num, 2);
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $usable_integral = bcsub((string)$userInfo['integral'], (string)$userBillServices->getBillSum(['uid' => $userInfo['uid'], 'is_frozen' => 1]), 0);
        if ($total_price > $usable_integral) throw new ApiException(410047);
        $orderInfo = [
            'uid' => $uid,
            'order_id' => $this->getNewOrderId(),
            'real_name' => $addressInfo['real_name'],
            'user_phone' => $addressInfo['phone'],
            'user_address' => $addressInfo['province'] . ' ' . $addressInfo['city'] . ' ' . $addressInfo['district'] . ' ' . $addressInfo['detail'],
            'product_id' => $productInfo['product_id'],
            'image' => $productInfo['image'],
            'store_name' => $productInfo['store_name'],
            'suk' => $productInfo['suk'],
            'total_num' => $num,
            'price' => $productInfo['price'],
            'total_price' => $total_price,
            'add_time' => time(),
            'status' => 1,
            'mark' => $mark,
            'channel_type' => $userInfo['user_type']
        ];
        $order = $this->transaction(function () use ($orderInfo, $userInfo, $productInfo, $uid, $num, $total_price) {
            //创建订单
            $order = $this->dao->save($orderInfo);
            if (!$order) {
                throw new ApiException(410200);
            }
            //扣库存
            $this->decGoodsStock($productInfo, $num);
            //减积分
            $this->deductIntegral($userInfo, $total_price, (int)$userInfo['uid'], $order->id);
            return $order;
        });
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'cache_key_create_order',
            'change_message' => '订单生成',
            'change_time' => time()
        ]);
        return $order;
    }

    /**
     * 抵扣积分
     * @param array $userInfo
     * @param bool $useIntegral
     * @param array $priceData
     * @param int $uid
     * @param string $key
     */
    public function deductIntegral(array $userInfo, $priceIntegral, int $uid, string $orderId)
    {
        $res2 = true;
        if ($userInfo['integral'] > 0) {
            /** @var UserServices $userServices */
            $userServices = app()->make(UserServices::class);
            $res2 = false !== $userServices->bcDec($userInfo['uid'], 'integral', $priceIntegral, 'uid');
            /** @var UserBillServices $userBillServices */
            $userBillServices = app()->make(UserBillServices::class);
            $res3 = $userBillServices->income('storeIntegral_use_integral', $uid, $priceIntegral, $userInfo['integral'], $orderId);
            $res2 = $res2 && false != $res3;
        }
        if (!$res2) {
            throw new ApiException(410227);
        }
    }

    /**
     * 扣库存
     * @param array $cartInfo
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     */
    public function decGoodsStock(array $productInfo, int $num)
    {
        $res5 = true;
        /** @var StoreIntegralServices $StoreIntegralServices */
        $StoreIntegralServices = app()->make(StoreIntegralServices::class);
        try {
            $res5 = $res5 && $StoreIntegralServices->decIntegralStock((int)$num, $productInfo['product_id'], $productInfo['unique']);
            if (!$res5) {
                throw new ApiException(410296);
            }
        } catch (\Throwable $e) {
            throw new ApiException(410296);
        }
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getNewOrderId(string $prefix = 'wx')
    {
        $snowflake = new \Godruoyi\Snowflake\Snowflake();
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->id();
        }
        return $prefix . $id;
    }

    /**
     *获取订单数量
     * @param array $where
     * @return mixed
     */
    public function orderCount(array $where)
    {
        //全部订单
        $data['statusAll'] = (string)$this->dao->count($where + ['is_system_del' => 0]);
        //未发货
        $data['unshipped'] = (string)$this->dao->count($where + ['status' => 1, 'is_system_del' => 0]);
        //待收货
        $data['untake'] = (string)$this->dao->count($where + ['status' => 2, 'is_system_del' => 0]);
        //待评价
//        $data['unevaluate'] = (string)$this->dao->count(['status' => 3, 'time' => $where['time'], 'is_system_del' => 0]);
        //交易完成
        $data['complete'] = (string)$this->dao->count($where + ['status' => 3, 'is_system_del' => 0]);
        return $data;
    }


    /**
     * 打印订单
     * @param $order
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPrint($order)
    {
        $data = [
            'clientId' => sys_config('printing_client_id', ''),
            'apiKey' => sys_config('printing_api_key', ''),
            'partner' => sys_config('develop_id', ''),
            'terminal' => sys_config('terminal_number', '')
        ];
        if (!$data['clientId'] || !$data['apiKey'] || !$data['partner'] || !$data['terminal']) {
            throw new AdminException(400099);
        }
        $printer = new Printer('yi_lian_yun', $data);
        $res = $printer->setIntegralPrinterContent([
            'name' => sys_config('site_name'),
            'orderInfo' => is_object($order) ? $order->toArray() : $order,
        ])->startPrinter();
        if (!$res) {
            throw new AdminException($printer->getError());
        }
        return $res;
    }

    /**
     * 获取订单确认数据
     * @param array $user
     * @param $cartId
     * @return mixed
     */
    public function getOrderConfirmData(array $user, $unique, $num)
    {
        /** @var StoreProductAttrValueServices $StoreProductAttrValueServices */
        $StoreProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $attrValue = $StoreProductAttrValueServices->uniqueByField($unique, 'product_id,suk,price,image,unique');
        if(!$attrValue || !isset($attrValue['storeIntegral']) || !$attrValue['storeIntegral']){
            throw new ApiException(410295);
        }
        $data = [];
        $attrValue = is_object($attrValue) ? $attrValue->toArray() : $attrValue;
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $data['integral'] = bcsub((string)$user['integral'], (string)$userBillServices->getBillSum(['uid' => $user['uid'], 'is_frozen' => 1]), 0);
        $data['num'] = $num;
        $data['total_price'] = bcmul($num, $attrValue['price'], 2);
        $data['productInfo'] = $attrValue;
        return $data;
    }

    /**
     * 删除订单
     * @param $uni
     * @param $uid
     * @return bool
     */
    public function removeOrder(string $order_id, int $uid)
    {
        $order = $this->getUserOrderDetail($order_id, $uid);
        if ($order['status'] != 3)
            throw new ApiException(100008);

        $order->is_del = 1;
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $res = $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'remove_order',
            'change_message' => '删除订单',
            'change_time' => time()
        ]);
        if ($order->save() && $res) {
            return true;
        } else
            throw new ApiException(100008);
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
            throw new AdminException(400118);
        }
        if ($orderInfo->is_del) {
            throw new AdminException(400520);
        }
        if ($orderInfo->status != 1) {
            throw new AdminException(400521);
        }
        $type = (int)$data['type'];
        unset($data['type']);
        if ($type == 1) {
            // 检测快递公司编码
            /** @var ExpressServices $expressServices */
            $expressServices = app()->make(ExpressServices::class);
            if (!$expressServices->be(['code' => $data['delivery_code']])) {
                throw new AdminException(410324);
            }
        }
        switch ($type) {
            case 1:
                //发货
                $this->orderDeliverGoods($id, $data, $orderInfo);
                break;
            case 2:
                $this->orderDelivery($id, $data, $orderInfo);
                break;
            case 3:
                $this->orderVirtualDelivery($id, $data, $orderInfo);
                break;
            default:
                throw new AdminException(400522);
        }
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
        $data['status'] = 2;
        unset($data['sh_delivery_name'], $data['sh_delivery_id'], $data['delivery_name'], $data['delivery_id']);
        //保存信息
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
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
     * 订单配送
     * @param int $id
     * @param array $data
     */
    public function orderDelivery(int $id, array $data, $orderInfo)
    {
        $data['delivery_type'] = 'send';
        $data['delivery_name'] = $data['sh_delivery_name'];
        $data['delivery_id'] = $data['sh_delivery_id'];
        $data['delivery_uid'] = $data['sh_delivery_uid'];
//        获取核销码
        $data['verify_code'] = $this->getStoreCode();
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
        if (!check_phone($data['delivery_id'])) {
            throw new AdminException(400526);
        }
        $data['status'] = 2;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->delivery_id = $data['delivery_id'];
        $orderInfo->status = $data['status'];
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
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
     * 订单快递发货
     * @param int $id
     * @param array $data
     */
    public function orderDeliverGoods(int $id, array $data, $orderInfo)
    {
        if (!$data['delivery_name']) {
            throw new AdminException(400007);
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException(400123);
            }
            if (!$data['express_temp_id']) {
                throw new AdminException(400527);
            }
            if (!$data['to_name']) {
                throw new AdminException(400008);
            }
            if (!$data['to_tel']) {
                throw new AdminException(400009);
            }
            if (!$data['to_addr']) {
                throw new AdminException(400011);
            }
            /** @var ServeServices $ServeServices */
            $ServeServices = app()->make(ServeServices::class);
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
            $expData['cargo'] = $orderInfo->store_name . '(' . $orderInfo->suk . ')*' . $orderInfo->total_num;
            $expData['order_id'] = $orderInfo->order_id;
            if (!sys_config('config_export_open', 0)) {
                throw new AdminException(400528);
            }
            $dump = $ServeServices->express()->dump($expData);
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
                throw new AdminException(400120);
            }
            $orderInfo->delivery_id = $data['delivery_id'];
        }
        $data['status'] = 2;
        $orderInfo->delivery_type = $data['delivery_type'];
        $orderInfo->delivery_name = $data['delivery_name'];
        $orderInfo->status = $data['status'];
        /** @var StoreIntegralOrderStatusServices $services */
        $services = app()->make(StoreIntegralOrderStatusServices::class);
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
     * 核销订单生成核销码
     * @return false|string
     */
    public function getStoreCode()
    {
        mt_srand();
        list($msec, $sec) = explode(' ', microtime());
        $num = time() + mt_rand(10, 999999) . '' . substr($msec, 2, 3);//生成随机数
        if (strlen($num) < 12)
            $num = str_pad((string)$num, 12, 0, STR_PAD_RIGHT);
        else
            $num = substr($num, 0, 12);
        if ($this->dao->count(['verify_code' => $num])) {
            return $this->getStoreCode();
        }
        return $num;
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
                $f[] = Form::select('delivery_code', '快递公司', (string)$orderInfo->getData('delivery_code'))->setOptions($expressServices->expressSelectForm(['is_show' => 1]))->required('请选择快递公司');
                $f[] = Form::input('delivery_id', '快递单号', $orderInfo->getData('delivery_id'))->required('请填写快递单号');
                break;
        }
        return create_form('配送信息', $f, $this->url('/marketing/integral/order/distribution/' . $id), 'PUT');
    }

    /**
     * 用户订单收货
     * @param $uni
     * @param $uid
     * @return bool
     */
    public function takeOrder(string $order_id, int $uid)
    {
        $order = $this->dao->getUserOrderDetail($order_id, $uid);
        if (!$order) {
            throw new ApiException(400118);
        }
        if ($order['status'] != 2) {
            throw new ApiException(400530);
        }
        $order->status = 3;
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $res = $order->save() && $statusService->save([
                'oid' => $order['id'],
                'change_type' => 'user_take_delivery',
                'change_message' => '用户已收货',
                'change_time' => time()
            ]);
        if (!$res) {
            throw new ApiException(400116);
        }
        return $order;
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
            throw new AdminException(400118);
        }
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new AdminException(400523);
                }
                if (!$data['delivery_id']) {
                    throw new AdminException(400524);
                }
                if (!check_phone($data['delivery_id'])) {
                    throw new AdminException(400526);
                }
                break;
            case 'express':
                // 检测快递公司编码
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                if ($name = $expressServices->value(['code' => $data['delivery_code']], 'name')) {
                    $data['delivery_name'] = $name;
                } else {
                    throw new AdminException(410324);
                }
                break;
            default:
                throw new AdminException(400532);
        }
        /** @var StoreIntegralOrderStatusServices $statusService */
        $statusService = app()->make(StoreIntegralOrderStatusServices::class);
        $statusService->save([
            'oid' => $id,
            'change_type' => 'distribution',
            'change_message' => '修改发货信息为' . $data['delivery_name'] . '号' . $data['delivery_id'],
            'change_time' => time()
        ]);
        return $this->dao->update($id, $data);
    }

    /**
     * 批量删除用户已经删除的订单
     * @return string|null
     */
    public function delOrders(array $ids)
    {
        if (!count($ids)) throw new AdminException(100100);
        if ($this->getOrderIdsCount($ids)) throw new AdminException(400118);
        return $this->batchUpdate($ids, ['is_system_del' => 1]);
    }

    /**
     * 删除订单
     * @param $id
     * @return bool
     */
    public function delOrder(int $id)
    {
        if (!$id || !($orderInfo = $this->get($id))) throw new AdminException(400118);
        if (!$orderInfo->is_del) throw new AdminException(400157);
        $orderInfo->is_system_del = 1;
        return $orderInfo->save();
    }

    /**
     * 修改备注
     * @param int $id
     * @param string $remark
     * @return mixed
     */
    public function remark(int $id, string $remark)
    {
        if (!$remark) throw new AdminException(400106);
        if (!$id) throw new AdminException(100100);
        if (!$order = $this->services->get($id)) {
           throw new AdminException(100025);
        }

        $order->remark = $remark;
        return $order->save();
    }
}
