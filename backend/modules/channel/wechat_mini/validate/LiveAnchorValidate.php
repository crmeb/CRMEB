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
namespace modules\channel\wechat_mini\validate;

use think\Validate;

class LiveAnchorValidate extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require',
        'wechat' => 'require',
        'phone' => 'require|checkPhone',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require' => '请输入姓名',
        'wechat.require' => '请输入微信账号',
        'phone.require' => '请填写手机号码',
        'phone.checkPhone' => '手机号格式错误',
    ];

    protected function checkPhone($value): bool
    {
        return check_phone($value) == true;
    }

    protected $scene = [
        'save' => ['name', 'wechat', 'phone'],
    ];
}
