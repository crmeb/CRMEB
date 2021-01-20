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

use app\dao\order\StoreOrderDao;
use app\services\BaseServices;
use app\services\message\sms\SmsSendServices;
use app\services\pay\PayServices;
use app\services\product\product\StoreProductLogServices;
use app\services\user\UserInvoiceServices;
use app\services\user\UserServices;
use app\services\product\product\StoreProductReplyServices;
use app\services\user\UserAddressServices;
use app\services\user\UserBillServices;
use app\services\wechat\WechatUserServices;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\SystemConfigService;
use crmeb\traits\ServicesTrait;
use crmeb\utils\Arr;
use think\exception\ValidateException;
use think\facade\Log;

/**
 * Class StoreOrderServices
 * @package app\services\order
 * @method getOrderIdsCount(array $ids) 获取订单id下没有删除的订单数量
 * @method StoreOrderDao getUserOrderDetail(string $key, int $uid) 获取订单详情
 * @method chartTimePrice($start, $stop) 获取当前时间到指定时间的支付金额 管理员
 * @method chartTimeNumber($start, $stop) 获取当前时间到指定时间的支付订单数 管理员
 * @method together(array $where, string $field, string $together = 'sum') 聚合查询
 * @method getBuyCount($uid, $type, $typeId) 获取用户已购买此活动商品的个数
 * @method getDistinctCount(array $where, $field, ?bool $search = true)
 * @method getTrendData($time, $type, $timeType, $str) 用户趋势
 * @method getRegion($time, $channelType) 地域统计
 * @method getProductTrend($time, $timeType, $field, $str) 商品趋势
 */
class StoreOrderServices extends BaseServices
{

    use ServicesTrait;

    /**
     * 发货类型
     * @var string[]
     */
    public $deliveryType = ['send' => '商家配送', 'express' => '快递配送', 'fictitious' => '虚拟发货'];

