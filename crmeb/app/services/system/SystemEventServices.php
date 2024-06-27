<?php

namespace app\services\system;

use app\dao\system\SystemEventDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use think\facade\Db;

class SystemEventServices extends BaseServices
{
    public function __construct(SystemEventDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取场景列表
     * @return \string[][]
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getMarkList()
    {
//        $data = [
//            [
//                'label' => '用户注册',
//                'value' => 'user_register',
//                'data' => [
//                    'uid' => '用户uid',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户手机号',
//                    'add_time' => '用户注册时间',
//                    'user_type' => '用户来源',
//                ]
//            ],
//            [
//                'label' => '用户登录',
//                'value' => 'user_login',
//                'data' => [
//                    'uid' => '用户uid',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户手机号',
//                    'add_time' => '用户注册时间',
//                    'login_time' => '用户登录时间',
//                    'user_type' => '用户来源',
//                ]
//            ],
//            [
//                'label' => '用户注销',
//                'value' => 'user_cancel',
//                'data' => [
//                    'uid' => '用户uid',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户手机号',
//                    'add_time' => '用户注册时间',
//                    'cancel_time' => '用户注销时间',
//                    'user_type' => '用户来源',
//                ]
//            ],
//            [
//                'label' => '用户修改信息',
//                'value' => 'user_change_info',
//                'data' => [
//                    'uid' => '用户uid',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户手机号',
//                    'avatar' => '用户头像',
//                    'add_time' => '用户注册时间',
//                    'user_type' => '用户来源',
//                ]
//            ],
//            [
//                'label' => '绑定推广关系',
//                'value' => 'user_spread',
//                'data' => [
//                    'uid' => '用户uid',
//                    'nickname' => '用户昵称',
//                    'spread_uid' => '上级用户uid',
//                    'spread_time' => '用户绑定时间',
//                    'user_type' => '用户来源',
//                ]
//            ],
//            [
//                'label' => '用户签到',
//                'value' => 'user_sign',
//                'data' => [
//                    'uid' => '用户uid',
//                    'sign_point' => '签到积分',
//                    'sign_exp' => '签到经验',
//                    'sign_time' => '签到时间',
//                ]
//            ],
//            [
//                'label' => '用户充值',
//                'value' => 'user_recharge',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '订单id',
//                    'order_id' => '订单order_id',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户电话',
//                    'price' => '充值金额',
//                    'give_price' => '赠送金额',
//                    'now_money' => '当前余额',
//                    'recharge_time' => '充值时间',
//                ]
//            ],
//            [
//                'label' => '用户提现',
//                'value' => 'user_extract',
//                'data' => [
//                    'uid' => '用户uid',
//                    'phone' => '用户电话',
//                    'extract_type' => '提现类型',
//                    'extract_price' => '提现金额',
//                    'extract_fee' => '提现手续费',
//                    'extract_time' => '提现时间',
//                ]
//            ],
//            [
//                'label' => '用户商品访问',
//                'value' => 'user_product_visit',
//                'data' => [
//                    'product_id' => '商品id',
//                    'uid' => '用户uid',
//                    'visit_time' => '访问时间',
//                ]
//            ],
//            [
//                'label' => '用户商品收藏',
//                'value' => 'user_product_collect',
//                'data' => [
//                    'product_id' => '商品id',
//                    'uid' => '用户uid',
//                    'collect_time' => '访问时间',
//                ]
//            ],
//            [
//                'label' => '用户加入购物车',
//                'value' => 'user_add_cart',
//                'data' => [
//                    'product_id' => '商品id',
//                    'uid' => '用户uid',
//                    'cart_num' => '商品数量',
//                    'add_time' => '添加时间',
//                ]
//            ],
//            [
//                'label' => '用户抽奖',
//                'value' => 'user_lottery',
//                'data' => [
//                    'uid' => '用户uid',
//                    'lottery_id' => '抽奖id',
//                    'prize_id' => '奖品id',
//                    'record_id' => '中奖记录id',
//                    'lottery_time' => '抽奖时间',
//                ]
//            ],
//            [
//                'label' => '订单创建',
//                'value' => 'order_create',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '订单id',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'total_num' => '商品总数',
//                    'pay_price' => '支付金额',
//                    'pay_postage' => '支付邮费',
//                    'deduction_price' => '积分抵扣金额',
//                    'coupon_price' => '优惠券抵扣金额',
//                    'store_name' => '商品名称',
//                    'add_time' => '订单创建时间',
//                ]
//            ],
//            [
//                'label' => '订单取消',
//                'value' => 'order_cancel',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '订单id',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'total_num' => '商品总数',
//                    'pay_price' => '支付金额',
//                    'deduction_price' => '积分抵扣金额',
//                    'coupon_price' => '优惠券抵扣金额',
//                    'cancel_time' => '订单取消时间',
//                ]
//            ],
//            [
//                'label' => '订单支付',
//                'value' => 'order_pay',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '订单id',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'total_num' => '商品总数',
//                    'pay_price' => '支付金额',
//                    'pay_postage' => '支付邮费',
//                    'deduction_price' => '积分抵扣金额',
//                    'coupon_price' => '优惠券抵扣金额',
//                    'store_name' => '商品名称',
//                    'add_time' => '订单创建时间',
//                ]
//            ],
//            [
//                'label' => '订单收货/核销',
//                'value' => 'order_take',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '订单id',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'total_num' => '商品总数',
//                    'pay_price' => '支付金额',
//                    'pay_postage' => '支付邮费',
//                    'deduction_price' => '积分抵扣金额',
//                    'coupon_price' => '优惠券抵扣金额',
//                    'store_name' => '商品名称',
//                    'add_time' => '订单创建时间',
//                ]
//            ],
//            [
//                'label' => '订单发起退款',
//                'value' => 'order_initiated_refund',
//                'data' => [
//                    'uid' => '用户uid',
//                    'refund_order_id' => '退款订单order_id',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'refund_num' => '退款数量',
//                    'refund_price' => '退款金额',
//                    'refund_time' => '退款发起时间',
//                ]
//            ],
//            [
//                'label' => '用户取消退款',
//                'value' => 'order_refund_cancel',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '退款订单id',
//                    'store_order_id' => '对应正常订单id',
//                    'order_id' => '退款订单order_id',
//                    'refund_num' => '退款数量',
//                    'refund_price' => '退款金额',
//                    'cancel_time' => '拒绝时间',
//                ]
//            ],
//            [
//                'label' => '佣金到账',
//                'value' => 'order_brokerage',
//                'data' => [
//                    'uid' => '推广人uid',
//                    'order_id' => '订单order_id',
//                    'phone' => '推广人电话',
//                    'brokeragePrice' => '佣金金额',
//                    'goodsName' => '商品名称',
//                    'goodsPrice' => '订单金额',
//                    'add_time' => '到账时间',
//                ]
//            ],
//            [
//                'label' => '积分到账',
//                'value' => 'order_point',
//                'data' => [
//                    'uid' => '用户uid',
//                    'order_id' => '订单order_id',
//                    'phone' => '用户电话',
//                    'storeTitle' => '商品名称',
//                    'give_integral' => '赠送积分',
//                    'integral' => '总积分',
//                    'add_time' => '赠送时间',
//                ]
//            ],
//            [
//                'label' => '申请开票',
//                'value' => 'order_invoice',
//                'data' => [
//                    'uid' => '用户uid',
//                    'order_id' => '订单order_id',
//                    'phone' => '用户电话',
//                    'invoice_id' => '发票id',
//                    'add_time' => '开票时间',
//                ]
//            ],
//            [
//                'label' => '订单评价',
//                'value' => 'order_comment',
//                'data' => [
//                    'uid' => '用户uid',
//                    'oid' => '订单id',
//                    'unique' => '商品规格唯一值',
//                    'suk' => '商品规格',
//                    'product_id' => '商品id',
//                    'add_time' => '评价时间',
//                ]
//            ],
//            [
//                'label' => '管理员登录',
//                'value' => 'admin_login',
//                'data' => [
//                    'id' => '管理员id',
//                    'account' => '管理员账号',
//                    'head_pic' => '管理员头像',
//                    'real_name' => '管理员名称',
//                    'login_time' => '登录时间',
//                ]
//            ],
//
//            [
//                'label' => '后台提现成功',
//                'value' => 'admin_extract_success',
//                'data' => [
//                    'uid' => '用户uid',
//                    'price' => '提现金额',
//                    'pay_type' => '提现类型',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户电话',
//                    'success_time' => '成功时间'
//                ]
//            ],
//            [
//                'label' => '后台提现失败',
//                'value' => 'admin_extract_fail',
//                'data' => [
//                    'uid' => '用户uid',
//                    'price' => '提现金额',
//                    'pay_type' => '提现类型',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户电话',
//                    'fail_time' => '失败时间'
//                ]
//            ],
//            [
//                'label' => '后台充值退款',
//                'value' => 'admin_recharge_refund',
//                'data' => [
//                    'uid' => '用户uid',
//                    'refund_price' => '退款金额',
//                    'now_money' => '剩余余额',
//                    'nickname' => '用户昵称',
//                    'phone' => '用户电话',
//                    'refund_time' => '退款时间',
//                ]
//            ],
//            [
//                'label' => '后台修改订单改价',
//                'value' => 'admin_order_change',
//                'data' => [
//                    'uid' => '用户uid',
//                    'order_id' => '订单order_id',
//                    'pay_price' => '修改后订单金额',
//                    'gain_integral' => '修改后订单赠送积分',
//                    'change_time' => '修改时间',
//                ]
//            ],
//            [
//                'label' => '后台订单发货',
//                'value' => 'admin_order_express',
//                'data' => [
//                    'uid' => '用户uid',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'order_id' => '订单order_id',
//                    'delivery_name' => '快递名称/配送员名称',
//                    'delivery_id' => '快递单号/配送员电话',
//                    'express_time' => '发货事件',
//                ]
//            ],
//            [
//                'label' => '后台订单退款',
//                'value' => 'admin_order_refund_success',
//                'data' => [
//                    'uid' => '用户uid',
//                    'order_id' => '订单order_id',
//                    'real_name' => '用户名称',
//                    'user_phone' => '用户电话',
//                    'user_address' => '用户地址',
//                    'total_num' => '商品总数',
//                    'pay_price' => '支付金额',
//                    'refund_reason_wap' => '退款理由类型',
//                    'refund_reason_wap_explain' => '退款理由',
//                    'refund_price' => '实际退款金额',
//                    'refund_time' => '退款时间',
//                ]
//            ],
//            [
//                'label' => '后台订单拒绝退款',
//                'value' => 'admin_order_refund_fail',
//                'data' => [
//                    'uid' => '用户uid',
//                    'id' => '退款订单id',
//                    'store_order_id' => '对应正常订单id',
//                    'order_id' => '退款订单order_id',
//                    'refund_num' => '退款数量',
//                    'refund_price' => '退款金额',
//                    'refuse_reason' => '拒绝退款理由',
//                    'refuse_time' => '拒绝时间',
//                ]
//            ],
//        ];
//        foreach ($data as &$item){
//            $item['data'] = json_encode($item['data']);
//        }
//        app()->make(SystemEventDataServices::class)->saveAll($data);

        $data = app()->make(SystemEventDataServices::class)->selectList([])->toArray();

        foreach ($data as &$item) {
            $str = '$data = ' . var_export(json_decode($item['data'], true), true);
            $item['data'] = str_replace(['array (', ')'], ['[', ']'], $str);
        }
        return $data;
    }

    /**
     * 获取事件列表
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getEventList()
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList(['is_del' => 0], 'id,name,mark,content,add_time,is_open', $page, $limit, 'id desc')->toArray();
        $count = $this->dao->getCount(['is_del' => 0]);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            foreach ($this->getMarkList() as $markItem) {
                if ($markItem['value'] == $item['mark']) {
                    $item['mark_name'] = $markItem['label'];
                }
            }
        }
        return compact('list', 'count');
    }

    /**
     * 获取事件详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function getEventInfo($id)
    {
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException('事件不存在');
        $info = $info->toArray();
        $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
        $info['customCode'] = "<?php\n\n" . json_decode($info['customCode'], true);
        return $info;
    }

    public function saveEvent($data)
    {
        $data['add_time'] = time();
        $data['customCode'] = json_encode(preg_replace('/<\?php\s*\n/', '', $data['customCode']));
        if (!$data['id']) {
            unset($data['id']);
            $res = $this->dao->save($data);
        } else {
            $res = $this->dao->update(['id' => $data['id']], $data);
        }
        if (!$res) throw new AdminException(100006);
        return true;
    }

    /**
     * 删除事件
     * @param $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function eventDel($id)
    {
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException('事件不存在');
        $info->is_del = 1;
        $info->save();
        return true;
    }

    /**
     * 设置事件状态
     * @param $id
     * @param $is_open
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/6/7
     */
    public function setEventStatus($id, $is_open)
    {
        $res = $this->dao->update(['id' => $id], ['is_open' => $is_open]);
        if (!$res) throw new AdminException(100014);
        return true;
    }
}