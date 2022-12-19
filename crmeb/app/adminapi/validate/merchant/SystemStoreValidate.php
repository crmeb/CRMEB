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
namespace app\adminapi\validate\merchant;

use think\Validate;

class SystemStoreValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
        'introduction' => 'require',
        'phone' => ['require', 'mobile'],
        'address' => 'require',
        'image' => 'require',
        'oblong_image' => 'require',
        'detailed_address' => 'require',
        'latlng' => 'require',
        'day_time' => 'require',
    ];
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '400378',
        'introduction.require' => '400379',
        'phone.require' => '400380',
        'image.require' => '400381',
        'oblong_image.require' => '400382',
        'phone.mobile' => '400252',
        'address.require' => '400383',
        'detailed_address.require' => '400384',
        'latlng.require' => '400385',
        'day_time.require' => '400386',
    ];

    protected $scene = [
        'save' => ['name', 'phone', 'address', 'detailed_address', 'latlng', 'day_time', 'image', 'oblong_image'],
    ];
}
