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

namespace app\adminapi\validate\serve;


use think\Validate;

class MealValidata extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'meal_id' => 'require|number',
        'price' => 'require',
        'num' => 'require|number',
        'type' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'meal_id.require' => '请传入套餐id',
        'meal_id.number' => '套餐id必须为数字',
        'price.require' => '请填写套餐金额',
        'num.require' => '请填写购买数量',
        'num.number' => '购买数量必须为数字',
        'type.require' => '请填写购买套餐类型'
    ];

}
