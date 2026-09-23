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

namespace app\adminapi\validate\driver\logistics;

use think\Validate;

/**
 * 物流查询驱动验证器
 * Class LogisticsDriverValidate
 * @package driver\logistics
 */
class LogisticsDriverValidate extends Validate
{
    /**
     * @var array
     */
    protected $rule = [
        'driver_name' => 'require|max:64',
        'status' => 'in:0,1',
        'sort' => 'number',
    ];

    /**
     * @var array
     */
    protected $message = [
        'driver_name.require' => '请填写驱动名称',
        'driver_name.max' => '驱动名称最多64个字符',
        'status.in' => '状态值错误',
        'sort.number' => '排序必须为数字',
    ];
}
