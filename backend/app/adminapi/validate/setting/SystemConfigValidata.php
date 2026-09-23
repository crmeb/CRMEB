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
        // 基础配置
        'site_url' => 'url',
        // 分销配置
        'store_brokerage_ratio' => 'float|egt:0|elt:100|regex:float_two',
        'store_brokerage_two' => 'float|egt:0|elt:100|regex:float_two',
        'store_brokerage_price' => 'float',
        'extract_time' => 'number|between:0,180',
        // 用户提现
        'user_extract_min_price' => 'float|gt:0',
        // 商品库存
        'store_stock' => 'number',
        // 积分
        'integral_ratio' => 'float|egt:0|elt:1000|regex:float_two',
        'order_give_integral' => 'float|egt:0|elt:1000',
        // 订单时间
        'order_cancel_time' => 'float',
        'order_activity_time' => 'float',
        'order_bargain_time' => 'float',
        'order_seckill_time' => 'float',
        'order_pink_time' => 'float',
        'system_delivery_time' => 'float',
        // 运费
        'store_free_postage' => 'float',
        // 经验
        'order_give_exp' => 'number|egt:0',
        'invite_user_exp' => 'number|egt:0',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'site_url.url' => '请输入有效的网址',
        'store_brokerage_ratio.float' => '一级返佣比例必须为数字',
        'store_brokerage_ratio.regex' => '一级返佣比例最多两位小数',
        'store_brokerage_ratio.egt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_ratio.elt' => '一级返佣比例填写范围在0-100之间',
        'store_brokerage_two.float' => '二级返佣比例必须为数字',
        'store_brokerage_two.regex' => '二级返佣比例最多两位小数',
        'store_brokerage_two.egt' => '二级返佣比例填写范围在0-100之间',
        'store_brokerage_two.elt' => '二级返佣比例填写范围在0-100之间',
        'store_stock.number' => '警戒库存必须为数字',
        'user_extract_min_price.float' => '提现最小金额只能为数字',
        'user_extract_min_price.gt' => '提现最小金额必须大于0',
        'extract_time.number' => '佣金冻结时间范围在0-180之间',
        'extract_time.between' => '佣金冻结时间范围在0-180之间',
        'store_brokerage_price.float' => '满额分销金额必须为数字',
        'integral_ratio.float' => '积分抵用比例必须为数字',
        'integral_ratio.regex' => '积分抵用比例最多两位小数',
        'integral_ratio.egt' => '积分抵用比例必须在0-1000之间',
        'integral_ratio.elt' => '积分抵用比例必须在0-1000之间',
        'order_give_integral.float' => '下单赠送积分必须为数字',
        'order_give_integral.egt' => '下单赠送积分必须在0-1000之间',
        'order_give_integral.elt' => '下单赠送积分必须在0-1000之间',
        'order_cancel_time.float' => '普通商品未支付取消订单时间必须为数字',
        'order_activity_time.float' => '活动商品未支付取消订单时间必须为数字',
        'order_bargain_time.float' => '砍价商品未支付取消订单时间必须为数字',
        'order_pink_time.float' => '拼团商品未支付取消订单时间必须为数字',
        'system_delivery_time.float' => '订单发货后自动收货时间必须为数字',
        'store_free_postage.float' => '满额包邮金额必须为数字',
        'order_give_exp.number' => '下单赠送经验比率必须为数字',
        'order_give_exp.egt' => '下单赠送经验比率必须大于0',
        'invite_user_exp.number' => '邀请新用户赠送经验必须为数字',
        'invite_user_exp.egt' => '邀请新用户赠送经验必须大于0',
    ];

    protected $scene = [

    ];
}
