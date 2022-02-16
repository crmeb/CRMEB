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

class StoreSeckillValidate extends Validate
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
        'time_id' => 'require',
        'temp_id' => 'require',
        'description' => 'require',
        'attrs' => 'require',
        'items' => 'require',
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
        'info.require' => '请填写秒杀活动简介',
        'unit_name.require' => '请填写单位',
        'images.require' => '请选择商品轮播图',
        'section_time.require' => '请选择时间段',
        'num.require' => '请填写数量限制',
        'num.gt' => '数量限制必须大于0',
        'once_num.require' => '请填写单次购买次数',
        'once_num.gt' => '单次购买次数必须大于0',
        'time_id.require' => '请选择秒杀时间段',
        'temp_id.require' => '请选择运费模板',
        'description.require' => '请填写秒杀商品详情',
        'attrs.require' => '请选择规格',
    ];

    protected $scene = [
        'save' => ['product_id', 'title', 'info', 'unit_name', 'image', 'images', 'give_integral', 'section_time', 'is_hot', 'status', 'num', 'once_num', 'time_id', 'temp_id', 'sort', 'description', 'attrs', 'items'],
    ];
}
