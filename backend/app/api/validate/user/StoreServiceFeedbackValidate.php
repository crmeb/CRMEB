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

namespace app\api\validate\user;


use think\Validate;

class StoreServiceFeedbackValidate extends Validate
{
    protected $regex = ['phone' => '/^1[3456789]\d{9}$/'];

    protected $rule = [
        'phone' => 'require|regex:phone',
        'rela_name' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'phone.require' => '手机号必须填写',
        'phone.regex' => '手机号格式错误',
        'content.require' => '请填写反馈内容',
        'rela_name.require' => '名称必须填写',
    ];
}
