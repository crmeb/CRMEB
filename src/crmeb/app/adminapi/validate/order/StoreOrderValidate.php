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
namespace app\adminapi\validate\order;

use think\Validate;

/**
 *
 * Class StoreOrderValidate
 * @package app\adminapi\validates
 */
class StoreOrderValidate extends Validate
{

    protected $rule = [
        'order_id'      => ['require','length'=>'1,32','alphaNum'],
        'total_price'   => ['require','float'],
        'total_postage' => ['require','float'],
        'pay_price'     => ['require','float'],
        'pay_postage'   => ['require','float'],
        'gain_integral' => ['float'],
    ];

    protected $message = [
        'order_id.require'      => '订单号必须存在',
        'order_id.length'       => '订单号有误',
        'order_id.alphaNum'     => '订单号必须为字母和数字',
        'total_price.require'   => '订单金额必须填写',
        'total_price.float'    => '订单金额必须为数字',
        'pay_price.require'     => '订单金额必须填写',
        'pay_price.float'      => '订单金额必须为数字',
        'pay_postage.require'   => '订单邮费必须填写',
        'pay_postage.float'    => '订单邮费必须为数字',
        'gain_integral.float'  => '赠送积分必须为数字',
    ];

    protected $scene = [

    ];
}
