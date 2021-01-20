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


class ExpressValidata extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'com' => 'require',
        'temp_id' => 'require',
        'to_name' => 'require',
        'to_tel' => 'require|mobile',
        'to_address' => 'require',
        'siid' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'com.require' => '请选择快递公司',
        'temp_id.number' => '请选择快递模板',
        'to_name.require' => '请填写寄件人姓名',
        'to_tel.require' => '请输入寄件人手机号码',
        'to_tel.mobile' => '寄件人手机号码不正确',
        'to_address.require' => '请填写寄件人详细地址',
        'siid.require' => '请填写云打印机编号',
    ];
}
