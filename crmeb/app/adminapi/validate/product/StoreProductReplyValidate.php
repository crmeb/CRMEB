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
namespace app\adminapi\validate\product;

use think\Validate;

class StoreProductReplyValidate extends Validate
{
    public function __construct()
    {
        parent::__construct();

        /**
         * 定义错误信息
         * 格式：'字段名.规则名'    =>    '错误信息'
         *
         * @var array
         */
        $this->message = [
            'product_id.require' => '400337',
            'avatar.require' => '400000',
            'nickname.require' => '400001',
            'comment.require' => '400002',
            'product_score.require' => '400003',
            'service_score.require' => '400004',
            'product_score.In' => '400005',
            'service_score.In' => '400006',
        ];
    }

    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'product_id' => 'require',
        'avatar' => 'require',
        'nickname' => 'require',
        'comment' => 'require',
        'product_score' => ['require','In:1,2,3,4,5'],
        'service_score' => ['require','In:1,2,3,4,5'],
    ];

    protected $scene = [
        'save' => ['product_id', 'nickname', 'comment', 'avatar', 'product_score', 'service_score'],
    ];
}
