<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\system;

use app\admin\model\order\StoreOrderCartInfo;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\facade\Route as Url;
use app\models\store\StoreCart;
use app\admin\model\wechat\WechatUser;
use app\admin\model\store\StoreProduct;
use app\models\routine\RoutineTemplate;
use app\admin\model\user\{
    User, UserBill
};
use app\admin\model\ump\{
    StoreCouponUser, StorePink
};
use crmeb\services\{
    PHPExcelService, WechatTemplateService
};

/**
 * 订单管理Model
 * Class StoreOrder
 * @package app\admin\model\store
 */
class SystemVerifyOrder extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_order';

    use ModelTrait;

    public static function orderCount()
    {
        $data['wz'] = self::statusByWhere(0, new self())->where(['is_system_del' => 0])->count();
        $data['wf'] = self::statusByWhere(1, new self())->where(['is_system_del' => 0, 'shipping_type' => 1])->count();
        $data['ds'] = self::statusByWhere(2, new self())->where(['is_system_del' => 0, 'shipping_type' => 1])->count();
        $data['dp'] = self::statusByWhere(3, new self())->where(['is_system_del' => 0])->count();
        $data['jy'] = self::statusByWhere(4, new self())->where(['is_system_del' => 0])->count();
        $data['tk'] = self::statusByWhere(-1, new self())->where(['is_system_del' => 0])->count();
        $data['yt'] = self::statusByWhere(-2, new self())->where(['is_system_del' => 0])->count();
        $data['del'] = self::statusByWhere(-4, new self())->where(['is_system_del' => 0])->count();
        $data['write_off'] = self::statusByWhere(5, new self())->where(['is_system_del' => 0])->count();
        $data['general'] = self::where(['pink_id' => 0, 'combination_id' => 0, 'seckill_id' => 0, 'bargain_id' => 0, 'is_system_del' => 0])->count();
        $data['pink'] = self::where('pink_id|combination_id', '>', 0)->where('is_system_del', 0)->count();
        $data['seckill'] = self::where('seckill_id', '>', 0)->where('is_system_del', 0)->count();
        $data['bargain'] = self::where('bargain_id', '>', 0)->where('is_system_del', 0)->count();
        return $data;
    }

    /**
     * 核销订单列表
     * @param $where
     * @return array
     */
    public static function OrderList($where)
    {
        $model = self::getOrderWhere($where, self::alias('a')
            ->join('user r', 'r.uid=a.uid', 'LEFT'), 'a.', 'r')
            ->field('a.*,r.nickname,r.phone,r.spread_uid')->order('a.add_time desc,id desc');

        $data = ($data = $model->page((int)$where['page'], (int)$where['limit'])->select()) && count($data) ? $data->toArray() : [];
        foreach ($data as &$item) {
            $_info = StoreOrderCartInfo::where('oid', $item['id'])->field('cart_info')->select();
            $_info = count($_info) ? $_info->toArray() : [];
            foreach ($_info as $k => $v) {
                $cart_info = json_decode($v['cart_info'], true);
                if (!isset($cart_info['productInfo'])) $cart_info['productInfo'] = [];
                $_info[$k]['cart_info'] = $cart_info;
                unset($cart_info);
            }
            $item['_info'] = $_info;
            $item['spread_nickname'] = User::where('uid', $item['spread_uid'])->value('nickname');
            $item['add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '';
            $item['back_integral'] = $item['back_integral'] ?: 0;

            if ($item['paid'] == 1) {
                switch ($item['pay_type']) {
                    case 'weixin':
                        $item['pay_type_name'] = '微信支付';
                        break;
                    case 'yue':
                        $item['pay_type_name'] = '余额支付';
                        break;
                    case 'offline':
                        $item['pay_type_name'] = '线下支付';
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
            if ($item['paid'] == 0 && $item['status'] == 0) {
                $item['status_name'] = '未支付';
            } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                $item['status_name'] = '未发货';
            } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                $item['status_name'] = '未核销';
            } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                $item['status_name'] = '待收货';
            } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                $item['status_name'] = '未核销';
            } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                $item['status_name'] = '待评价';
            } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                $item['status_name'] = '已完成';
            }
            if ($item['clerk_id']) {
                $item['clerk_name'] = SystemStoreStaff::where('a.id', $item['clerk_id'])->alias('a')->join('user u', 'u.uid = a.uid')->value('u.nickname');
            } else {
                $item['clerk_name'] = '总平台';
            }
            if ($item['store_id']) {
                $item['store_name'] = SystemStore::where('id', $item['store_id'])->value('name');
            } else {
                $item['store_name'] = '';
            }
        }
        $count = self::getOrderWhere($where, self::alias('a')->join('user r', 'r.uid=a.uid', 'LEFT'), 'a.', 'r')->count();
        return compact('count', 'data');
    }

    /**
     * 处理where条件
     * @param $where
     * @param $model
     * @return mixed
     */
    public static function getOrderWhere($where, $model, $aler = '', $join = '')
    {
        $model = $model->where($aler . 'is_system_del', 0)
            ->where($aler . 'shipping_type', 2)
            ->where($aler . 'paid', 1)
            ->where($aler . 'status', 2);

        if (isset($where['real_name']) && $where['real_name'] != '') {
            $model = $model->where($aler . 'order_id|' . $aler . 'real_name|' . $aler . 'user_phone' . ($join ? '|' . $join . '.nickname|' . $join . '.uid|' . $join . '.phone' : ''), 'LIKE', "%$where[real_name]%");
        }
        if (isset($where['data']) && $where['data'] !== '') {
            $model = self::getModelTime($where, $model, $aler . 'add_time');
        }
        if (isset($where['store_id']) && $where['store_id'] !== '') {
            $model = $model->where($aler . 'store_id', $where['store_id']);
        }
        return $model;
    }

    public static function getBadge($where)
    {
        $price = self::getOrderPrice($where);
        return [
            [
                'name' => '订单数量',
                'field' => '件',
                'count' => $price['count_sum'],
                'background_color' => 'layui-bg-blue',
                'col' => 2
            ],

            [
                'name' => '订单金额',
                'field' => '元',
                'count' => $price['pay_price'],
                'background_color' => 'layui-bg-blue',
                'col' => 2
            ],
            [
                'name' => '退款金额',
                'field' => '元',
                'count' => $price['refund_price'],
                'background_color' => 'layui-bg-blue',
                'col' => 2
            ],
            [
                'name' => '退款订单数',
                'field' => '个',
                'count' => $price['refund_sum'],
                'background_color' => 'layui-bg-blue',
                'col' => 2
            ],
        ];
    }

    public static function statusByWhere($status, $model = null, $alert = '')
    {
        if ($model == null) $model = new self;
        if ('' === $status)
            return $model;
        else if ($status == 8)
            return $model;
        else if ($status == 0)//未支付
            return $model->where($alert . 'paid', 0)->where($alert . 'status', 0)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == 1)//已支付 未发货
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 0)->where($alert . 'shipping_type', 1)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == 2)//已支付  待收货
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 1)->where($alert . 'shipping_type', 1)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == 5)//已支付  待核销
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 0)->where($alert . 'shipping_type', 2)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == 3)// 已支付  已收货  待评价
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 2)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == 4)// 交易完成
            return $model->where($alert . 'paid', 1)->where($alert . 'status', 3)->where($alert . 'refund_status', 0)->where($alert . 'is_del', 0);
        else if ($status == -1)//退款中
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 1)->where($alert . 'is_del', 0);
        else if ($status == -2)//已退款
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 2)->where($alert . 'is_del', 0);
        else if ($status == -3)//退款
            return $model->where($alert . 'paid', 1)->where($alert . 'refund_status', 'in', '1,2')->where($alert . 'is_del', 0);
        else if ($status == -4)//已删除
            return $model->where($alert . 'is_del', 1);
        else
            return $model;
    }

    /**
     * 订单数量 支付方式
     * @return array
     */
    public static function payTypeCount()
    {
        $where['status'] = 8;
        $where['is_del'] = 0;
        $where['real_name'] = '';
        $where['data'] = '';
        $where['type'] = '';
        $where['order'] = '';
        $where['pay_type'] = 1;
        $weixin = self::getOrderWhere($where, new self)->count();
        $where['pay_type'] = 2;
        $yue = self::getOrderWhere($where, new self)->count();
        $where['pay_type'] = 3;
        $offline = self::getOrderWhere($where, new self)->count();
        return compact('weixin', 'yue', 'offline');
    }

    /**
     * 处理订单金额
     * @param $where
     * @return array
     */
    public static function getOrderPrice($where)
    {
        $where['is_del'] = 0;//删除订单不统计
        $model = new self;
        $price = [];
        $price['pay_price'] = 0;//支付金额
        $price['refund_price'] = 0;//退款金额
        $price['count_sum'] = 0; //核销订单数
        $price['refund_sum'] = 0;//退款订单数

        $whereData = ['is_del' => 0];
        if ($where['status'] == '') {
            $whereData['paid'] = 1;
            $whereData['refund_status'] = 0;
        }

        $price['refund_price'] = self::getOrderWhere($where, $model)->where(['is_del' => 0, 'paid' => 1, 'refund_status' => 2])->sum('refund_price');
        $price['refund_sum'] = self::getOrderWhere($where, $model)->where(['is_del' => 0, 'paid' => 1, 'refund_status' => 2])->count();
        $sumNumber = self::getOrderWhere($where, $model)->where($whereData)->field([
            'count(id) as count_sum',
            'sum(pay_price) as sum_pay_price',
        ])->find();
        if ($sumNumber) {
            $price['count_sum'] = $sumNumber['count_sum'];
            $price['pay_price'] = $sumNumber['sum_pay_price'];
        }
        return $price;
    }

}