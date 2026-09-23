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

namespace app\services\activity\integral;

use app\dao\activity\integral\StoreIntegralOrderDao;
use crmeb\basic\BaseService;
use app\services\shipping\ExpressServices;
use app\services\user\UserServices;
use crmeb\services\logistics\Express;
use app\services\driver\express\ExpressDriverServices;
use app\services\user\UserAddressServices;
use app\services\user\UserBillServices;
use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\utils\FormBuilder as Form;
use crmeb\utils\SnowflakeIdGenerator;
use app\services\activity\integral\StoreIntegralOrderStatusServices;
use app\services\driver\express\ExpressConfigServices;
use app\services\user\UserUserServices;
use app\services\driver\printer\SystemTicketServices;

/**
 * Class StoreIntegralOrderServices
 * @package app\services\order
 * @method getOrderIdsCount(array $ids) 获取订单id下没有删除的订单数量
 * @method getUserOrderDetail(string $key, int $uid) 获取订单详情
 * @method getBuyCount($uid, $type) 获取用户已购买此活动商品的个数
 */
class StoreIntegralOrderServices extends BaseService
{
    /**
     * @var StoreIntegralOrderDao
     */
    protected $dao;

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
        $order['price'] = (int)$order['price'];
        $order['total_price'] = (int)$order['total_price'];
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
            $item['price'] = (int)$item['price'];
            $item['total_price'] = (int)$item['total_price'];
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
    public function createOrder($uid, $addressId, $mark, $userInfo, $num, $productInfo)
    {
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        if (!$addressId) {
            throw new ApiException('请选择收货地址');
        }
        if (!$addressInfo = $addressServices->getOne(['uid' => $uid, 'id' => $addressId, 'is_del' => 0])) throw new ApiException('地址选择有误');
        $addressInfo = $addressInfo->toArray();
        $total_price = bcmul($productInfo['price'], $num, 2);
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $usable_integral = bcsub((string)$userInfo['integral'], (string)$userBillServices->getBillSum(['uid' => $userInfo['uid'], 'is_frozen' => 1]), 0);
        if ($total_price > $usable_integral) throw new ApiException('可用积分不足');
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
                throw new ApiException('订单生成失败');
            }
            //扣库存
            $this->decGoodsStock($productInfo, $num);
            //减积分
            if (!$this->deductIntegral($userInfo, $total_price, (int)$userInfo['uid'], $order->id)) {
                throw new ApiException('积分抵扣失败');
            }
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
    public function deductIntegral(array $userInfo, $priceIntegral, int $uid, string $orderId): bool
    {
        if ($priceIntegral <= 0) {
            return true;
        }
        
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        
        return $userBillServices->billIntegralIncome('storeIntegral_use_integral', $uid, $priceIntegral, $orderId);
    }

