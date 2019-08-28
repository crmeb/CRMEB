<?php
namespace app\http\validates\user;

use think\Validate;

/**
 * 用户地址验证类
 * Class AddressValidate
 * @package app\http\validates\user
 */
class AddressValidate extends  Validate
{
    //移动
    protected $regex = [ 'phone' => '/^1[3456789]\d{9}|([0-9]{3,4}-)?[0-9]{7,8}$/'];

    protected $rule = [
        'real_name'  =>  'require|max:25',
        'phone'  =>  'require|regex:phone',
        'province'  =>  'require',
        'city'  =>  'require',
        'district'  =>  'require',
        'detail'  =>  'require',
    ];

    protected $message  =   [
        'real_name.require' => '名称必须填写',
        'real_name.max'     => '名称最多不能超过25个字符',
        'phone.require' => '手机号必须填写',
        'phone.regex' => '手机号格式错误',
        'province.require' => '省必须填写',
        'city.require' => '市名称必须填写',
        'district.require' => '区/县名称必须填写',
        'detail.require' => '详细地址必须填写',
    ];
}