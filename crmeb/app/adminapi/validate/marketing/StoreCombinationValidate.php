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
        'image' => 'require',
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
        'product_id.require' => '请选择商品',
        'title.require' => '请填写商品标题',
        'info.require' => '请填写拼团活动简介',
        'unit_name.require' => '请填写单位',
        'image.require' => '请选择商品主图',
        'images.require' => '请选择商品轮播图',
        'section_time.require' => '请选择时间段',
        'num.require' => '请填写购买数量',
        'num.gt' => '购买数量必须大于0',
        'virtual.require' => '请填写虚拟拼团比例',
        'virtual.gt' => '虚拟拼团比例必须在1-100之间',
        'virtual.elt' => '虚拟拼团比例必须在1-100之间',
        'once_num.require' => '请填写单次购买数量',
        'once_num.gt' => '单次购买数量必须大于0',
        'temp_id.require' => '请选择运费模板',
        'description.require' => '请填写拼团商品详情',
        'attrs.require' => '请选择规格',
        'people.require' => '请填写成团人数',
        'people.gt' => '拼团人数不能小于2人',
        'effective_time.require' => '请填写成团有效期',
        'effective_time.gt' => '成团有效期必须大于0',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'info', 'unit_name', 'image', 'images', 'section_time', 'is_host', 'is_show', 'num', 'people', 'once_num', 'virtual', 'temp_id', 'sort', 'description', 'attrs', 'items', 'people', 'effective_time'],
    ];
}
