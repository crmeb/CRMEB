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
        'type' => 'number',
        'appoint' => 'number',
        'sort' => 'number'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '请填写运费模板名称',
        'region_info.array' => '运费信息必须为数组',
        'appoint_info.array' => '包邮信息必须为数组',
        'type.number' => 'type数据格式错误，应为1或2或3',
        'appoint.number' => 'appoint数据格式错误，应为0或1',
        'sort.number' => 'sort数据格式错误，应为整数',
    ];

    protected $scene = [
        'save' => ['name', 'type', 'appoint', 'sort', 'region_info', 'appoint_info'],
    ];
}
