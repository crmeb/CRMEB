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

class StoreBargainValidate extends Validate
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
        'temp_id' => 'require',
        'description' => 'require',
        'attrs' => 'require',
        'items' => 'require',
        'bargain_num'=>'require|gt:0',
        'people_num'=>'require|gt:1',
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
        'bargain_num.require' => '400356',
        'bargain_num.gt' => '400357',
        'people_num.require' => '400358',
        'people_num.gt' => '400359',
        'temp_id.require' => '400360',
        'description.require' => '400361',
        'attrs.require' => '400362',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'info', 'unit_name', 'image', 'images', 'give_integral', 'section_time', 'is_hot', 'status', 'num', 'bargain_num', 'people_num', 'temp_id', 'sort', 'description', 'attrs', 'items'],
    ];
}