    /**
     * StoreOrderProductServices constructor.
     * @param StoreOrderDao $dao
     */
    public function __construct(StoreOrderDao $dao)
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
        $stat = $this->getBadge($where);
        return compact('data', 'count', 'stat');
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
        foreach ($data as &$item) {
            $item = $this->tidyOrder($item, true);
            if ($item['_status']['_type'] == 3) {
                foreach ($item['cartInfo'] ?: [] as $key => $product) {
                    $item['cartInfo'][$key]['add_time'] = isset($product['add_time']) ? date('Y-m-d H:i', (int)$product['add_time']) : '时间错误';
                }
            }
        }
        return $data;
    }

    /**
     * 获取订单数量
     * @param int $uid
     * @return mixed
     */
    public function getOrderData(int $uid = 0)
    {
        $where = ['uid' => $uid, 'paid' => 1, 'refund_status' => 0, 'is_del' => 0, 'is_system_del' => 0];
        $data['order_count'] = (string)$this->dao->count($where);
        $data['sum_price'] = (string)$this->dao->sum($where, 'pay_price');
        $countWhere = ['is_del' => 0, 'is_system_del' => 0];
        if ($uid) {
            $countWhere['uid'] = $uid;
        }
        $data['unpaid_count'] = (string)$this->dao->count(['status' => 0] + $countWhere);
        $data['unshipped_count'] = (string)$this->dao->count(['status' => 1] + $countWhere);
        $data['received_count'] = (string)$this->dao->count(['status' => 2] + $countWhere);
        $data['evaluated_count'] = (string)$this->dao->count(['status' => 3] + $countWhere);
        $data['complete_count'] = (string)$this->dao->count(['status' => 4] + $countWhere);
        $data['refund_count'] = (string)$this->dao->count(['status' => -3] + $countWhere);
        $data['yue_pay_status'] = (int)sys_config('yue_pay_status') == 1 ? (int)1 : (int)2;//余额支付 1 开启 2 关闭
        $data['pay_weixin_open'] = (int)sys_config('pay_weixin_open') ?? 0;//微信支付 1 开启 0 关闭
        return $data;
    }

    /**
     * 订单详情数据格式化
     * @param $order
     * @param bool $detail 是否需要订单商品详情
     * @param bool $isPic 是否需要订单状态图片
     * @return mixed
     */
    public function tidyOrder($order, bool $detail = false, $isPic = false)
    {
        if ($detail == true && isset($order['id'])) {
            /** @var StoreOrderCartInfoServices $cartServices */
            $cartServices = app()->make(StoreOrderCartInfoServices::class);
            $cartInfo = $cartServices->getCartColunm(['oid' => $order['id']], 'cart_info', 'unique');
            $info = [];
            /** @var StoreProductReplyServices $replyServices */
            $replyServices = app()->make(StoreProductReplyServices::class);
            foreach ($cartInfo as $k => $cart) {
                $cart = json_decode($cart, true);
                $cart['unique'] = $k;
                //新增是否评价字段
                $cart['is_reply'] = $replyServices->count(['unique' => $k]);
                array_push($info, $cart);
                unset($cart);
            }
            $order['cartInfo'] = $info;
        }
        /** @var StoreOrderStatusServices $statusServices */
        $statusServices = app()->make(StoreOrderStatusServices::class);
        $status = [];
        if (!$order['paid'] && $order['pay_type'] == 'offline' && !$order['status'] >= 2) {
            $status['_type'] = 9;
            $status['_title'] = '线下付款,未支付';
            $status['_msg'] = '等待商家处理,请耐心等待';
            $status['_class'] = 'nobuy';
        } else if (!$order['paid']) {
            $status['_type'] = 0;
            $status['_title'] = '未支付';
            //系统预设取消订单时间段
            $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
            //获取配置
            $systemValue = SystemConfigService::more($keyValue);
            //格式化数据
            $systemValue = Arr::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);

            $time = $order['add_time'] + $systemValue['order_cancel_time'] * 3600;
            $status['_msg'] = '请在' . date('m-d H:i:s', (int)$time) . '前完成支付!';
            $status['_class'] = 'nobuy';
        } else if ($order['refund_status'] == 1) {
            $status['_type'] = -1;
            $status['_title'] = '申请退款中';
            $status['_msg'] = '商家审核中,请耐心等待';
            $status['_class'] = 'state-sqtk';
        } else if ($order['refund_status'] == 2) {
            $status['_type'] = -2;
            $status['_title'] = '已退款';
            $status['_msg'] = '已为您退款,感谢您的支持';
            $status['_class'] = 'state-sqtk';
        } else if (!$order['status']) {
            if ($order['shipping_type'] === 1) {
                $status['_type'] = 1;
                $status['_title'] = '未发货';
                $status['_msg'] = '商家未发货,请耐心等待';
                $status['_class'] = 'state-nfh';
            } else {
                $status['_type'] = 1;
                $status['_title'] = '待核销';
                $status['_msg'] = '待核销,请到核销点进行核销';
                $status['_class'] = 'state-nfh';
            }

        } else if ($order['status'] == 1) {
            if ($order['delivery_type'] == 'send') {//TODO 送货
                $status['_type'] = 2;
                $status['_title'] = '待收货';
                $status['_msg'] = date('m月d日H时i分', $statusServices->value(['oid' => $order['id'], 'change_type' => 'delivery'], 'change_time')) . '服务商已送货';
                $status['_class'] = 'state-ysh';
            } elseif ($order['delivery_type'] == 'express') {//TODO  发货
                $status['_type'] = 2;
                $status['_title'] = '待收货';
                $status['_msg'] = date('m月d日H时i分', $statusServices->value(['oid' => $order['id'], 'change_type' => 'delivery_goods'], 'change_time')) . '服务商已发货';
                $status['_class'] = 'state-ysh';
            } else {
                $status['_type'] = 2;
                $status['_title'] = '待收货';
                $status['_msg'] = date('m月d日H时i分', $statusServices->value(['oid' => $order['id'], 'change_type' => 'delivery_fictitious'], 'change_time')) . '服务商已虚拟发货';
                $status['_class'] = 'state-ysh';
            }
        } else if ($order['status'] == 2) {
            $status['_type'] = 3;
            $status['_title'] = '待评价';
            $status['_msg'] = '已收货,快去评价一下吧';
            $status['_class'] = 'state-ypj';
        } else if ($order['status'] == 3) {
            $status['_type'] = 4;
            $status['_title'] = '交易完成';
            $status['_msg'] = '交易完成,感谢您的支持';
            $status['_class'] = 'state-ytk';
        }
        if (isset($order['pay_type']))
            $status['_payType'] = $status['_type'] == 0 ? '' : PayServices::PAY_TYPE[$order['pay_type']] ?? '其他方式';
        if (isset($order['delivery_type']))
            $status['_deliveryType'] = isset($this->deliveryType[$order['delivery_type']]) ? $this->deliveryType[$order['delivery_type']] : '其他方式';
        $order['_status'] = $status;
        $order['_pay_time'] = isset($order['pay_time']) && $order['pay_time'] != null ? date('Y-m-d H:i:s', $order['pay_time']) : '';
        $order['_add_time'] = isset($order['add_time']) ? (strstr((string)$order['add_time'], '-') === false ? date('Y-m-d H:i:s', $order['add_time']) : $order['add_time']) : '';
        $order['stop_time'] = sys_config('order_cancel_time') * 3600 + $order['add_time'];
        $order['status_pic'] = '';
        //获取商品状态图片
        if ($isPic) {
            $order_details_images = sys_data('order_details_images') ?: [];
            foreach ($order_details_images as $image) {
                if (isset($image['order_status']) && $image['order_status'] == $order['_status']['_type']) {
                    $order['status_pic'] = $image['pic'];
                    break;
                }
            }
        }
        $order['offlinePayStatus'] = (int)sys_config('offline_pay_status') ?? (int)2;
        $log = $statusServices->getColumn(['oid' => $order['id']], 'change_time', 'change_type');
        if (isset($log['delivery'])) {
            $delivery = date('Y-m-d', $log['delivery']);
        } elseif (isset($log['delivery_goods'])) {
            $delivery = date('Y-m-d', $log['delivery_goods']);
        } elseif (isset($log['delivery_fictitious'])) {
            $delivery = date('Y-m-d', $log['delivery_fictitious']);
        } else {
            $delivery = '';
        }
        $order['order_log'] = [
            'create' => isset($log['cache_key_create_order']) ? date('Y-m-d', $log['cache_key_create_order']) : '',
            'pay' => isset($log['pay_success']) ? date('Y-m-d', $log['pay_success']) : '',
            'delivery' => $delivery,
            'take' => isset($log['take_delivery']) ? date('Y-m-d', $log['take_delivery']) : '',
            'complete' => isset($log['check_order_over']) ? date('Y-m-d', $log['check_order_over']) : '',
        ];
        return $order;
    }

    /**
     * 数据转换
     * @param array $data
     * @return array
     */
    public function tidyOrderList(array $data)
    {
        /** @var StoreOrderCartInfoServices $services */
        $services = app()->make(StoreOrderCartInfoServices::class);
        foreach ($data as &$item) {
            $item['_info'] = $services->getOrderCartInfo((int)$item['id']);
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_pay_time'] = isset($item['pay_time']) && $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '';
            if ($item['shipping_type'] == 1) {
                $item['pink_name'] = '[普通订单]';
                $item['color'] = '#895612';
            } else if ($item['shipping_type'] == 2) {
                $item['pink_name'] = '[核销订单]';
                $item['color'] = '#8956E8';
            }
            if ($item['paid'] == 1) {
                switch ($item['pay_type']) {
                    case PayServices::WEIXIN_PAY:
                        $item['pay_type_name'] = '微信支付';
                        break;
                    case PayServices::YUE_PAY:
                        $item['pay_type_name'] = '余额支付';
                        break;
                    case PayServices::OFFLINE_PAY:
                        $item['pay_type_name'] = '线下支付';
                        break;
                        break;
                    default:
                        $item['pay_type_name'] = '其他支付';
                        break;
                }
            } else {
                switch ($item['pay_type']) {
                    default:
                        $item['pay_type_name'] = '未支付';
                        break;
                    case 'offline':
                        $item['pay_type_name'] = '线下支付';
                        $item['pay_type_info'] = 1;
                        break;
                }
            }
            $status_name = ['status_name' => '', 'pics' => []];
            if ($item['paid'] == 0 && $item['status'] == 0) {
                $status_name['status_name'] = '未支付';
            } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '未发货';
            } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '未核销';
            } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '待收货';
            } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '未核销';
            } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '待评价';
            } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                $status_name['status_name'] = '已完成';
            } else if ($item['paid'] == 1 && $item['refund_status'] == 1) {
                $refundReasonTime = date('Y-m-d H:i', $item['refund_reason_time']);
                $refundReasonWapImg = json_decode($item['refund_reason_wap_img'], true);
                $refundReasonWapImg = $refundReasonWapImg ? $refundReasonWapImg : [];
                $img = [];
                if (count($refundReasonWapImg)) {
                    foreach ($refundReasonWapImg as $itemImg) {
                        if (strlen(trim($itemImg)))
                            $img[] = $itemImg;
                    }
                }
                $status_name['status_name'] = <<<HTML
<b style="color:#f124c7">申请退款</b><br/>
<span>退款原因：{$item['refund_reason_wap']}</span><br/>
<span>备注说明：{$item['refund_reason_wap_explain']}</span><br/>
<span>退款时间：{$refundReasonTime}</span><br/>
<span>退款凭证：</span>
HTML;
                $status_name['pics'] = $img;
            } else if ($item['paid'] == 1 && $item['refund_status'] == 2) {
                $status_name['status_name'] = '已退款';
            }
            $item['status_name'] = $status_name;
            if ($item['paid'] == 0 && $item['status'] == 0 && $item['refund_status'] == 0) {
                $item['_status'] = 1;//未支付
            } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['refund_status'] == 0) {
                $item['_status'] = 2;//已支付 未发货
            } else if ($item['paid'] == 1 && $item['refund_status'] == 1) {
                $item['_status'] = 3;//已支付 申请退款中
            } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['refund_status'] == 0) {
                $item['_status'] = 4;//已支付 待收货
            } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                $item['_status'] = 5;//已支付 待评价
            } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                $item['_status'] = 6;//已支付 已完成
            } else if ($item['paid'] == 1 && $item['refund_status'] == 2) {
                $item['_status'] = 7;//已支付 已退款
            }
            if ($item['clerk_id'] == 0 && !isset($item['clerk_name'])) {
                $item['clerk_name'] = '总平台';
            }
            //根据核销员更改store_name
            if ($item['clerk_id'] && isset($item['staff_store_id']) && $item['staff_store_id']) {
                $item['store_name'] = '';
            }
        }
        return $data;
    }

    /**
     * 处理订单金额
     * @param $where
     * @return array
     */
    public function getOrderPrice($where)
    {
        $where['is_del'] = 0;//删除订单不统计
        $price['pay_price'] = 0;//支付金额
        $price['refund_price'] = 0;//退款金额
        $price['pay_price_wx'] = 0;//微信支付金额
        $price['pay_price_yue'] = 0;//余额支付金额
        $price['pay_price_offline'] = 0;//线下支付金额
        $price['pay_price_other'] = 0;//其他支付金额
        $price['use_integral'] = 0;//用户使用积分
        $price['back_integral'] = 0;//退积分总数
        $price['deduction_price'] = 0;//抵扣金额
        $price['total_num'] = 0; //商品总数
        $price['count_sum'] = 0; //商品总数
        $price['brokerage'] = 0;
        $price['pay_postage'] = 0;
        $whereData = ['is_del' => 0];
        if ($where['status'] == '') {
            $whereData['paid'] = 1;
            $whereData['refund_status'] = 0;
        }
        $ids = $this->dao->column($where + $whereData, 'id');
        if (count($ids)) {
            /** @var UserBillServices $services */
            $services = app()->make(UserBillServices::class);
            $price['brokerage'] = $services->getBrokerageNumSum($ids);
        }
        $price['refund_price'] = $this->dao->together($where + ['is_del' => 0, 'paid' => 1, 'refund_status' => 2], 'refund_price');
//        if ($where['type'] == '') {
//            $whereData = [];
//        }
        $sumNumber = $this->dao->search($where + $whereData)->field([
            'sum(total_num) as sum_total_num',
            'count(id) as count_sum',
            'sum(pay_price) as sum_pay_price',
            'sum(pay_postage) as sum_pay_postage',
            'sum(use_integral) as sum_use_integral',
            'sum(back_integral) as sum_back_integral',
            'sum(deduction_price) as sum_deduction_price'
        ])->find();
        if ($sumNumber) {
            $price['count_sum'] = $sumNumber['count_sum'];
            $price['total_num'] = $sumNumber['sum_total_num'];
            $price['pay_price'] = $sumNumber['sum_pay_price'];
            $price['pay_postage'] = $sumNumber['sum_pay_postage'];
            $price['use_integral'] = $sumNumber['sum_use_integral'];
            $price['back_integral'] = $sumNumber['sum_back_integral'];
            $price['deduction_price'] = $sumNumber['sum_deduction_price'];
        }
        $list = $this->dao->column($where + $whereData, 'sum(pay_price) as sum_pay_price,pay_type', 'id', 'pay_type');
        foreach ($list as $v) {
            if ($v['pay_type'] == 'weixin') {
                $price['pay_price_wx'] = $v['sum_pay_price'];
            } elseif ($v['pay_type'] == 'yue') {
                $price['pay_price_yue'] = $v['sum_pay_price'];
            } elseif ($v['pay_type'] == 'offline') {
                $price['pay_price_offline'] = $v['sum_pay_price'];
            } else {
                $price['pay_price_other'] = $v['sum_pay_price'];
            }
        }
        return $price;
    }

    /**
     * 获取订单列表页面统计数据
     * @param $where
     * @return array
     */
    public function getBadge($where)
    {
        $price = $this->getOrderPrice($where);
        return [
            [
                'name' => '订单数量',
                'field' => '件',
                'count' => $price['count_sum'],
                'className' => 'md-basket',
                'col' => 6
            ],
            [
                'name' => '订单金额',
                'field' => '元',
                'count' => $price['pay_price'],
                'className' => 'md-pricetags',
                'col' => 6
            ],
            [
                'name' => '微信支付金额',
                'field' => '元',
                'count' => $price['pay_price_wx'],
                'className' => 'ios-chatbubbles',
                'col' => 6
            ],
            [
                'name' => '余额支付金额',
                'field' => '元',
                'count' => $price['pay_price_yue'],
                'className' => 'ios-cash',
                'col' => 6
            ],
        ];
    }

    /**
     *
     * @param array $where
     * @return mixed
     */
    public function orderCount(array $where)
    {
        //全部订单
        $data['all'] = (string)$this->dao->count(['time' => $where['time'], 'is_system_del' => 0]);
        //普通订单
        $data['general'] = (string)$this->dao->count(['type' => 1, 'is_system_del' => 0]);
        switch ($where['type']) {
            case 0:
                $data['statusAll'] = $data['all'];
                break;
            case 1:
                $data['statusAll'] = $data['general'];
                break;
        }
        //未支付
        $data['unpaid'] = (string)$this->dao->count(['status' => 0, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //未发货
        $data['unshipped'] = (string)$this->dao->count(['status' => 1, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待收货
        $data['untake'] = (string)$this->dao->count(['status' => 2, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待核销
        $data['write_off'] = (string)$this->dao->count(['status' => 5, 'time' => $where['time'], 'shipping_type' => 1, 'is_system_del' => 0, 'type' => $where['type']]);
        //待评价
        $data['unevaluate'] = (string)$this->dao->count(['status' => 3, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //交易完成
        $data['complete'] = (string)$this->dao->count(['status' => 4, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //退款中
        $data['refunding'] = (string)$this->dao->count(['status' => -1, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //已退款
        $data['refund'] = (string)$this->dao->count(['status' => -2, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        //删除订单
        $data['del'] = (string)$this->dao->count(['status' => -4, 'time' => $where['time'], 'is_system_del' => 0, 'type' => $where['type']]);
        return $data;
    }

    /**
     * 创建修改订单表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function updateForm(int $id)
    {
        $product = $this->dao->get($id);
        if (!$product) {
            throw new ValidateException('Data does not exist!');
        }
        $f = [];
        $f[] = Form::input('order_id', '订单编号', $product->getData('order_id'))->disabled(true);
        $f[] = Form::number('total_price', '商品总价', (float)$product->getData('total_price'))->min(0);
        $f[] = Form::number('total_postage', '原始邮费', (float)$product->getData('total_postage'))->min(0);
        $f[] = Form::number('pay_price', '实际支付金额', (float)$product->getData('pay_price'))->min(0);
        $f[] = Form::number('pay_postage', '实际支付邮费', (float)$product->getData('pay_postage') ?: 0);
        $f[] = Form::number('gain_integral', '赠送积分', (float)$product->getData('gain_integral') ?: 0);
        return create_form('修改订单', $f, $this->url('/order/update/' . $id), 'PUT');
    }

    /**
     * 修改订单
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function updateOrder(int $id, array $data)
    {
        $order = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        if (!$order) {
            throw new ValidateException('订单不存在或已删除');
        }
        /** @var StoreOrderCreateServices $createServices */
        $createServices = app()->make(StoreOrderCreateServices::class);
        $data['order_id'] = $createServices->getNewOrderId();
        /** @var StoreOrderStatusServices $services */
        $services = app()->make(StoreOrderStatusServices::class);
        return $this->transaction(function () use ($id, $order, $data, $services) {
            $res = $this->dao->update($id, $data);
            $res = $res && $services->save([
                    'oid' => $id,
                    'change_type' => 'order_edit',
                    'change_time' => time(),
                    'change_message' => '修改商品总价为：' . $data['total_price'] . ' 实际支付金额' . $data['pay_price']
                ]);
            if ($res) {
                //改价短信提醒
                if ($data['pay_price'] != $order['pay_price']) {
                    $switch = sys_config('price_revision_switch') ? true : false;
                    if ($switch) {
                        /** @var SmsSendServices $smsServices */
                        $smsServices = app()->make(SmsSendServices::class);
                        $smsServices->send($switch, $order['user_phone'], ['order_id' => $order['order_id'], 'pay_price' => $data['pay_price']], 'PRICE_REVISION_CODE');
                    }
                }
                return true;
            } else {
                throw new ValidateException('Modification failed');
            }
        });
    }

    /**
     * 订单图表
     * @param $cycle
     * @return array
     */
    public function orderCharts($cycle)
    {
        $datalist = [];
        switch ($cycle) {
            case 'thirtyday':
                $datebefor = date('Y-m-d', strtotime('-30 day'));
                $dateafter = date('Y-m-d');
                //上期
                $pre_datebefor = date('Y-m-d', strtotime('-60 day'));
                $pre_dateafter = date('Y-m-d', strtotime('-30 day'));
                for ($i = -30; $i < 0; $i++) {
                    $datalist[date('m-d', strtotime($i . ' day'))] = date('m-d', strtotime($i . ' day'));
                }
                $order_list = $this->dao->orderAddTimeList($datebefor, $dateafter, '30');
                if (empty($order_list)) return ['yAxis' => [], 'legend' => [], 'xAxis' => [], 'serise' => [], 'pre_cycle' => [], 'cycle' => []];
                foreach ($order_list as $k => &$v) {
                    $order_list[$v['day']] = $v;
                }
                $cycle_list = [];
                foreach ($datalist as $dk => $dd) {
                    if (!empty($order_list[$dd])) {
                        $cycle_list[$dd] = $order_list[$dd];
                    } else {
                        $cycle_list[$dd] = ['count' => 0, 'day' => $dd, 'price' => ''];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($cycle_list as $k => $v) {
                    $data['day'][] = $v['day'];
                    $data['count'][] = $v['count'];
                    $data['price'][] = round($v['price'], 2);
                    if ($chartdata['yAxis']['maxnum'] < $v['count'])
                        $chartdata['yAxis']['maxnum'] = $v['count'];//日最大订单数
                    if ($chartdata['yAxis']['maxprice'] < $v['price'])
                        $chartdata['yAxis']['maxprice'] = $v['price'];//日最大金额
                }
                $chartdata['legend'] = ['订单金额', '订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                $series1 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#69cdff'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#3eb3f7'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#1495eb'
                        ]
                    ]
                ]]
                ];
                $series2 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#6fdeab'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#44d693'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#2cc981'
                        ]
                    ]
                ]]
                ];
                $chartdata['series'][] = ['name' => $chartdata['legend'][0], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][1], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['count'], 'yAxisIndex' => 1];//分类2值
                //统计总数上期
                $pre_total = $this->dao->preTotalFind($pre_datebefor, $pre_dateafter);
                if ($pre_total) {
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count'] ?: 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price'] ?: 0
                    ];
                }
                //统计总数
                $total = $this->dao->preTotalFind($datebefor, $dateafter);
                if ($total) {
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count'] == 0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count'] ?: 0,
                        'percent' => round((abs($cha_count) / intval($pre_total['count']) * 100), 2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'], 2) - round($total['price'], 2);
                    $pre_total['price'] = $pre_total['price'] == 0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price'] ?: 0,
                        'percent' => round(abs($cha_price) / $pre_total['price'] * 100, 2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return $chartdata;
                break;
            case 'week':
                $weekarray = array(['周日'], ['周一'], ['周二'], ['周三'], ['周四'], ['周五'], ['周六']);
                $datebefor = date('Y-m-d', strtotime('-1 week Monday'));
                $dateafter = date('Y-m-d', strtotime('-1 week Sunday'));
                $order_list = $this->dao->orderAddTimeList($datebefor, $dateafter, 'week');
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k => $v) {
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
                $now_dateafter = date('Y-m-d', strtotime("+1 day"));
                $now_order_list = $this->dao->nowOrderList($now_datebefor, $now_dateafter, 'week');
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k => $v) {
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk => $dd) {
                    if (!empty($new_order_list[$dk])) {
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    } else {
                        $weekarray[$dk]['pre'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                    if (!empty($new_now_order_list[$dk])) {
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    } else {
                        $weekarray[$dk]['now'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k => $v) {
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'], 2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'], 2);
                    if ($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']) {
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count'] > $v['now']['count'] ? $v['pre']['count'] : $v['now']['count'];//日最大订单数
                    }
                    if ($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']) {
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price'] > $v['now']['price'] ? $v['pre']['price'] : $v['now']['price'];//日最大金额
                    }
                }
                $chartdata['legend'] = ['上周金额', '本周金额', '上周订单数', '本周订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                $series1 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#69cdff'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#3eb3f7'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#1495eb'
                        ]
                    ]
                ]]
                ];
                $series2 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#6fdeab'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#44d693'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#2cc981'
                        ]
                    ]
                ]]
                ];
                $chartdata['series'][] = ['name' => $chartdata['legend'][0], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][1], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['now']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][2], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['pre']['count'], 'yAxisIndex' => 1];//分类2值
                $chartdata['series'][] = ['name' => $chartdata['legend'][3], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['now']['count'], 'yAxisIndex' => 1];//分类2值

                //统计总数上期
                $pre_total = $this->dao->preTotalFind($datebefor, $dateafter);
                if ($pre_total) {
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count'] ?: 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price'] ?: 0
                    ];
                }
                //统计总数
                $total = $this->dao->preTotalFind($now_datebefor, $now_dateafter);
                if ($total) {
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count'] == 0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count'] ?: 0,
                        'percent' => round((abs($cha_count) / intval($pre_total['count']) * 100), 2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'], 2) - round($total['price'], 2);
                    $pre_total['price'] = $pre_total['price'] == 0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price'] ?: 0,
                        'percent' => round(abs($cha_price) / $pre_total['price'] * 100, 2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return $chartdata;
                break;
            case 'month':
                $weekarray = array('01' => ['1'], '02' => ['2'], '03' => ['3'], '04' => ['4'], '05' => ['5'], '06' => ['6'], '07' => ['7'], '08' => ['8'], '09' => ['9'], '10' => ['10'], '11' => ['11'], '12' => ['12'], '13' => ['13'], '14' => ['14'], '15' => ['15'], '16' => ['16'], '17' => ['17'], '18' => ['18'], '19' => ['19'], '20' => ['20'], '21' => ['21'], '22' => ['22'], '23' => ['23'], '24' => ['24'], '25' => ['25'], '26' => ['26'], '27' => ['27'], '28' => ['28'], '29' => ['29'], '30' => ['30'], '31' => ['31']);

                $datebefor = date('Y-m-01', strtotime('-1 month'));
                $dateafter = date('Y-m-d', strtotime(date('Y-m-01')));
                $order_list = $this->dao->orderAddTimeList($datebefor, $dateafter, "month");
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k => $v) {
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-m-01');
                $now_dateafter = date('Y-m-d', strtotime("+1 day"));
                $now_order_list = $this->dao->nowOrderList($now_datebefor, $now_dateafter, "month");
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k => $v) {
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk => $dd) {
                    if (!empty($new_order_list[$dk])) {
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    } else {
                        $weekarray[$dk]['pre'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                    if (!empty($new_now_order_list[$dk])) {
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    } else {
                        $weekarray[$dk]['now'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k => $v) {
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'], 2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'], 2);
                    if ($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']) {
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count'] > $v['now']['count'] ? $v['pre']['count'] : $v['now']['count'];//日最大订单数
                    }
                    if ($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']) {
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price'] > $v['now']['price'] ? $v['pre']['price'] : $v['now']['price'];//日最大金额
                    }

                }
                $chartdata['legend'] = ['上月金额', '本月金额', '上月订单数', '本月订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                $series1 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#69cdff'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#3eb3f7'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#1495eb'
                        ]
                    ]
                ]]
                ];
                $series2 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#6fdeab'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#44d693'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#2cc981'
                        ]
                    ]
                ]]
                ];
                $chartdata['series'][] = ['name' => $chartdata['legend'][0], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][1], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['now']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][2], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['pre']['count'], 'yAxisIndex' => 1];//分类2值
                $chartdata['series'][] = ['name' => $chartdata['legend'][3], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['now']['count'], 'yAxisIndex' => 1];//分类2值

                //统计总数上期
                $pre_total = $this->dao->preTotalFind($datebefor, $dateafter);
                if ($pre_total) {
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count'] ?: 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price'] ?: 0
                    ];
                }
                //统计总数
                $total = $this->dao->preTotalFind($now_datebefor, $now_dateafter);
                if ($total) {
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count'] == 0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count'] ?: 0,
                        'percent' => round((abs($cha_count) / intval($pre_total['count']) * 100), 2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'], 2) - round($total['price'], 2);
                    $pre_total['price'] = $pre_total['price'] == 0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price'] ?: 0,
                        'percent' => round(abs($cha_price) / $pre_total['price'] * 100, 2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return $chartdata;
                break;
            case 'year':
                $weekarray = array('01' => ['一月'], '02' => ['二月'], '03' => ['三月'], '04' => ['四月'], '05' => ['五月'], '06' => ['六月'], '07' => ['七月'], '08' => ['八月'], '09' => ['九月'], '10' => ['十月'], '11' => ['十一月'], '12' => ['十二月']);
                $datebefor = date('Y-01-01', strtotime('-1 year'));
                $dateafter = date('Y-12-31', strtotime('-1 year'));
                $order_list = $this->dao->orderAddTimeList($datebefor, $dateafter, 'year');
                //数据查询重新处理
                $new_order_list = [];
                foreach ($order_list as $k => $v) {
                    $new_order_list[$v['day']] = $v;
                }
                $now_datebefor = date('Y-01-01');
                $now_dateafter = date('Y-m-d');
                $now_order_list = $this->dao->nowOrderList($now_datebefor, $now_dateafter, 'year');
                //数据查询重新处理 key 变为当前值
                $new_now_order_list = [];
                foreach ($now_order_list as $k => $v) {
                    $new_now_order_list[$v['day']] = $v;
                }
                foreach ($weekarray as $dk => $dd) {
                    if (!empty($new_order_list[$dk])) {
                        $weekarray[$dk]['pre'] = $new_order_list[$dk];
                    } else {
                        $weekarray[$dk]['pre'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                    if (!empty($new_now_order_list[$dk])) {
                        $weekarray[$dk]['now'] = $new_now_order_list[$dk];
                    } else {
                        $weekarray[$dk]['now'] = ['count' => 0, 'day' => $weekarray[$dk][0], 'price' => '0'];
                    }
                }
                $chartdata = [];
                $data = [];//临时
                $chartdata['yAxis']['maxnum'] = 0;//最大值数量
                $chartdata['yAxis']['maxprice'] = 0;//最大值金额
                foreach ($weekarray as $k => $v) {
                    $data['day'][] = $v[0];
                    $data['pre']['count'][] = $v['pre']['count'];
                    $data['pre']['price'][] = round($v['pre']['price'], 2);
                    $data['now']['count'][] = $v['now']['count'];
                    $data['now']['price'][] = round($v['now']['price'], 2);
                    if ($chartdata['yAxis']['maxnum'] < $v['pre']['count'] || $chartdata['yAxis']['maxnum'] < $v['now']['count']) {
                        $chartdata['yAxis']['maxnum'] = $v['pre']['count'] > $v['now']['count'] ? $v['pre']['count'] : $v['now']['count'];//日最大订单数
                    }
                    if ($chartdata['yAxis']['maxprice'] < $v['pre']['price'] || $chartdata['yAxis']['maxprice'] < $v['now']['price']) {
                        $chartdata['yAxis']['maxprice'] = $v['pre']['price'] > $v['now']['price'] ? $v['pre']['price'] : $v['now']['price'];//日最大金额
                    }
                }
                $chartdata['legend'] = ['去年金额', '今年金额', '去年订单数', '今年订单数'];//分类
                $chartdata['xAxis'] = $data['day'];//X轴值
                $series1 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#69cdff'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#3eb3f7'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#1495eb'
                        ]
                    ]
                ]]
                ];
                $series2 = ['normal' => ['color' => [
                    'x' => 0, 'y' => 0, 'x2' => 0, 'y2' => 1,
                    'colorStops' => [
                        [
                            'offset' => 0,
                            'color' => '#6fdeab'
                        ],
                        [
                            'offset' => 0.5,
                            'color' => '#44d693'
                        ],
                        [
                            'offset' => 1,
                            'color' => '#2cc981'
                        ]
                    ]
                ]]
                ];
                $chartdata['series'][] = ['name' => $chartdata['legend'][0], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['pre']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][1], 'type' => 'bar', 'itemStyle' => $series1, 'data' => $data['now']['price']];//分类1值
                $chartdata['series'][] = ['name' => $chartdata['legend'][2], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['pre']['count'], 'yAxisIndex' => 1];//分类2值
                $chartdata['series'][] = ['name' => $chartdata['legend'][3], 'type' => 'line', 'itemStyle' => $series2, 'data' => $data['now']['count'], 'yAxisIndex' => 1];//分类2值

                //统计总数上期
                $pre_total = $this->dao->preTotalFind($datebefor, $dateafter);
                if ($pre_total) {
                    $chartdata['pre_cycle']['count'] = [
                        'data' => $pre_total['count'] ?: 0
                    ];
                    $chartdata['pre_cycle']['price'] = [
                        'data' => $pre_total['price'] ?: 0
                    ];
                }
                //统计总数
                $total = $this->dao->preTotalFind($now_datebefor, $now_dateafter);
                if ($total) {
                    $cha_count = intval($pre_total['count']) - intval($total['count']);
                    $pre_total['count'] = $pre_total['count'] == 0 ? 1 : $pre_total['count'];
                    $chartdata['cycle']['count'] = [
                        'data' => $total['count'] ?: 0,
                        'percent' => round((abs($cha_count) / intval($pre_total['count']) * 100), 2),
                        'is_plus' => $cha_count > 0 ? -1 : ($cha_count == 0 ? 0 : 1)
                    ];
                    $cha_price = round($pre_total['price'], 2) - round($total['price'], 2);
                    $pre_total['price'] = $pre_total['price'] == 0 ? 1 : $pre_total['price'];
                    $chartdata['cycle']['price'] = [
                        'data' => $total['price'] ?: 0,
                        'percent' => round(abs($cha_price) / $pre_total['price'] * 100, 2),
                        'is_plus' => $cha_price > 0 ? -1 : ($cha_price == 0 ? 0 : 1)
                    ];
                }
                return $chartdata;
                break;
            default:
                break;
        }
    }

    /**
     * 获取订单数量
     * @return int
     */
    public function storeOrderCount()
    {
        return $this->dao->storeOrderCount();
    }

    /**
     * 新订单ID
     * @param $status
     * @return array
     */
    public function newOrderId($status)
    {
        return $this->dao->search(['status' => $status, 'is_remind' => 0])->column('order_id', 'id');;
    }

    /**
     * 新订单修改
     * @param $newOrderId
     * @return \crmeb\basic\BaseModel
     */
    public function newOrderUpdate($newOrderId)
    {
        return $this->dao->newOrderUpdates($newOrderId);
    }

    /**
     * 增长率
     * @param $left
     * @param $right
     * @return int|string
     */
    public function growth($nowValue, $lastValue)
    {
        if ($lastValue == 0 && $nowValue == 0) return 0;
        if ($lastValue == 0) return round($nowValue, 2);
        if ($nowValue == 0) return -round($lastValue, 2);
        return bcmul(bcdiv((bcsub($nowValue, $lastValue, 2)), $lastValue, 2), 100, 2);
    }

    public function homeStatics()
    {
        /** @var UserServices $uSercice */
        $uSercice = app()->make(UserServices::class);
        /** @var StoreProductLogServices $productLogServices */
        $productLogServices = app()->make(StoreProductLogServices::class);
        //TODO 销售额
        //今日销售额
        $today_sales = $this->dao->todaySales('today');
        //昨日销售额
        $yesterday_sales = $this->dao->todaySales('yesterday');
        //日同比
        $sales_today_ratio = $this->growth($today_sales, $yesterday_sales);
        //周销售额
        //本周
        $this_week_sales = $this->dao->thisWeekSales('week');
        //上周
        $last_week_sales = $this->dao->thisWeekSales('last week');
        //周同比
        $sales_week_ratio = $this->growth($this_week_sales, $last_week_sales);
        //总销售额
        $total_sales = $this->dao->totalSales('month');
        $sales = [
            'today' => $today_sales,
            'yesterday' => $yesterday_sales,
            'today_ratio' => $sales_today_ratio,
            'week' => $this_week_sales,
            'last_week' => $last_week_sales,
            'week_ratio' => $sales_week_ratio,
            'total' => $total_sales . '元',
            'date' => '昨日'
        ];
        //TODO:用户访问量
        //今日访问量
        $today_visits = $productLogServices->count(['time' => 'today', 'type' => 'visit']);
        //昨日访问量
        $yesterday_visits = $productLogServices->count(['time' => 'yesterday', 'type' => 'visit']);
        //日同比
        $visits_today_ratio = $this->growth($today_visits, $yesterday_visits);
        //本周访问量
        $this_week_visits = $productLogServices->count(['time' => 'week', 'type' => 'visit']);
        //上周访问量
        $last_week_visits = $productLogServices->count(['time' => 'last week', 'type' => 'visit']);
        //周同比
        $visits_week_ratio = $this->growth($this_week_visits, $last_week_visits);
        //总访问量
        $total_visits = $productLogServices->count(['time' => 'month']);
        $visits = [
            'today' => $today_visits,
            'yesterday' => $yesterday_visits,
            'today_ratio' => $visits_today_ratio,
            'week' => $this_week_visits,
            'last_week' => $last_week_visits,
            'week_ratio' => $visits_week_ratio,
            'total' => $total_visits . 'Pv',
            'date' => '昨日'
        ];
        //TODO 订单量
        //今日订单量
        $today_order = $this->dao->todayOrderVisit('today', 1);
        //昨日订单量
        $yesterday_order = $this->dao->todayOrderVisit('yesterday', 1);
        //订单日同比
        $order_today_ratio = $this->growth($today_order, $yesterday_order);
        //本周订单量
        $this_week_order = $this->dao->todayOrderVisit('week', 2);
        //上周订单量
        $last_week_order = $this->dao->todayOrderVisit('last week', 2);
        //订单周同比
        $order_week_ratio = $this->growth($this_week_order, $last_week_order);
        //总订单量
        $total_order = $this->dao->count(['time' => 'month']);
        $order = [
            'today' => $today_order,
            'yesterday' => $yesterday_order,
            'today_ratio' => $order_today_ratio,
            'week' => $this_week_order,
            'last_week' => $last_week_order,
            'week_ratio' => $order_week_ratio,
            'total' => $total_order . '单',
            'date' => '昨日'
        ];
        //TODO 用户
        //今日新增用户
        $today_user = $uSercice->todayAddVisits('today', 1);
        //昨日新增用户
        $yesterday_user = $uSercice->todayAddVisits('yesterday', 1);
        //新增用户日同比
        $user_today_ratio = $this->growth($today_user, $yesterday_user);
        //本周新增用户
        $this_week_user = $uSercice->todayAddVisits('week', 2);
        //上周新增用户
        $last_week_user = $uSercice->todayAddVisits('last week', 2);
        //新增用户周同比
        $user_week_ratio = $this->growth($this_week_user, $last_week_user);
        //所有用户
        $total_user = $uSercice->count(['time' => 'month']);
        $user = [
            'today' => $today_user,
            'yesterday' => $yesterday_user,
            'today_ratio' => $user_today_ratio,
            'week' => $this_week_user,
            'last_week' => $last_week_user,
            'week_ratio' => $user_week_ratio,
            'total' => $total_user . '人',
            'date' => '昨日'
        ];
        $info = array_values(compact('sales', 'visits', 'order', 'user'));
        $info[0]['title'] = '销售额';
        $info[1]['title'] = '用户访问量';
        $info[2]['title'] = '订单量';
        $info[3]['title'] = '新增用户';
        $info[0]['total_name'] = '本月销售额';
        $info[1]['total_name'] = '本月访问量';
        $info[2]['total_name'] = '本月订单量';
        $info[3]['total_name'] = '本月新增用户';
        return $info;
    }

    /**
     * 获取订单确认数据
     * @param array $user
     * @param $cartId
     * @return mixed
     */
    public function getOrderConfirmData(array $user, $cartId, bool $new)
    {
        /** @var StoreCartServices $cartServices */
        $cartServices = app()->make(StoreCartServices::class);
        $cartGroup = $cartServices->getUserProductCartListV1($user['uid'], $cartId, $new, 1);
        if (count($cartGroup['invalid'])) {
            throw new ValidateException($cartGroup['invalid'][0]['productInfo']['store_name'] . '已失效!');
        }
        if (!$cartGroup['valid']) {
            throw new ValidateException('请提交购买的商品');
        }
        $cartInfo = $cartGroup['valid'];
        /** @var UserAddressServices $addressServices */
        $addressServices = app()->make(UserAddressServices::class);
        $addr = $addressServices->getUserDefaultAddress($user['uid']);
        /** @var StoreOrderComputedServices $computedServices */
        $computedServices = app()->make(StoreOrderComputedServices::class);
        $priceGroup = $computedServices->getOrderPriceGroup($cartInfo, $addr);
        $other = [
            'offlinePostage' => sys_config('offline_postage'),
            'integralRatio' => sys_config('integral_ratio')
        ];
        $data['addressInfo'] = $addr;
        $data['cartInfo'] = $cartInfo;
        $data['priceGroup'] = $priceGroup;
        $data['orderKey'] = $this->cacheOrderInfo($user['uid'], $cartInfo, $priceGroup, $other);
        $data['offlinePostage'] = $other['offlinePostage'];

        $user['vip_id'] = 0;
        $user['discount'] = 0;
        $data['userInfo'] = $user;
        $data['integralRatio'] = $other['integralRatio'];
        $data['offline_pay_status'] = (int)sys_config('offline_pay_status') ?? (int)2;
        $data['yue_pay_status'] =  (int)sys_config('yue_pay_status') == 1 ? (int)1 : (int)2;//余额支付 1 开启 2 关闭
        $data['pay_weixin_open'] = (int)sys_config('pay_weixin_open') ?? 0;//微信支付 1 开启 0 关闭
        $data['system_store'] = [];//门店信息
        /** @var UserInvoiceServices $userInvoice */
        $userInvoice = app()->make(UserInvoiceServices::class);
        $invoice_func = $userInvoice->invoiceFuncStatus();
        $data['invoice_func'] = $invoice_func['invoice_func'];
        $data['special_invoice'] = $invoice_func['special_invoice'];
        return $data;
    }

    /**
     * 缓存订单信息
     * @param $uid
     * @param $cartInfo
     * @param $priceGroup
     * @param array $other
     * @param int $cacheTime
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function cacheOrderInfo($uid, $cartInfo, $priceGroup, $other = [], $cacheTime = 600)
    {
        $key = $this->getCacheKey();
        CacheService::redisHandler()->set('user_order_' . $uid . $key, compact('cartInfo', 'priceGroup', 'other'), $cacheTime);
        return $key;
    }

    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public function getCacheKey(string $prefix = '')
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
     * 获取订单缓存信息
     * @param int $uid
     * @param string $key
     * @return |null
     */
    public function getCacheOrderInfo(int $uid, string $key)
    {
        $cacheName = 'user_order_' . $uid . $key;
        if (!CacheService::redisHandler()->has($cacheName)) return null;
        return CacheService::redisHandler()->get($cacheName);
    }

    /**
     * 获取拼团的订单id
     * @param int $pid
     * @param int $uid
     * @return mixed
     */
    public function getStoreIdPink(int $pid, int $uid)
    {
        return $this->dao->value(['uid' => $uid, 'pink_id' => $pid, 'is_del' => 0], 'order_id');
    }

    /**
     * 判断当前订单中是否有拼团
     * @param int $pid
     * @param int $uid
     * @return int
     */
    public function getIsOrderPink($pid = 0, $uid = 0)
    {
        return $this->dao->count(['uid' => $uid, 'pink_id' => $pid, 'refund_status' => 0, 'is_del' => 0]);
    }

    /**
     * 判断支付方式是否开启
     * @param $payType
     * @return bool
     */
    public function checkPaytype(string $payType)
    {
        $res = false;
        switch ($payType) {
            case PayServices::WEIXIN_PAY:
                $res = sys_config('pay_weixin_open') ? true : false;
                break;
            case PayServices::YUE_PAY:
                $res = sys_config('yue_pay_status') == 1 ? true : false;
                break;
            case 'offline':
                $res = sys_config('offline_pay_status') == 1 ? true : false;
                break;
        }
        return $res;
    }


    /**
     * 修改支付方式为线下支付
     * @param string $orderId
     * @return bool
     */
    public function setOrderTypePayOffline(string $orderId)
    {
        return $this->dao->update($orderId, ['pay_type' => 'offline'], 'order_id');
    }

    /**
     * 删除订单
     * @param $uni
     * @param $uid
     * @return bool
     */
    public function removeOrder(string $uni, int $uid)
    {
        $order = $this->getUserOrderDetail($uni, $uid);
        if (!$order) {
            throw new ValidateException('订单不存在!');
        }
        $order = $this->tidyOrder($order);
        if ($order['_status']['_type'] != 0 && $order['_status']['_type'] != -2 && $order['_status']['_type'] != 4)
            throw new ValidateException('该订单无法删除!');

        $order->is_del = 1;
        /** @var StoreOrderStatusServices $statusService */
        $statusService = app()->make(StoreOrderStatusServices::class);
        $res = $statusService->save([
            'oid' => $order['id'],
            'change_type' => 'remove_order',
            'change_message' => '删除订单',
            'change_time' => time()
        ]);
        if ($order->save() && $res) {
            //未支付和已退款的状态下才可以退积分退库存退优惠券
            if ($order['_status']['_type'] == 0 || $order['_status']['_type'] == -2) {
                /** @var StoreOrderRefundServices $refundServices */
                $refundServices = app()->make(StoreOrderRefundServices::class);
                $this->transaction(function () use ($order, $refundServices) {
                    //回退积分和优惠卷
                    $res = $refundServices->integralAndCouponBack($order);
                    //回退库存
                    $res = $res && $refundServices->regressionStock($order);
                    if (!$res) {
                        throw new ValidateException('取消订单失败!');
                    }
                });

            }
            return true;
        } else
            throw new ValidateException('订单删除失败!');
    }

    /**
     * 取消订单
     * @param $order_id
     * @param $uid
     * @return bool|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function cancelOrder($order_id, int $uid)
    {
        $order = $this->dao->getOne(['order_id' => $order_id, 'uid' => $uid, 'is_del' => 0]);
        if (!$order) {
            throw new ValidateException('没有查到此订单');
        }
        if ($order->paid) {
            throw new ValidateException('订单已经支付无法取消');
        }
        /** @var StoreOrderCartInfoServices $cartServices */
        $cartServices = app()->make(StoreOrderCartInfoServices::class);
        $cartInfo = $cartServices->getOrderCartInfo($order['id']);
        /** @var StoreOrderRefundServices $refundServices */
        $refundServices = app()->make(StoreOrderRefundServices::class);

        $this->transaction(function () use ($refundServices, $order) {
            $res = $refundServices->integralAndCouponBack($order) && $refundServices->regressionStock($order);
            $order->is_del = 1;
            if (!($res && $order->save())) {
                throw new ValidateException('取消订单失败');
            }
        });
        return true;
    }

    /**
     * 判断订单完成
     * @param StoreProductReplyServices $replyServices
     * @param array $uniqueList
     * @param $oid
     * @return mixed
     */
    public function checkOrderOver($replyServices, array $uniqueList, $oid)
    {
        //订单商品全部评价完成
        $replyServices->count(['unique' => $uniqueList, 'oid' => $oid]);
        if ($replyServices->count(['unique' => $uniqueList, 'oid' => $oid]) == count($uniqueList)) {
            event('StoreProductOrderOver', [$oid]);
            $res = $this->dao->update($oid, ['status' => '3']);
            if (!$res) throw new ValidateException('评价后置操作失败!');
            /** @var StoreOrderStatusServices $statusService */
            $statusService = app()->make(StoreOrderStatusServices::class);
            $statusService->save([
                'oid' => $oid,
                'change_type' => 'check_order_over',
                'change_message' => '用户评价',
                'change_time' => time()
            ]);
        }
    }

    /**
     * 某个用户订单
     * @param int $uid
     * @param UserServices $userServices
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserOrderList(int $uid)
    {
        /** @var UserServices $userServices */
        $userServices = app()->make(UserServices::class);
        $user = $userServices->getUserInfo($uid);
        if (!$user) {
            throw  new ValidateException('数据不存在');
        }
        [$page, $limit] = $this->getPageValue();
        $where = ['uid' => $uid, 'paid' => 1, 'refund_status' => 0, 'is_del' => 0, 'is_system_del' => 0];
        $list = $this->dao->getStairOrderList($where, 'order_id,real_name,total_num,total_price,pay_price,FROM_UNIXTIME(pay_time,"%Y-%m-%d") as pay_time,paid,pay_type', $page, $limit);
        $count = $this->dao->count(['uid' => $uid]);
        return compact('list', 'count');
    }


    /**
     * 订单导出
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExportList(array $where)
    {
        $list = $this->dao->search($where)->select()->toArray();
        foreach ($list as &$item) {
            /** @var StoreOrderCartInfoServices $orderCart */
            $orderCart = app()->make(StoreOrderCartInfoServices::class);
            $_info = $orderCart->getCartColunm(['oid' => $item['id']], 'cart_info', 'unique');
            foreach ($_info as $k => $v) {
                $cart_info = is_string($v) ? json_decode($v, true) : $v;
                if (!isset($cart_info['productInfo'])) $cart_info['productInfo'] = [];
                $_info[$k] = $cart_info;
                unset($cart_info);
            }
            $item['_info'] = $_info;
            /** @var WechatUserServices $wechatUserService */
            $wechatUserService = app()->make(WechatUserServices::class);
            $item['sex'] = $wechatUserService->value(['uid' => $item['uid']], 'sex');
            if ($item['shipping_type'] == 1) {
                $item['pink_name'] = '[普通订单]';
                $item['color'] = '#895612';
            } else if ($item['shipping_type'] == 2) {
                $item['pink_name'] = '[核销订单]';
                $item['color'] = '#8956E8';
            }
        }
        return $list;
    }

    /**
     * 自动取消订单
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderUnpaidCancel()
    {
        //系统预设取消订单时间段
        $keyValue = ['order_cancel_time', 'order_activity_time', 'order_bargain_time', 'order_seckill_time', 'order_pink_time'];
        //获取配置
        $systemValue = SystemConfigService::more($keyValue);
        //格式化数据
        $systemValue = Arr::setValeTime($keyValue, is_array($systemValue) ? $systemValue : []);
        $list = $this->dao->getOrderUnPaidList();
        /** @var StoreOrderRefundServices $refundServices */
        $refundServices = app()->make(StoreOrderRefundServices::class);
        foreach ($list as $order) {
            if ($order['pink_id'] || $order['combination_id']) {
                $secs = $systemValue['order_pink_time'] ? $systemValue['order_pink_time'] : $systemValue['order_activity_time'];
            } elseif ($order['seckill_id']) {
                $secs = $systemValue['order_seckill_time'] ? $systemValue['order_seckill_time'] : $systemValue['order_activity_time'];
            } elseif ($order['bargain_id']) {
                $secs = $systemValue['order_bargain_time'] ? $systemValue['order_bargain_time'] : $systemValue['order_activity_time'];
            } else {
                $secs = $systemValue['order_cancel_time'];
            }
            if ($secs == 0) return true;
            if (($order['add_time'] + bcmul($secs, '3600', 0)) < time()) {
                try {
                    $this->transaction(function () use ($order, $refundServices) {
                        //回退积分和优惠卷
                        $res = $refundServices->integralAndCouponBack($order);
                        //回退库存和销量
                        $res = $res && $refundServices->regressionStock($order);
                        //修改订单状态
                        $res = $res && $this->dao->update($order['id'], ['is_del' => 1, 'mark' => '订单未支付已超过系统预设时间']);
                        if (!$res) {
                            throw new ValidateException('订单号' . $order['order_id'] . '自动取消订单失败');
                        }
                        return true;
                    });

                    /** @var StoreOrderCartInfoServices $cartServices */
                    $cartServices = app()->make(StoreOrderCartInfoServices::class);
                    $cartInfo = $cartServices->getOrderCartInfo((int)$order['id']);

                } catch (\Throwable $e) {
                    Log::error('自动取消订单失败,失败原因:' . $e->getMessage(), $e->getTrace());
                }
            }
        }
    }

    /**
     * 根据时间获取当天或昨天订单营业额
     * @param array $where
     * @return float|int
     */
    public function getOrderMoneyByWhere(array $where, string $sum_field, string $selectType, string $group = "")
    {
        switch ($selectType) {
            case "sum" :
                return $this->dao->getDayTotalMoney($where, $sum_field);
            case "group" :
                return $this->dao->getDayGroupMoney($where, $sum_field, $group);
        }
    }

    /**
     * 统计时间段订单数
     * @param array $where
     * @param string $sum_field
     */
    public function getOrderCountByWhere(array $where)
    {
        return $this->dao->getDayOrderCount($where);
    }

    /**
     * 分组统计时间段订单数
     * @param $where
     * @return mixed
     */
    public function getOrderGroupCountByWhere($where)
    {
        return $this->dao->getOrderGroupCount($where);
    }

    /** 时间段支付订单人数
     * @param $where
     * @return mixed
     */
    public function getPayOrderPeopleByWhere($where)
    {
        return $this->dao->getPayOrderPeople($where);
    }

    /**时间段分组统计支付订单人数
     * @param $where
     * @return mixed
     */
    public function getPayOrderGroupPeopleByWhere($where)
    {
        return $this->dao->getPayOrderGroupPeople($where);
    }
}
