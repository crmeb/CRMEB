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

namespace app\adminapi\validate\setting;


use think\Validate;

/**
 * Class SystemConfigValidata
 * @package app\adminapi\validate\setting
 */
class SystemConfigValidata extends Validate
{

    protected $regex = ['float_two' => '/^[0-9]+(.[0-9]{1,2})?$/'];
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'site_url' => 'url',
        'store_brokerage_ratio' => 'float|egt:0|elt:100|regex:float_two',
        'store_brokerage_two' => 'float|egt:0|elt:100|regex:float_two',
        'user_extract_min_price' => 'float|gt:0|checkMinPrice',
        'extract_time' => 'number|between:0,180',
        'replenishment_num' => 'number',
        'store_stock' => 'number',
        'store_brokerage_price' => 'float',
        'integral_ratio' => 'float|egt:0|elt:1000|regex:float_two',
        'integral_max_num' => 'number|egt:0',
        'order_give_integral' => 'float|egt:0|elt:1000',
        'order_cancel_time' => 'float',
        'order_activity_time' => 'float',
        'order_bargain_time' => 'float',
        'order_seckill_time' => 'float',
        'order_pink_time' => 'float',
        'system_delivery_time' => 'float',
        'store_free_postage' => 'float',
        'integral_rule_number' => 'number|gt:0',
        'express_rule_number' => 'number|gt:0',
        'sign_rule_number' => 'number|gt:0',
        'offline_rule_number' => 'number|gt:0',
        'order_give_exp' => 'number|egt:0',
        'sign_give_exp' => 'number|egt:0',
        'invite_user_exp' => 'number|egt:0',
        'config_export_to_name' => 'chs|length:2,10',
        'config_export_to_tel' => 'mobile|number',
        'config_export_to_address' => 'chsAlphaNum|length:10,100',
        'config_export_siid' => 'alphaNum|length:10,50',
        'service_feedback' => 'length:10,90',
        'thumb_big_height' => 'number|egt:0',
        'thumb_big_width' => 'number|egt:0',
        'thumb_mid_height' => 'number|egt:0',
        'thumb_mid_width' => 'number|egt:0',
        'thumb_small_height' => 'number|egt:0',
        'thumb_small_width' => 'number|egt:0',
        'watermark_opacity' => 'number|between:0,100',
        'watermark_text' => 'chsAlphaNum|length:1,10',
        'watermark_text_size' => 'number|egt:0',
        'watermark_x' => 'number|egt:0',
        'watermark_y' => 'number|egt:0',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'site_url.url' => '400041',
        'store_brokerage_ratio.float' => '400042',
        'store_brokerage_ratio.regex' => '400043',
        'store_brokerage_ratio.egt' => '400044',
        'store_brokerage_ratio.elt' => '400044',
        'store_brokerage_two.float' => '400045',
        'store_brokerage_two.regex' => '400046',
        'store_brokerage_two.egt' => '400047',
        'store_brokerage_two.elt' => '400047',
        'replenishment_num.number' => '400048',
        'store_stock.number' => '400049',
        'store_brokerage_two.between' => '400047',
        'user_extract_min_price.float' => '400050',
        'user_extract_min_price.gt' => '400051',
        'extract_time.number' => '400052',
        'extract_time.between' => '400052',
        'store_brokerage_price.float' => '400053',
        'integral_ratio.float' => '400054',
        'integral_ratio.regex' => '400055',
        'integral_ratio.egt' => '400056',
        'integral_ratio.elt' => '400056',
        'integral_max_num.number' => '400057',
        'integral_max_num.egt' => '400058',
        'order_give_integral.float' => '400059',
        'order_give_integral.egt' => '400060',
        'order_give_integral.elt' => '400060',
        'order_cancel_time.float' => '400061',
        'order_activity_time.float' => '400062',
        'order_bargain_time.float' => '400063',
        'order_pink_time.float' => '400064',
        'system_delivery_time.float' => '400065',
        'store_free_postage.float' => '400066',
        'integral_rule_number.number' => '400067',
        'express_rule_number.number' => '400068',
        'sign_rule_number.number' => '400069',
        'offline_rule_number.number' => '400070',
        'order_give_exp.number' => '400071',
        'order_give_exp.egt' => '400072',
        'sign_give_exp.number' => '400073',
        'sign_give_exp.egt' => '400074',
        'invite_user_exp.number' => '400075',
        'invite_user_exp.egt' => '400076',
        'config_export_to_name.chs' => '400077',
        'config_export_to_name.length' => '400078',
        'config_export_to_tel.number' => '400079',
        'config_export_to_tel.mobile' => '400080',
        'config_export_to_address.chsAlphaNum' => '400081',
        'config_export_to_address.length' => '400082',
        'config_export_siid.alphaNum' => '400083',
        'config_export_siid.length' => '400084',
        'service_feedback.length' => '400085',
        'thumb_big_height.number' => '400405',
        'thumb_big_height.egt' => '400406',
        'thumb_big_width.number' => '400407',
        'thumb_big_width.egt' => '400408',
        'thumb_mid_height.number' => '400409',
        'thumb_mid_height.egt' => '400410',
        'thumb_mid_width.number' => '400411',
        'thumb_mid_width.egt' => '400412',
        'thumb_small_height.number' => '400413',
        'thumb_small_height.egt' => '400414',
        'thumb_small_width.number' => '400415',
        'thumb_small_width.egt' => '400416',
        'watermark_text.chsAlphaNum' => '400417',
        'watermark_text.length' => '400418',
        'watermark_text_size.number' => '400419',
        'watermark_text_size.egt' => '400420',
        'watermark_x.number' => '400421',
        'watermark_x.egt' => '400422',
        'watermark_y.number' => '400423',
        'watermark_y.egt' => '400424',
    ];

    protected $scene = [

    ];


    protected function checkMinPrice($value, $rule, $data = [])
    {
        if ($data['brokerage_type'] == 1 && bccomp($value, '1', 2) < 0) {
            return 410112;
        }
        return true;
    }
}
