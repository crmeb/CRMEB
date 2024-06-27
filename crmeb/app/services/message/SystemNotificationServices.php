<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\message;

use app\dao\system\SystemNotificationDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use think\facade\Route as Url;

/**
 * 消息管理类
 * Class SystemNotificationServices
 * @package app\services\system
 * @method value($where, $value) 条件获取某个字段的值
 */
class SystemNotificationServices extends BaseServices
{

    protected $messageData = [

        //短信验证码
        'verify_code' => [
            ['label' => '验证码', 'value' => 'code'],
            ['label' => '有效时间', 'value' => 'time'],
        ],

        //用户登录
        'login_success' => [
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '上次登录时间', 'value' => 'last_time'],
            ['label' => '用户余额', 'value' => 'now_money'],
            ['label' => '用户佣金', 'value' => 'brokerage_price'],
            ['label' => '用户积分', 'value' => 'integral'],
            ['label' => '用户经验', 'value' => 'exp'],
            ['label' => '登录时间', 'value' => 'time'],
        ],

        //用户绑定关系
        'spread_success' => [
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '绑定时间', 'value' => 'time'],
        ],

        //未支付订单修改金额
        'price_change_price' => [
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '订单原金额', 'value' => 'pay_price'],
            ['label' => '修改后金额', 'value' => 'change_price'],
        ],

        //订单支付成功
        'order_pay_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
            ['label' => '商品名称', 'value' => 'storeName'],
            ['label' => '下单时间', 'value' => 'time'],
        ],

        //订单快递发货
        'order_express_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
            ['label' => '商品名称', 'value' => 'storeName'],
            ['label' => '快递公司', 'value' => 'delivery_name'],
            ['label' => '快递单号', 'value' => 'delivery_id'],
            ['label' => '发货时间', 'value' => 'time'],
        ],

        //订单配送员送货
        'order_send_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
            ['label' => '商品名称', 'value' => 'storeName'],
            ['label' => '配送员姓名', 'value' => 'delivery_name'],
            ['label' => '配送员电话', 'value' => 'delivery_id'],
            ['label' => '送货时间', 'value' => 'time'],

        ],

        //订单收货
        'order_take' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
            ['label' => '商品名称', 'value' => 'storeTitle'],
            ['label' => '配送员姓名', 'value' => 'delivery_name'],
            ['label' => '配送员电话', 'value' => 'delivery_id'],
            ['label' => '签收时间', 'value' => 'time'],
        ],

        //订单发起退款
        'order_initiated_refund' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
        ],

        //订单成功退款
        'order_refund_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '订单order_id', 'value' => 'order_id'],
            ['label' => '用户名称', 'value' => 'real_name'],
            ['label' => '用户电话', 'value' => 'user_phone'],
            ['label' => '用户地址', 'value' => 'user_address'],
            ['label' => '商品总数', 'value' => 'total_num'],
            ['label' => '支付金额', 'value' => 'pay_price'],
            ['label' => '支付邮费', 'value' => 'pay_postage'],
            ['label' => '积分抵扣金额', 'value' => 'deduction_price'],
            ['label' => '优惠券抵扣金额', 'value' => 'coupon_price'],
            ['label' => '支付类型', 'value' => 'pay_type'],
            ['label' => '退款理由类型', 'value' => 'refund_reason_wap'],
            ['label' => '退款理由', 'value' => 'refund_reason_wap_explain'],
            ['label' => '实际退款金额', 'value' => 'refund_price'],
        ],

        //订单拒绝退款
        'order_refund_fail' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '退款金额', 'value' => 'refund_price'],
            ['label' => '拒绝退款理由', 'value' => 'refuse_reason'],
            ['label' => '拒绝时间', 'value' => 'time'],
        ],

        //用户充值
        'recharge_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '充值金额', 'value' => 'price'],
            ['label' => '赠送金额', 'value' => 'give_price'],
            ['label' => '充值后用户余额', 'value' => 'now_money'],
            ['label' => '充值时间', 'value' => 'time'],
        ],

        //用户充值退款
        'recharge_refund' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '退款金额', 'value' => 'price'],
            ['label' => '退款后用户余额', 'value' => 'now_money'],
            ['label' => '退款时间', 'value' => 'time'],
        ],

        //用户提现通过
        'extract_success' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '提现金额', 'value' => 'price'],
            ['label' => '提现时间', 'value' => 'time'],
        ],

        //用户提现失败
        'extract_fail' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户昵称', 'value' => 'nickname'],
            ['label' => '失败理由', 'value' => 'message'],
            ['label' => '提现金额', 'value' => 'price'],
            ['label' => '失败时间', 'value' => 'time'],
        ],

        //佣金到账
        'brokerage_received' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '到账金额', 'value' => 'brokeragePrice'],
            ['label' => '商品名称', 'value' => 'goodsName'],
            ['label' => '商品金额', 'value' => 'goodsPrice'],
            ['label' => '到账时间', 'value' => 'time'],
        ],

        //积分到账
        'point_received' => [
            ['label' => '用户uid', 'value' => 'uid'],
            ['label' => '用户电话', 'value' => 'phone'],
            ['label' => '积分数量', 'value' => 'give_integral'],
            ['label' => '商品名称', 'value' => 'storeTitle'],
            ['label' => '积分总数', 'value' => 'integral'],
            ['label' => '到账时间', 'value' => 'time'],
        ],


    ];

    /**
     * SystemNotificationServices constructor.
     * @param SystemNotificationDao $dao
     */
    public function __construct(SystemNotificationDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 单个配置
     * @param int $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOneNotce(array $where)
    {
        return $this->dao->getOne($where);
    }

    /**
     * 后台获取列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotList(array $where)
    {
        return $this->dao->getList($where);
    }

    /**
     * 添加自定义消息表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/2/19
     */
    public function getNotForm($id = 0)
    {
        if ($id) {
            $info = $this->dao->get($id);
            if ($info) $info = $info->toArray();
        } else {
            $info = [];
        }
        $data = [
            ['value' => 'login_success', 'label' => '用户登录成功场景'],
            ['value' => 'spread_success', 'label' => '绑定推广关系成功场景'],
            ['value' => 'price_change_price', 'label' => '未支付订单修改价格场景'],
            ['value' => 'order_pay_success', 'label' => '订单支付成功场景'],
            ['value' => 'order_express_success', 'label' => '订单快递发货成功场景'],
            ['value' => 'order_send_success', 'label' => '订单配送员开始送货场景'],
            ['value' => 'order_take', 'label' => '订单成功收货场景'],
            ['value' => 'order_initiated_refund', 'label' => '订单发起退款场景'],
            ['value' => 'order_refund_success', 'label' => '订单退款成功场景'],
            ['value' => 'order_refund_fail', 'label' => '订单退款失败场景'],
            ['value' => 'recharge_success', 'label' => '充值成功场景'],
            ['value' => 'recharge_refund', 'label' => '充值退款场景'],
            ['value' => 'extract_success', 'label' => '提现成功场景'],
            ['value' => 'extract_fail', 'label' => '提现失败场景'],
            ['value' => 'brokerage_received', 'label' => '佣金到账场景'],
            ['value' => 'point_received', 'label' => '积分到账场景'],
        ];
        $field = [];
        $field[] = Form::select('custom_trigger', '触发位置', $info['custom_trigger'] ?? '')->options($data);
        $field[] = Form::input('name', '名称', $info['name'] ?? '')->placeholder('请填写消息名称，例：支付成功消息');
        $field[] = Form::input('mark', '标识', $info['mark'] ?? '')->placeholder('请填写消息标识，使用英文和下划线，例：order_pay_success');
        return create_form('添加消息', $field, Url::buildUrl('/setting/notification/not_form_save/' . $id), 'POST');
    }

    /**
     * 保存自定义消息
     * @param $id
     * @param $data
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/2/20
     */
    public function notFormSave($id, $data)
    {
        if ($id) {
            $data['title'] = $data['name'];
            $res = $this->dao->update($id, $data);
        } else {
            $data['type'] = 3;
            $data['title'] = $data['name'];
            $data['is_system'] = $data['is_wechat'] = $data['is_routine'] = $data['is_sms'] = $data['is_ent_wechat'] = 2;
            $data['add_time'] = time();
            $res = $this->dao->save($data);
        }
        if ($res) return true;
        throw new AdminException(100006);
    }

    /**
     * 获取单条数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNotInfo(array $where)
    {
        $type = $where['type'];
        unset($where['type']);
        $info = $this->dao->getOne($where);
        if (!$info) return [];
        $info = $info->toArray();
        switch ($type) {
            case 'is_system':
                $info['content'] = $info['system_text'] ?? '';
                break;
            case 'is_sms':
                $info['content'] = $info['sms_text'];
                break;
            case 'is_wechat':
                $info['tempkey'] = $info['wechat_tempkey'] ?? '';
                $info['tempid'] = $info['wechat_tempid'] ?? '';
                $info['content'] = $info['wechat_content'] ?? '';
                $info['key_list'] = json_decode($info['wechat_data'], true) ?? [];
                break;
            case 'is_routine':
                $info['tempkey'] = $info['routine_tempkey'] ?? '';
                $info['tempid'] = $info['routine_tempid'] ?? '';
                $info['content'] = $info['routine_content'] ?? '';
                $info['key_list'] = json_decode($info['routine_data'], true) ?? [];
                break;
            case 'is_ent_wechat':
                $info['content'] = $info['ent_wechat_text'];
                break;
        }
        if ($info['type'] == 3) {
            $info['custom_variable'] = $this->messageData[$info['custom_trigger']];
            if (in_array($type, ['is_system', 'is_sms', 'is_ent_wechat'])) {
                foreach ($info['custom_variable'] as &$item) {
                    $item['value'] = '{' . $item['value'] . '}';
                }
            }
        }
        return $info;
    }

    /**
     * 保存数据
     * @param array $data
     * @return bool|\crmeb\basic\BaseModel|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveData(array $data)
    {
        $type = $data['type'];
        $id = $data['id'];
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(100026);
        }
        $res = null;
        switch ($type) {
            case 'is_system':
                $update = [];
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_system'] = $data['is_system'];
                $update['is_app'] = $data['is_app'];
                $update['system_title'] = $data['system_title'];
                $update['system_text'] = $data['system_text'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_sms':
                $update = [];
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_sms'] = $data['is_sms'];
                $update['sms_id'] = $data['sms_id'];
                $update['sms_text'] = $data['sms_text'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_wechat':
                $update['is_wechat'] = $data['is_wechat'];
                $update['wechat_tempid'] = $data['tempid'];
                $update['wechat_tempkey'] = $data['tempkey'];
                $update['wechat_content'] = $data['content'];
                $update['wechat_link'] = $data['wechat_link'];
                $update['wechat_to_routine'] = $data['wechat_to_routine'];
                $update['wechat_data'] = json_encode($data['key_list']);
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_routine':
                $update['is_routine'] = $data['is_routine'];
                $update['routine_tempid'] = $data['tempid'];
                $update['routine_tempkey'] = $data['tempkey'];
                $update['routine_content'] = $data['content'];
                $update['routine_data'] = json_encode($data['key_list']);
                $update['routine_link'] = $data['routine_link'];
                $res = $this->dao->update((int)$id, $update);
                break;
            case 'is_ent_wechat':
                $update['name'] = $data['name'];
                $update['title'] = $data['title'];
                $update['is_ent_wechat'] = $data['is_ent_wechat'];
                $update['ent_wechat_text'] = $data['ent_wechat_text'];
                $update['url'] = $data['url'];
                $res = $this->dao->update((int)$id, $update);
                break;
        }
        return $res;
    }

    /**
     * 获取tempid
     * @param $type
     * @return array
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/16
     */
    public function getTempId($type)
    {
        return $this->dao->getTempId($type);
    }

    /**
     * 获取tempkey
     * @param $type
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/16
     */
    public function getTempKey($type)
    {
        return $this->dao->getTempKey($type);
    }
}
