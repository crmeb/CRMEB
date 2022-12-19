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
namespace app\outapi\validate;

use think\Validate;

class StoreCategoryValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'pid' => 'number|egt:0',
        'cate_name' => 'require|max:25',
        'pic' => 'max:128',
        'big_pic' => 'max:200',
        'sort' => 'number|egt:0',
        'is_show' => 'in:0,1'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'pid.number' => '400745',
        'pid.egt' => '400745',
        'cate_name.require' => '410095',
        'cate_name.max' => '400746',
        'pic.max' => '400747',
        'big_pic.max' => '400748',
        'sort.number' => '400749',
        'sort.egt' => '400750',
        'is_show.in' => '400751',
    ];

    protected $scene = [
        'save' => ['pid', 'cate_name', 'pic', 'big_pic', 'sort', 'is_show'],
    ];
}