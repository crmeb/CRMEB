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

namespace app\services\serve;


use app\services\BaseServices;
use crmeb\services\express\Express;
use crmeb\services\FormBuilder;
use crmeb\services\product\Product;
use crmeb\services\serve\Serve;
use crmeb\services\sms\Sms;

/**
 * 平台服务入口
 * Class ServeServices
 * @package crmeb\services
 */
class ServeServices extends BaseServices
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
     * 获取配置
     * @return array
     */
    public function getConfig(array $config = [])
    {
        return array_merge([
            'account' => sys_config('sms_account'),
            'secret' => sys_config('sms_token')
        ], $config);
    }

    /**
     * 短信
     * @return Sms
     */
    public function sms(array $config = [])
    {
        return app()->make(Sms::class, [$this->getConfig($config)]);
    }

    /**
     * 复制商品
     * @return Product
     */
    public function copy(array $config = [])
    {
        return app()->make(Product::class, [$this->getConfig($config)]);
    }

    /**
     * 电子面单
     * @return Express
     */
    public function express(array $config = [])
    {
        return app()->make(Express::class, [$this->getConfig($config)]);
    }

    /**
     * 用户
     * @return Serve
     */
    public function user(array $config = [])
    {
        return app()->make(Serve::class, [$this->getConfig($config)]);
    }

    /**
     * 获取短信模板
     * @param int $page
     * @param int $limit
     * @param int $type
     * @return array
     */
    public function getSmsTempsList(int $page, int $limit, int $type)
    {
        $list = $this->sms()->temps($page, $limit, $type);
        foreach ($list['data'] as &$item) {
            $item['templateid'] = $item['temp_id'];
            switch ((int)$item['temp_type']) {
                case 1:
                    $item['type'] = '验证码';
                    break;
                case 2:
                    $item['type'] = '通知';
                    break;
                case 30:
                    $item['type'] = '营销短信';
                    break;
            }
        }
        return $list;
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
