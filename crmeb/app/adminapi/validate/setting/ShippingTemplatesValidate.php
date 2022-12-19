<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\validate\setting;

use think\Validate;

class ShippingTemplatesValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
        'region_info' => 'array',
        'appoint_info' => 'array',
        'no_delivery_info' => 'array',
        'type' => 'number',
        'appoint' => 'number',
        'no_delivery' => 'number',
        'sort' => 'number'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '400025',
        'region_info.array' => '400026',
        'appoint_info.array' => '400027',
        'no_delivery_info.array' => '400028',
        'type.number' => '400029',
        'appoint.number' => '400030',
        'no_delivery.number' => '400031',
        'sort.number' => '400032',
    ];

    protected $scene = [
        'save' => ['name', 'type', 'appoint', 'sort', 'region_info', 'appoint_info', 'no_delivery_info', 'no_delivery'],
    ];
}
