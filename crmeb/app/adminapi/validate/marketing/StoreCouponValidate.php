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
namespace app\adminapi\validate\marketing;

use think\Validate;

class StoreCouponValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require',
        'image' => 'require',
        'category_id' => 'require',
        'coupon_price' => 'require',
        'use_min_price' => 'require',
        'coupon_time' => 'require',
        'status' => 'In:0,1',
        'type' => ['require', 'In:0,1,2'],
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'title.require' => '请填写优惠卷名称',
        'image.require' => '请选择商品',
        'category_id.require' => '请选择商品类型',
        'coupon_price.require' => '请填写优惠卷金额',
        'use_min_price.require' => '请填写优惠卷最小使用金额',
        'coupon_time.require' => '请填写优惠卷有效期',
    ];

    protected $scene = [
        'save' => ['title', 'coupon_price', 'use_min_price', 'coupon_time'],
        'type' => ['title', 'category_id', 'coupon_price', 'use_min_price', 'coupon_time'],
        'product' => ['title', 'image', 'coupon_price', 'use_min_price', 'coupon_time'],
    ];
}
