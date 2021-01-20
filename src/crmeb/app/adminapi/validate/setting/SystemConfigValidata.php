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

namespace app\adminapi\validate\setting;


use think\Validate;

/**
 * Class SystemConfigValidata
 * @package app\adminapi\validate\setting
 */
class SystemConfigValidata extends Validate
{

    protected $regex = ['float_two' => '/^[0-9]+(.[0-9]{1,2})?$/'];
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'store_brokerage_ratio' => 'float|egt:0|elt:100|regex:float_two',
        'store_brokerage_two' => 'float|egt:0|elt:100|regex:float_two',
        'user_extract_min_price' => 'float|gt:0',
        'extract_time' => 'number|between:0,180',
        'replenishment_num' => 'number',
        'store_stock' => 'number',
        'store_brokerage_price' => 'float|egt:0',
        'integral_ratio' => 'float|egt:0|elt:1000|regex:float_two',
        'integral_max_num' => 'number|egt:0',
        'order_give_integral' => 'float|egt:0|elt:1000',
        'order_cancel_time' => 'float',
        'order_activity_time' => 'float',
        'order_bargain_time' => 'float',
        'order_seckill_time' => 'float',
        'order_pink_time' => 'float',
        'system_delivery_time' => 'float',
        'store_free_postage' => 'float',
        'integral_rule_number' => 'number|gt:0',
        'express_rule_number' => 'number|gt:0',
        'sign_rule_number' => 'number|gt:0',
        'offline_rule_number' => 'number|gt:0',
        'order_give_exp' => 'number|egt:0',
        'sign_give_exp' => 'number|egt:0',
        'invite_user_exp' => 'number|egt:0',
        'config_export_to_name' => 'chs|length:2,10',
        'config_export_to_tel' => 'mobile|number',
        'config_export_to_address' => 'chsAlphaNum|length:10,100',
        'config_export_siid' => 'alphaNum|length:10,50',
        'service_feedback' => 'length:10,90',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'store_brokerage_ratio.float' => '一级返佣比例必须为数字',
        'store_brokerage_ratio.regex' => '一级返佣比例最多两位小数',
        'store_brokerage_ratio.egt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_ratio.elt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_two.float' => '二级返佣比例必须为数字',
        'store_brokerage_two.regex' => '二级返佣比例最多两位小数',
        'store_brokerage_two.egt' => '二级返佣比例填写范围在0-100之间',
        'store_brokerage_two.elt' => '二级返佣比例填写范围在0-100之间',
        'replenishment_num.number' => '待补货数量必须为数字',
        'store_stock.number' => '警戒库存必须为数字',
        'store_brokerage_two.between' => '二级返佣比例填写范围在0-100之间',
        'user_extract_min_price.float' => '提现最小金额只能为数字',
        'user_extract_min_price.egt' => '提现最小金额必须大于0',
        'extract_time.number' => '佣金冻结时间范围在0-180之间',
        'extract_time.between' => '佣金冻结时间范围在0-180之间',
        'store_brokerage_price.float' => '满额分销金额金额必须为数字',
        'store_brokerage_price.gt' => '满额分销金额金额必须大于0',
        'integral_ratio.float' => '积分抵用比例必须为数字',
        'integral_ratio.regex' => '积分抵用比例最多两位小数',
        'integral_ratio.egt' => '积分抵用比例必须在0-1000之间',
        'integral_ratio.elt' => '积分抵用比例必须在0-1000之间',
        'integral_max_num.number' => '积分抵用上限必须为数字',
        'integral_max_num.egt' => '积分抵用上限必须大于等于0',
        'order_give_integral.float' => '下单赠送积分比例必须为数字',
        'order_give_integral.egt' => '下单赠送积分比例必须在0-1000之间',
        'order_give_integral.elt' => '下单赠送积分比例必须在0-1000之间',
        'order_cancel_time.float' => '普通商品未支付取消订单时间必须为数字',
        'order_activity_time.float' => '活动商品未支付取消订单时间必须为数字',
        'order_bargain_time.float' => '砍价商品未支付取消订单时间必须为数字',
        'order_pink_time.float' => '拼团商品未支付取消订单时间必须为数字',
        'system_delivery_time.float' => '订单发货后自动收货时间必须为数字',
        'store_free_postage.float' => '满额包邮金额必须为数字',
        'integral_rule_number.number' => '积分倍数必须大于0',
        'express_rule_number.number' => '折扣数必须大于0',
        'sign_rule_number.number' => '积分倍数必须大于0',
        'offline_rule_number.number' => '折扣数必须大于0',
        'order_give_exp.number' => '下单赠送经验比率必须为数字',
        'order_give_exp.egt' => '下单赠送经验比率必须大于0',
        'sign_give_exp.number' => '签到赠送经验必须为数字',
        'sign_give_exp.egt' => '签到赠送经验必须大于0',
        'invite_user_exp.number' => '邀请新用户赠送经验必须为数字',
        'invite_user_exp.egt' => '邀请新用户赠送经验必须大于0',
        'config_export_to_name.chs' => '发货人姓名必须为汉字',
        'config_export_to_name.length' => '发货人姓名长度在2-10位',
        'config_export_to_tel.number' => '发货人电话必须为数字',
        'config_export_to_tel.mobile' => '发货人电话请填写有效的手机号',
        'config_export_to_address.chsAlphaNum' => '发货人地址只能是汉字、字母、数字',
        'config_export_to_address.length' => '发货人地址长度为10-100位',
        'config_export_siid.alphaNum' => '电子面单打印机编号必须为数字、字母',
        'config_export_siid.length' => '电子面单打印机编号长度为10-50位',
        'service_feedback.length' => '客服反馈长度位10-90位',
    ];

    protected $scene = [

    ];
}
