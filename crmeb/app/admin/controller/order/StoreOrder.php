<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\controller\order;

use app\admin\controller\AuthController;
use app\admin\model\order\StoreOrderCartInfo;
use app\admin\model\system\Express;
use crmeb\repositories\OrderRepository;
use crmeb\repositories\ShortLetterRepositories;
use crmeb\services\{
    ExpressService,
    JsonService,
    MiniProgramService,
    WechatService,
    FormBuilder as Form,
    CacheService,
    UtilService as Util,
    JsonService as Json
};
use app\admin\model\order\StoreOrderStatus;
use app\admin\model\ump\StorePink;
use app\admin\model\user\{
    User, UserBill
};
use crmeb\basic\BaseModel;
use think\facade\Route as Url;
use app\admin\model\order\StoreOrder as StoreOrderModel;
use crmeb\services\YLYService;
use think\facade\Log;

/**
 * 订单管理控制器 同一个订单表放在一个控制器
 * Class StoreOrder
 * @package app\admin\controller\store
 */
class StoreOrder extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'year' => get_month(),
            'real_name' => $this->request->get('real_name', ''),
            'status' => $this->request->param('status', ''),
            'orderCount' => StoreOrderModel::orderCount(),
            'payTypeCount' => StoreOrderModel::payTypeCount(),
        ]);
        return $this->fetch();
    }

    /**
     * 获取头部订单金额等信息
     * return json
     */
    public function getBadge()
    {
        $where = Util::postMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['type', ''],
            ['pay_type', ''],
            ['order', '']
        ]);
        return Json::successful(StoreOrderModel::getBadge($where));
    }

    /**
     * 获取订单列表
     * return json
     */
    public function order_list()
    {
        $where = Util::getMore([
            ['status', ''],
            ['real_name', $this->request->param('real_name', '')],
            ['is_del', 0],
            ['data', ''],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['page', 1],
            ['limit', 20],
            ['excel', 0]
        ]);
        return Json::successlayui(StoreOrderModel::OrderList($where));
    }

    public function order_print($id = '')
    {
        if (!$id) {
            return JsonService::fail('缺少参数');
        }
        $order = StoreOrderModel::get($id);
        if (!$order) {
            return JsonService::fail('订单没有查到,无法打印!');
        }
        try {
            $order['cart_id'] = is_string($order['cart_id']) ? json_decode($order['cart_id'], true) : $order['cart_id'];
            $cartInfo = StoreOrderCartInfo::whereIn('cart_id', $order['cart_id'])->field('cart_info')->select();
            $cartInfo = count($cartInfo) ? $cartInfo->toArray() : [];
            $product = [];
            foreach ($cartInfo as $item) {
                $value = is_string($item['cart_info']) ? json_decode($item['cart_info'], true) : $item['cart_info'];
                $value['productInfo']['store_name'] = $value['productInfo']['store_name'] ?? "";
                $value['productInfo']['store_name'] = StoreOrderCartInfo::getSubstrUTf8($value['productInfo']['store_name'], 10, 'UTF-8', '');
                $product[] = $value;
            }
            if(!$product){
                return JsonService::fail('订单商品获取失败,无法打印!');
            }
            $res = YLYService::instance()->setContent(sys_config('site_name'), is_object($order) ? $order->toArray() : $order, $product)->orderPrinting();
            if ($res) {
                return JsonService::successful('打印成功');
            } else {
                return JsonService::fail('打印失败');
            }
        } catch (\Exception $e) {
            Log::error('小票打印出现错误,错误原因：' . $e->getMessage());
            return JsonService::fail($e->getMessage());
        }
    }

    /**
     * 核销码核销
     * @param string $verify_code
     * @return html
     */
    public function write_order($verify_code = '', $is_confirm = 0)
    {
        if ($this->request->isAjax()) {
            if (!$verify_code) return Json::fail('缺少核销码！');
            StoreOrderModel::beginTrans();
            $orderInfo = StoreOrderModel::where('verify_code', $verify_code)->where('paid', 1)->where('refund_status', 0)->find();
            if (!$orderInfo) return Json::fail('核销订单不存在！');
            if ($orderInfo->status > 0) return Json::fail('订单已核销！');
            if ($orderInfo->combination_id && $orderInfo->pink_id) {
                $res = StorePink::where('id', $orderInfo->pink_id)->where('status', '<>', 2)->count();
                if ($res) return Json::fail('拼团订单暂未成功无法核销！');
            }
            if ($is_confirm == 0) {
                $orderInfo['nickname'] = User::where(['uid' => $orderInfo['uid']])->value('nickname');
                return Json::successful($orderInfo);
            }
            $orderInfo->status = 2;
            if ($orderInfo->save()) {
                OrderRepository::storeProductOrderTakeDeliveryAdmin($orderInfo);
                StoreOrderStatus::setStatus($orderInfo->id, 'take_delivery', '已核销');
                //发送短信
                event('ShortMssageSend', [$orderInfo['order_id'], 'Receiving']);
                StoreOrderModel::commitTrans();
                return Json::successful('核销成功！');
            } else {
                StoreOrderModel::rollbackTrans();
                return Json::fail('核销失败');
            }
        } else
            $this->assign('is_layui', 1);
        return $this->fetch();
    }

    public function orderchart()
    {
        $where = Util::getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['combination_id', ''],
            ['export', 0],
            ['order', 'id desc']
        ], $this->request);
        $limitTimeList = [
            'today' => implode(' - ', [date('Y/m/d'), date('Y/m/d', strtotime('+1 day'))]),
            'week' => implode(' - ', [
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month' => implode(' - ', [date('Y/m') . '/01', date('Y/m') . '/' . date('t')]),
            'quarter' => implode(' - ', [
                date('Y') . '/' . (ceil((date('n')) / 3) * 3 - 3 + 1) . '/01',
                date('Y') . '/' . (ceil((date('n')) / 3) * 3) . '/' . date('t', mktime(0, 0, 0, (ceil((date('n')) / 3) * 3), 1, date('Y')))
            ]),
            'year' => implode(' - ', [
                date('Y') . '/01/01', date('Y/m/d', strtotime(date('Y') . '/01/01 + 1year -1 day'))
            ])
        ];
        if ($where['data'] == '') $where['data'] = $limitTimeList['today'];
        $orderCount = [
            urlencode('未支付') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(0))->count(),
            urlencode('未发货') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(1))->count(),
            urlencode('待收货') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(2))->count(),
            urlencode('待评价') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(3))->count(),
            urlencode('交易完成') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(4))->count(),
            urlencode('退款中') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(-1))->count(),
            urlencode('已退款') => StoreOrderModel::getOrderWhere($where, StoreOrderModel::statusByWhere(-2))->count()
        ];
        $model = StoreOrderModel::getOrderWhere($where, new StoreOrderModel())->field('sum(total_num) total_num,count(*) count,sum(total_price) total_price,sum(refund_price) refund_price,from_unixtime(add_time,\'%Y-%m-%d\') add_time')
            ->group('from_unixtime(add_time,\'%Y-%m-%d\')');
        $orderPrice = $model->select()->toArray();
        $orderDays = [];
        $orderCategory = [
            ['name' => '商品数', 'type' => 'line', 'data' => []],
            ['name' => '订单数', 'type' => 'line', 'data' => []],
            ['name' => '订单金额', 'type' => 'line', 'data' => []],
            ['name' => '退款金额', 'type' => 'line', 'data' => []]
        ];
        foreach ($orderPrice as $price) {
            $orderDays[] = $price['add_time'];
            $orderCategory[0]['data'][] = $price['total_num'];
            $orderCategory[1]['data'][] = $price['count'];
            $orderCategory[2]['data'][] = $price['total_price'];
            $orderCategory[3]['data'][] = $price['refund_price'];
        }
        $this->assign(StoreOrderModel::systemPage($where, $this->adminId));
        $this->assign('price', StoreOrderModel::getOrderPrice($where));
        $this->assign(compact('limitTimeList', 'where', 'orderCount', 'orderPrice', 'orderDays', 'orderCategory'));
        return $this->fetch();
    }

    /**
     * 修改支付金额等
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('order_id', '订单编号', $product->getData('order_id'))->disabled(1);
        $f[] = Form::number('total_price', '商品总价', $product->getData('total_price'))->min(0);
        $f[] = Form::number('total_postage', '原始邮费', $product->getData('total_postage'))->min(0);
        $f[] = Form::number('pay_price', '实际支付金额', $product->getData('pay_price'))->min(0);
        $f[] = Form::number('pay_postage', '实际支付邮费', $product->getData('pay_postage'));
        $f[] = Form::number('gain_integral', '赠送积分', $product->getData('gain_integral'));
//        $f[] = Form::radio('status','状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]]);
        $form = Form::make_post_form('修改订单', $f, Url::buildUrl('update', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改订单提交更新
     * @param $id
     */
    public function update($id)
    {
        $data = Util::postMore([
            'order_id',
            'total_price',
            'total_postage',
            'pay_price',
            'pay_postage',
            'gain_integral',
        ]);
        if ($data['total_price'] <= 0) return Json::fail('请输入商品总价');
        if ($data['pay_price'] <= 0) return Json::fail('请输入实际支付金额');
        $orderInfo = StoreOrderModel::get($id);
        if (!$orderInfo) {
            return Json::fail('订单不存在');
        }
        $orderInfo->order_id = StoreOrderModel::changeOrderId($data['order_id']);
        $pay_price = $orderInfo->pay_price;
        $orderInfo->pay_price = $data['pay_price'];
        $orderInfo->total_price = $data['total_price'];
        $orderInfo->total_postage = $data['total_postage'];
        $orderInfo->pay_postage = $data['pay_postage'];
        $orderInfo->gain_integral = $data['gain_integral'];
        if ($orderInfo->save()) {
            //改价短信提醒
            if ($data['pay_price'] != $pay_price) {
                $switch = sys_config('price_revision_switch') ? true : false;
                ShortLetterRepositories::send($switch, $orderInfo->user_phone, ['order_id' => $orderInfo->order_id, 'pay_price' => $orderInfo->pay_price], 'PRICE_REVISION_CODE');
            }
            event('StoreProductOrderEditAfter', [$data, $id]);
            StoreOrderStatus::setStatus($id, 'order_edit', '修改商品总价为：' . $data['total_price'] . ' 实际支付金额' . $data['pay_price']);
            return Json::successful('修改成功!');
        } else {
            return Json::fail('订单修改失败');
        }
    }

    /*
     * 发送货
     * @param int $id
     * @return html
     * */
    public function order_goods($id = 0)
    {
        $list = Express::where('is_show', 1)->order('sort desc')->column('name', 'id');
        $this->assign([
            'list' => $list,
            'id' => $id
        ]);
        return $this->fetch();
    }

    /*
     * 删除订单
     * */
    public function del_order()
    {
        $ids = Util::postMore(['ids'])['ids'];
        if (!count($ids)) return Json::fail('请选择需要删除的订单');
        if (StoreOrderModel::where('is_del', 0)->where('id', 'in', $ids)->count())
            return Json::fail('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单');
        $res = StoreOrderModel::where('id', 'in', $ids)->update(['is_system_del' => 1]);
        if ($res)
            return Json::successful('删除成功');
        else
            return Json::fail('删除失败');
    }

    /**
     * TODO 送货信息提交
     * @param Request $request
     * @param $id
     */
    public function update_delivery($id = 0)
    {
        $data = Util::postMore([
            ['type', 1],
            ['delivery_name', ''],
            ['delivery_id', ''],
            ['sh_delivery_name', ''],
            ['sh_delivery_id', ''],
        ], $this->request);
        switch ((int)$data['type']) {
            case 1:
                //发货
                $data['delivery_type'] = 'express';
                if (!$data['delivery_name']) return Json::fail('请选择快递公司');
                if (!$data['delivery_id']) return Json::fail('请输入快递单号');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryGoodsAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_goods', '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']);
                break;
            case 2:
                //送货
                $data['delivery_type'] = 'send';
                $data['delivery_name'] = $data['sh_delivery_name'];
                $data['delivery_id'] = $data['sh_delivery_id'];
                unset($data['sh_delivery_name'], $data['sh_delivery_id']);
                if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
                if (!(int)$data['delivery_id']) return Json::fail('请输入送货人电话号码');
                else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery', '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']);
                break;
            case 3:
                //虚拟发货
                $data['delivery_type'] = 'fictitious';
                $data['status'] = 1;
                StoreOrderModel::edit($data, $id);
                event('StoreProductOrderDeliveryAfter', [$data, $id]);
                StoreOrderStatus::setStatus($id, 'delivery_fictitious', '已虚拟发货');
                break;
            default:
                return Json::fail('暂时不支持其他发货类型');
                break;
        }
        //短信发送
        event('ShortMssageSend', [StoreOrderModel::where('id', $id)->value('order_id'), 'Deliver']);
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写送货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function delivery($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1 && $product['status'] == 0) {
            $f = [];
            $f[] = Form::input('delivery_name', '送货人姓名')->required('送货人姓名不能为空', 'required:true;');
            $f[] = Form::input('delivery_id', '送货人电话')->required('请输入正确电话号码', 'telephone');
            $form = Form::make_post_form('修改订单', $f, Url::buildUrl('updateDelivery', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 送货信息提交
     * @param $id
     */
    public function updateDelivery($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'send';
        if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
        if (!(int)$data['delivery_id']) return Json::fail('请输入送货人电话号码');
        else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
        $data['status'] = 1;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDeliveryAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'delivery', '已配送 发货人：' . $data['delivery_name'] . ' 发货人电话：' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * TODO 填写发货信息
     * @param $id
     * @return mixed|void
     * @throws \think\exception\DbException
     */
    public function deliver_goods($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1 && $product['status'] == 0) {
            $f = [];
            $f[] = Form::select('delivery_name', '快递公司')->setOptions(function () {
                $list = Express::where('is_show', 1)->order('sort DESC')->column('name', 'id');
                $menus = [];
                foreach ($list as $k => $v) {
                    $menus[] = ['value' => $v, 'label' => $v];
                }
                return $menus;
            })->filterable(1);
            $f[] = Form::input('delivery_id', '快递单号');
            $form = Form::make_post_form('修改订单', $f, Url::buildUrl('updateDeliveryGoods', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else return $this->failedNotice('订单状态错误');
    }

    /**
     * TODO 发货信息提交
     * @param $id
     */
    public function updateDeliveryGoods($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        $data['delivery_type'] = 'express';
        if (!$data['delivery_name']) return Json::fail('请选择快递公司');
        if (!$data['delivery_id']) return Json::fail('请输入快递单号');
        $data['status'] = 1;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDeliveryGoodsAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'delivery_goods', '已发货 快递公司：' . $data['delivery_name'] . ' 快递单号：' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * 修改状态为已收货
     * @param $id
     * @return \think\response\Json|void
     */
    public function take_delivery($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $order = StoreOrderModel::get($id);
        if (!$order) return Json::fail('数据不存在!');
        if ($order['status'] == 2) return Json::fail('不能重复收货!');
        if ($order['paid'] == 1 && $order['status'] == 1) $data['status'] = 2;
        else if ($order['pay_type'] == 'offline') $data['status'] = 2;
        else return Json::fail('请先发货或者送货!');
        StoreOrderModel::beginTrans();
        try {
            if (!StoreOrderModel::edit($data, $id)) {
                StoreOrderModel::rollbackTrans();
                return Json::fail(StoreOrderModel::getErrorInfo('收货失败,请稍候再试!'));
            } else {
                OrderRepository::storeProductOrderTakeDeliveryAdmin($order, $id);
                StoreOrderStatus::setStatus($id, 'take_delivery', '已收货');
                StoreOrderModel::commitTrans();
                //发送短信
                event('ShortMssageSend', [$order['order_id'], 'Receiving']);
                return Json::successful('收货成功!');
            }
        } catch (\Exception $e) {
            StoreOrderModel::rollbackTrans();
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 修改退款状态
     * @param $id
     * @return \think\response\Json|void
     */
    public function refund_y($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1) {
            $f = [];
            $f[] = Form::input('order_id', '退款单号', $product->getData('order_id'))->disabled(1);
            $f[] = Form::number('refund_price', '退款金额', $product->getData('pay_price'))->precision(2)->min(0.01);
            $f[] = Form::radio('type', '状态', 1)->options([['label' => '直接退款', 'value' => 1], ['label' => '退款后,返回原状态', 'value' => 2]]);
            $form = Form::make_post_form('退款处理', $f, Url::buildUrl('updateRefundY', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else return Json::fail('数据不存在!');
    }

    /**
     * 退款处理
     * @param $id
     */
    public function updateRefundY($id)
    {
        $data = Util::postMore([
            'refund_price',
            ['type', 1],
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['pay_price'] == $product['refund_price']) return Json::fail('已退完支付金额!不能再退款了');
        if (!$data['refund_price']) return Json::fail('请输入退款金额');
        $refund_price = $data['refund_price'];
        $data['refund_price'] = bcadd($data['refund_price'], $product['refund_price'], 2);
        $bj = bccomp((float)$product['pay_price'], (float)$data['refund_price'], 2);
        if ($bj < 0) return Json::fail('退款金额大于支付金额，请修改退款金额');
        if ($data['type'] == 1) {
            $data['refund_status'] = 2;
        } else if ($data['type'] == 2) {
            $data['refund_status'] = 0;
        }
        $type = $data['type'];
        unset($data['type']);
        $refund_data['pay_price'] = $product['pay_price'];
        $refund_data['refund_price'] = $refund_price;
        if ($product['pay_type'] == 'weixin') {
            if ($product['is_channel'] == 1) {//小程序
                try {
                    MiniProgramService::payOrderRefund($product['order_id'], $refund_data);//2.5.36
                } catch (\Exception $e) {
                    return Json::fail($e->getMessage());
                }
            } else {//TODO 公众号
                try {
                    WechatService::payOrderRefund($product['order_id'], $refund_data);
                } catch (\Exception $e) {
                    return Json::fail($e->getMessage());
                }
            }
        } else if ($product['pay_type'] == 'yue') {
            BaseModel::beginTrans();
            $usermoney = User::where('uid', $product['uid'])->value('now_money');
            $res1 = User::bcInc($product['uid'], 'now_money', $refund_price, 'uid');
            $res2 = $res2 = UserBill::income('商品退款', $product['uid'], 'now_money', 'pay_product_refund', $refund_price, $product['id'], bcadd($usermoney, $refund_price, 2), '订单退款到余额' . floatval($refund_price) . '元');
            try {
                OrderRepository::storeOrderYueRefund($product, $refund_data);
            } catch (\Exception $e) {
                BaseModel::rollbackTrans();
                return Json::fail($e->getMessage());
            }
            $res = $res1 && $res2;
            BaseModel::checkTrans($res);
            if (!$res) return Json::fail('余额退款失败!');
        }
        $resEdit = StoreOrderModel::edit($data, $id);
        $res = true;
        if ($resEdit) {
            $data['type'] = $type;
            if ($data['type'] == 1) $res = StorePink::setRefundPink($id);
            if (!$res) return Json::fail('修改失败');
            try {
                OrderRepository::storeProductOrderRefundY($data, $id);
            } catch (\Exception $e) {
                BaseModel::rollbackTrans();
                return Json::fail($e->getMessage());
            }
            StoreOrderStatus::setStatus($id, 'refund_price', '退款给用户' . $refund_price . '元');

            //退佣金
            $brokerage_list = UserBill::where('category', 'now_money')
                ->where('type', 'brokerage')
                ->where('link_id', $id)
                ->where('pm', 1)
                ->select();

            if ($brokerage_list) {
                $brokerage_list = $brokerage_list->toArray();
                foreach ($brokerage_list as $item) {
                    $usermoney = User::where('uid', $item['uid'])->value('brokerage_price');
                    if ($item['number'] > $usermoney)
                        $item['number'] = $usermoney;
                    User::bcDec($item['uid'], 'brokerage_price', $item['number'], 'uid');
                    UserBill::expend('退款退佣金', $item['uid'], 'now_money', 'brokerage', $item['number'], $id, bcsub($usermoney, $item['number'], 2), '订单退款扣除佣金' . floatval($item['number']) . '元');
                }
            }

            //退款扣除用户积分
            //购买赠送的积分
            $bill_integral = UserBill::where('category', 'integral')
                ->where('type', 'gain')
                ->where('link_id', $id)
                ->where('pm', 1)
                ->find();
            if ($bill_integral) {
                $bill_integral = $bill_integral->toArray();
                //用户积分
                $user_integral = User::where('uid', $bill_integral['uid'])->value('integral');
                if ($bill_integral['number'] > $user_integral)
                    $bill_integral['number'] = $user_integral;
                User::bcDec($bill_integral['uid'], 'integral', $bill_integral['number'], 'uid');
                UserBill::expend('退款扣除积分', $bill_integral['uid'], 'integral', 'gain', $bill_integral['number'], $id, bcsub($user_integral, $bill_integral['number'], 2), '订单退款扣除积分' . floatval($bill_integral['number']) . '积分');
            }

            BaseModel::commitTrans();
            return Json::successful('修改成功!');
        } else {
            StoreOrderStatus::setStatus($id, 'refund_price', '退款给用户' . $refund_price . '元失败');
            return Json::fail('修改失败!');
        }
    }

    public function order_info($oid = '')
    {
        if (!$oid || !($orderInfo = StoreOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        $userInfo = User::getUserInfos($orderInfo['uid']);
        if ($userInfo['spread_uid']) {
            $spread = User::where('uid', $userInfo['spread_uid'])->value('nickname');
        } else {
            $spread = '';
        }
        $this->assign(compact('orderInfo', 'userInfo', 'spread'));
        return $this->fetch();
    }

    public function express($oid = '')
    {
        if (!$oid || !($order = StoreOrderModel::get($oid)))
            return $this->failed('订单不存在!');
        if ($order['delivery_type'] != 'express' || !$order['delivery_id']) return $this->failed('该订单不存在快递单号!');
        $cacheName = $order['order_id'] . $order['delivery_id'];
        $result = CacheService::get($cacheName, null);
        if ($result === null) {
            $result = ExpressService::query($order['delivery_id']);
            if (is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            CacheService::set($cacheName, $result, $cacheTime);
        }
        $this->assign([
            'order' => $order,
            'express' => $result
        ]);
        return $this->fetch();
    }

    /**
     * 修改配送信息
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function distribution($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f = [];
        $f[] = Form::input('order_id', '订单号', $product->getData('order_id'))->disabled(1);
        if ($product['delivery_type'] == 'send') {
            $f[] = Form::input('delivery_name', '送货人姓名', $product->getData('delivery_name'));
            $f[] = Form::input('delivery_id', '送货人电话', $product->getData('delivery_id'));
        } else if ($product['delivery_type'] == 'express') {
            $f[] = Form::select('delivery_name', '快递公司', $product->getData('delivery_name'))->setOptions(function () {
                $list = Express::where('is_show', 1)->column('name', 'id');
                $menus = [];
                foreach ($list as $k => $v) {
                    $menus[] = ['value' => $v, 'label' => $v];
                }
                return $menus;
            });
            $f[] = Form::input('delivery_id', '快递单号', $product->getData('delivery_id'));
        }
        $form = Form::make_post_form('配送信息', $f, Url::buildUrl('updateDistribution', array('id' => $id)), 7);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 修改配送信息
     * @param $id
     */
    public function updateDistribution($id)
    {
        $data = Util::postMore([
            'delivery_name',
            'delivery_id',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['delivery_type'] == 'send') {
            if (!$data['delivery_name']) return Json::fail('请输入送货人姓名');
            if (!(int)$data['delivery_id']) return Json::fail('请输入送货人电话号码');
            else if (!preg_match("/^1[3456789]{1}\d{9}$/", $data['delivery_id'])) return Json::fail('请输入正确的送货人电话号码');
        } else if ($product['delivery_type'] == 'express') {
            if (!$data['delivery_name']) return Json::fail('请选择快递公司');
            if (!$data['delivery_id']) return Json::fail('请输入快递单号');
        }
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderDistributionAfter', [$data, $id]);
        StoreOrderStatus::setStatus($id, 'distribution', '修改发货信息为' . $data['delivery_name'] . '号' . $data['delivery_id']);
        return Json::successful('修改成功!');
    }

    /**
     * 修改退款状态
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function refund_n($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        $f[] = Form::input('order_id', '订单号', $product->getData('order_id'))->disabled(1);
        $f[] = Form::input('refund_reason', '退款原因')->type('textarea');
        $form = Form::make_post_form('退款', $f, Url::buildUrl('updateRefundN', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 不退款原因
     * @param $id
     */
    public function updateRefundN($id)
    {
        $data = Util::postMore([
            'refund_reason',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if (!$data['refund_reason']) return Json::fail('请输入退款原因');
        $data['refund_status'] = 0;
        StoreOrderModel::edit($data, $id);
        event('StoreProductOrderRefundNAfter', [$data['refund_reason'], $id]);
        StoreOrderStatus::setStatus($id, 'refund_n', '不退款原因:' . $data['refund_reason']);
        return Json::successful('修改成功!');
    }

    /**
     * 立即支付
     * @param $id
     */
    public function offline($id)
    {
        $res = StoreOrderModel::updateOffline($id);
        if ($res) {
            event('StoreProductOrderOffline', [$id]);
            StoreOrderStatus::setStatus($id, 'offline', '线下付款');
            return Json::successful('修改成功!');
        } else {
            return Json::fail(StoreOrderModel::getErrorInfo('修改失败!'));
        }
    }

    /**
     * 修改积分和金额
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function integral_back($id)
    {
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($product['paid'] == 1) {
            $f[] = Form::input('order_id', '退款单号', $product->getData('order_id'))->disabled(1);
            $f[] = Form::number('use_integral', '使用的积分', $product->getData('use_integral'))->min(0)->disabled(1);
            $f[] = Form::number('use_integrals', '已退积分', $product->getData('back_integral'))->min(0)->disabled(1);
            $f[] = Form::number('back_integral', '可退积分', bcsub($product->getData('use_integral'), $product->getData('use_integral')))->min(0);
            $form = Form::make_post_form('退积分', $f, Url::buildUrl('updateIntegralBack', array('id' => $id)), 7);
            $this->assign(compact('form'));
            return $this->fetch('public/form-builder');
        } else {
            return Json::fail('参数错误!');
        }
        return $this->fetch('public/form-builder');
    }

    /**
     * 退积分保存
     * @param $id
     */
    public function updateIntegralBack($id)
    {
        $data = Util::postMore([
            'back_integral',
        ]);
        if (!$id) return $this->failed('数据不存在');
        $product = StoreOrderModel::get($id);
        if (!$product) return Json::fail('数据不存在!');
        if ($data['back_integral'] <= 0) return Json::fail('请输入积分');
        if ($product['use_integral'] == $product['back_integral']) return Json::fail('已退完积分!不能再积分了');
        $back_integral = $data['back_integral'];
        $data['back_integral'] = bcadd($data['back_integral'], $product['back_integral'], 2);
        $bj = bccomp((float)$product['use_integral'], (float)$data['back_integral'], 2);
        if ($bj < 0) return Json::fail('退积分大于支付积分，请修改退积分');
        BaseModel::beginTrans();
        $integral = User::where('uid', $product['uid'])->value('integral');
        $res1 = User::bcInc($product['uid'], 'integral', $back_integral, 'uid');
        $res2 = UserBill::income('商品退积分', $product['uid'], 'integral', 'pay_product_integral_back', $back_integral, $product['id'], bcadd($integral, $back_integral, 2), '订单退积分' . floatval($back_integral) . '积分到用户积分');
        event('StoreOrderIntegralBack', [$product, $back_integral]);
        try {
            OrderRepository::storeOrderIntegralBack($product, $back_integral);
        } catch (\Exception $e) {
            BaseModel::rollbackTrans();
            return Json::fail($e->getMessage());
        }
        $res = $res1 && $res2;
        BaseModel::checkTrans($res);
        if (!$res) return Json::fail('退积分失败!');
        if ($product['pay_price'] == 0 && $bj == 0) {
            $data['refund_status'] = 2;
        }
        StoreOrderModel::edit($data, $id);
        StoreOrderStatus::setStatus($id, 'integral_back', '商品退积分：' . $data['back_integral']);
        return Json::successful('退积分成功!');
    }

    public function remark()
    {
        $data = Util::postMore(['id', 'remark']);
        if (!$data['id']) return Json::fail('参数错误!');
        if ($data['remark'] == '') return Json::fail('请输入要备注的内容!');
        $id = $data['id'];
        unset($data['id']);
        StoreOrderModel::edit($data, $id);
        return Json::successful('备注成功!');
    }

    public function order_status($oid)
    {
        if (!$oid) return $this->failed('数据不存在');
        $this->assign(StoreOrderStatus::systemPage($oid));
        return $this->fetch();
    }

    /*
     * 订单列表推荐人详细
     */
    public function order_spread_user($uid)
    {
        $spread = User::where('uid', $uid)->find();
        $this->assign('spread', $spread);
        return $this->fetch();
    }

    /**
     * 立即核销
     * @param $id
     */
    public function verify($id)
    {
        StoreOrderModel::beginTrans();
        $orderInfo = StoreOrderModel::where('id', $id)->find();
        if (!$orderInfo) return Json::fail('核销订单不存在！');
        if ($orderInfo->status > 0) return Json::fail('订单已核销！');
        if ($orderInfo->combination_id && $orderInfo->pink_id) {
            $res = StorePink::where('id', $orderInfo->pink_id)->where('status', '<>', 2)->count();
            if ($res) return Json::fail('拼团订单暂未成功无法核销！');
        }

        $orderInfo->status = 2;
        if ($orderInfo->save()) {
            OrderRepository::storeProductOrderTakeDeliveryAdmin($orderInfo);
            StoreOrderStatus::setStatus($orderInfo->id, 'take_delivery', '已核销');
            //发送短信
            event('ShortMssageSend', [$orderInfo['order_id'], 'Receiving']);
            StoreOrderModel::commitTrans();
            return Json::successful('核销成功！');
        } else {
            StoreOrderModel::rollbackTrans();
            return Json::fail('核销失败');
        }
    }

}
