<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

\think\Route::get('captcha/[:id]', "\\think\\captcha\\CaptchaController@index");

\think\Validate::extend('captcha', function ($value, $id = '') {
    return captcha_check($value, $id);
});

\think\Validate::setTypeMsg('captcha', ':attribute错误!');


/**
 * @param string $id
 * @param array  $config
 * @return \think\Response
 */
function captcha($id = '', $config = [])
{
    $captcha = new \think\captcha\Captcha($config);
    return $captcha->entry($id);
}


/**
 * @param $id
 * @return string
 */
function captcha_src($id = '')
{
    return \think\Url::build('/captcha' . ($id ? "/{$id}" : ''));
}


/**
 * @param $id
 * @return mixed
 */
function captcha_img($id = '')
{
    return '<img src="' . captcha_src($id) . '" alt="captcha" />';
}


/**
 * @param string $id
 * @param string $element 验证码HTML元素ID
 * @return string
 */
function captcha_img_with_replacement($id = '', $element = 'think-captcha')
{
    return '<img src="' . captcha_src($id) . '" alt="captcha" id="' . $element . '" onclick="document.getElementById("'
        . $element . '").src="' . captcha_src($id) . '"+Math.random()' . '/>';
}


/**
 * @param        $value
 * @param string $id
 * @param array  $config
 * @return bool
 */
function captcha_check($value, $id = '')
{
    $captcha = new \think\captcha\Captcha((array)\think\Config::get('captcha'));
    return $captcha->check($value, $id);
}