    /**
     * 扣库存
     * @param array $cartInfo
     * @param int $combinationId
     * @param int $seckillId
     * @param int $bargainId
     */
    public function decGoodsStock(array $productInfo, int $num): bool
    {
        // 通过 ActivityDataProxy 事件代理扣减积分商品库存（避免直接引用 modules 层）
        $_results = event('ActivityDataProxy', [
            'mode' => 'integral',
            'action' => 'decIntegralStock',
            'args' => [(int)$num, $productInfo['product_id'], $productInfo['unique']],
        ]);
        $decResult = proxy_event_result($_results, false);

        if (!$decResult) {
            throw new ApiException('该商品库存不足');
        }

        return true;
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getNewOrderId(string $prefix = 'wx')
    {
        return SnowflakeIdGenerator::generate($prefix);
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
     * @return int 提交的打印设备数
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderPrint($order)
    {
        $order = is_object($order) ? $order->toArray() : $order;
        $printOrder = array_merge($order, [
            'total_price' => (string)$order['total_price'],
            'pay_postage' => '0.00', 'deduction_price' => '0.00',
            'pay_price' => (string)$order['total_price'],
            'pay_type' => '', 'pay_type_name' => '积分兑换', 'amount_unit' => '积分',
            'shipping_type' => (int)($order['shipping_type'] ?? 1),
            'pay_time' => (int)($order['pay_time'] ?? $order['add_time']),
        ]);
        $product = [[
            'sum_price' => (string)$order['price'], 'cart_num' => (int)$order['total_num'],
            'productInfo' => ['store_name' => $order['store_name'],
                'attrInfo' => ['suk' => $order['suk'] ?? '', 'bar_code' => $order['bar_code'] ?? '']],
        ]];
        $count = app()->make(SystemTicketServices::class)->startPrint($printOrder, $product, true);
        if ($count === 0) throw new AdminException('请先启用打印驱动并配置小票打印机');
        return $count;
    }

    /**
     * 获取订单确认数据
     * @param array $user
     * @param $cartId
     * @return mixed
     */
    public function getOrderConfirmData(array $user, $unique, $num)
    {
        // 通过 ActivityDataProxy 事件代理获取积分商品下单信息（避免直接引用 modules 层）
        $results = event('ActivityDataProxy', [
            'mode' => 'integral',
            'action' => 'getAttrValueByUnique',
            'args' => [$unique],
        ]);
        $attrValue = proxy_event_result($results, null);
        if (!$attrValue || empty($attrValue['storeIntegral'])) {
            throw new ApiException('该商品已下架或删除');
        }
        $data = [];
        $attrValue['price'] = (int)$attrValue['price'];
        /** @var UserBillServices $userBillServices */
        $userBillServices = app()->make(UserBillServices::class);
        $data['integral'] = bcsub((string)$user['integral'], (string)$userBillServices->getBillSum(['uid' => $user['uid'], 'is_frozen' => 1]), 0);
        $data['num'] = $num;
        $data['total_price'] = bcmul($num, $attrValue['price']);
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
            throw new ApiException('删除失败');

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
            throw new ApiException('删除失败');
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
            throw new AdminException('订单不存在');
        }
        if ($orderInfo->is_del) {
            throw new AdminException('订单已删除');
        }
        if ($orderInfo->status != 1) {
            throw new AdminException('订单已发货');
        }
        $type = (int)$data['type'];
        unset($data['type']);
        if ($type == 1) {
            // 检测快递公司编码
            /** @var ExpressServices $expressServices */
            $expressServices = app()->make(ExpressServices::class);
            if (!$expressServices->be(['code' => $data['delivery_code']])) {
                throw new AdminException('请核对快递公司编码');
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
                throw new AdminException('暂时不支持其他发货类型');
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
        if (!check_phone($data['delivery_id'])) {
            throw new AdminException('请输入正确的送货人电话号码');
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
            throw new AdminException('请选择快递公司');
        }
        $data['delivery_type'] = 'express';
        if ($data['express_record_type'] == 2) {//电子面单
            if (!$data['delivery_code']) {
                throw new AdminException('快递公司编号缺失');
            }
            if (!$data['express_temp_id']) {
                throw new AdminException('请选择电子面单模板');
            }
            if (!$data['to_name']) {
                throw new AdminException('请填写寄件人姓名');
            }
            if (!$data['to_tel']) {
                throw new AdminException('请输入寄件人手机号码');
            }
            if (!$data['to_addr']) {
                throw new AdminException('请填写寄件人详细地址');
            }
            $expData['com'] = $data['delivery_code'];
            $expData['to_name'] = $orderInfo->real_name;
            $expData['to_tel'] = $orderInfo->user_phone;
            $expData['to_addr'] = $orderInfo->user_address;
            $expData['from_name'] = $data['to_name'];
            $expData['from_tel'] = $data['to_tel'];
            $expData['from_addr'] = $data['to_addr'];
            /** @var ExpressDriverServices $driverServices */
            $driverServices = app()->make(ExpressDriverServices::class);
            $expData['siid'] = $driverServices->getCompanySiid($data['delivery_code']);
            $expData['temp_id'] = $data['express_temp_id'];
            $expData['count'] = $orderInfo->total_num;
            $expData['weight'] = 1;
            $expData['cargo'] = $orderInfo->store_name . '(' . $orderInfo->suk . ')*' . $orderInfo->total_num;
            $expData['order_id'] = $orderInfo->order_id;
            if (!app()->make(ExpressConfigServices::class)->getItem('open', 0)) {
                throw new AdminException('电子面单已关闭，请选择其他发货方式');
            }
            /** @var Express $expressService */
            $expressService = $driverServices->createExpressServiceByCode($data['delivery_code']);
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
                throw new AdminException('快递单号不存在');
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
                throw new AdminException('发货失败');
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
            throw new AdminException('订单不存在');

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
            throw new ApiException('订单不存在');
        }
        if ($order['status'] != 2) {
            throw new ApiException('订单状态错误');
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
            throw new ApiException('收货失败,请稍候再试');
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
            throw new AdminException('订单不存在');
        }
        switch ($order['delivery_type']) {
            case 'send':
                if (!$data['delivery_name']) {
                    throw new AdminException('请输入送货人姓名');
                }
                if (!$data['delivery_id']) {
                    throw new AdminException('请输入送货人电话号码');
                }
                if (!check_phone($data['delivery_id'])) {
                    throw new AdminException('请输入正确的送货人电话号码');
                }
                break;
            case 'express':
                // 检测快递公司编码
                /** @var ExpressServices $expressServices */
                $expressServices = app()->make(ExpressServices::class);
                if ($name = $expressServices->value(['code' => $data['delivery_code']], 'name')) {
                    $data['delivery_name'] = $name;
                } else {
                    throw new AdminException('请核对快递公司编码');
                }
                break;
            default:
                throw new AdminException('请先发货再修改配送信息');
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
        if (!count($ids)) throw new AdminException('参数错误');
        if ($this->getOrderIdsCount($ids)) throw new AdminException('订单不存在');
        return $this->batchUpdate($ids, ['is_system_del' => 1]);
    }

    /**
     * 删除订单
     * @param $id
     * @return bool
     */
    public function delOrder(int $id)
    {
        if (!$id || !($orderInfo = $this->get($id))) throw new AdminException('订单不存在');
        if (!$orderInfo->is_del) throw new AdminException('您选择的的订单存在用户未删除的订单');
        $orderInfo->is_system_del = 1;
        return $orderInfo->save();
    }

    /**
     * 修改备注
     * @param int $id
     * @param string $remark
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function remark(int $id, string $remark)
    {
        if (!$remark) throw new AdminException('备注不能为空');
        if (!$id) throw new AdminException('参数错误');
        if (!$order = $this->dao->get($id)) {
            throw new AdminException('备注失败');
        }

        $order->remark = $remark;
        return $order->save();
    }
}
