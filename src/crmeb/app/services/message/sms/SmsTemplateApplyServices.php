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

namespace app\services\message\sms;


use app\services\BaseServices;
use crmeb\services\FormBuilder;

/**
 * 短信模板
 * Class SmsTemplateApplyServices
 * @package app\services\message\sms
 */
class SmsTemplateApplyServices extends BaseServices
{
    /**
     * @var FormBuilder
     */
    protected $builder;

    /**
     * SmsTemplateApplyServices constructor.
     * @param FormBuilder $builder
     */
    public function __construct(FormBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * 创建短信模板表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createSmsTemplateForm()
    {
        $field = [
            $this->builder->input('title', '模板名称')->placeholder('模板名称,如：订单支付成功'),
            $this->builder->input('content', '模板内容')->type('textarea')->placeholder('模板内容，如：【CRMEB】您购买的商品已支付成功，支付金额{$pay_price}元，订单号{$order_id},感谢您的光临！'),
            $this->builder->radio('type', '模板类型', 1)->options([['label' => '验证码', 'value' => 1], ['label' => '通知', 'value' => 2], ['label' => '推广', 'value' => 3]])
        ];
        return $field;
    }

    /**
     * 获取短信申请模板
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getSmsTemplateForm()
    {
        return create_form('申请短信模板', $this->createSmsTemplateForm(), $this->url('/notify/sms/temp'), 'POST');
    }

}
