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

class LiveGoodsValidate extends Validate
{

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id' => 'require',
        'store_name' => 'require',
        'image' => 'require',
        'price' => 'require|gt:0',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'id.require' => '请选择商品',
        'store_name.require' => '请输入商品名称',
        'image.require' => '请选择背景图',
        'price.require' => '请输入直播价格',
        'price.gt' => '直播价格必须大于0',
    ];

    protected $scene = [
        'save' => ['id', 'store_name', 'image', 'price'],
    ];
}
