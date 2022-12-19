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
namespace app\adminapi\validate\marketing;

use think\Validate;

class StoreCombinationValidate extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'product_id' => 'require',
        'title' => 'require',
        'info' => 'require',
        'unit_name' => 'require',
        'images' => 'require',
        'section_time' => 'require',
        'num' => 'require|gt:0',
        'once_num' => 'require|gt:0',
        'temp_id' => 'require',
        'description' => 'require',
        'attrs' => 'require',
        'items' => 'require',
        'people' => 'require|gt:1',
        'effective_time' => 'require|gt:0',
        'virtual' => 'require|gt:0|elt:100',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'product_id.require' => '400337',
        'title.require' => '400338',
        'info.require' => '400347',
        'unit_name.require' => '400348',
        'images.require' => '400349',
        'section_time.require' => '400353',
        'num.require' => '400354',
        'num.gt' => '400355',
        'virtual.require' => '400363',
        'virtual.gt' => '400364',
        'virtual.elt' => '400365',
        'once_num.require' => '400366',
        'once_num.gt' => '400367',
        'temp_id.require' => '400360',
        'description.require' => '400361',
        'attrs.require' => '400362',
        'people.require' => '400368',
        'people.gt' => '400369',
        'effective_time.require' => '400370',
        'effective_time.gt' => '400371',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'info', 'unit_name', 'image', 'images', 'section_time', 'is_host', 'is_show', 'num', 'people', 'once_num', 'virtual', 'temp_id', 'sort', 'description', 'attrs', 'items', 'people', 'effective_time'],
    ];
}
