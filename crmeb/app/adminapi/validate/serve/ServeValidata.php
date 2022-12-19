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

namespace app\adminapi\validate\serve;


use think\Validate;

class ServeValidata extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'phone' => 'require|number|mobile',
        'password' => 'require',
        'verify_code' => 'require|number',
        'account' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'phone.require' => '400333',
        'phone.number' => '400019',
        'phone.mobile' => '400252',
        'password.require' => '400020',
        'verify_code.require' => '400137',
        'verify_code.number' => '400021',
        'account.require' => '400133',
    ];

    protected $scene = [
        'login' => ['password', 'account'],
        'phone' => ['phone']
    ];
}
