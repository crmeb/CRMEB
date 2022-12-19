<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

use think\facade\Env;

return [
    // 默认语言
    'default_lang' => Env::get('lang.default_lang', 'zh-cn'),
    // 允许的语言列表
    'allow_lang_list' => ['zh-cn', 'en-us', 'ja_jp', 'fr_fr', 'th_th', 'it_it', 'ko_kr', 'vi_vn', 'zh_ft'],
    // 多语言自动侦测变量名
    'detect_var' => 'lang',
    // 是否使用Cookie记录
    'use_cookie' => true,
    // 多语言cookie变量
    'cookie_var' => 'cb_lang',
    // 扩展语言包
    'extend_list' => [
        'zh_cn' => app()->getBasePath() . 'lang/zh_cn.php',
        'en_us' => app()->getBasePath() . 'lang/en_us.php',
        'ja_jp' => app()->getBasePath() . 'lang/ja_jp.php',
        'fr_fr' => app()->getBasePath() . 'lang/fr_fr.php',
        'th_th' => app()->getBasePath() . 'lang/th_th.php',
        'it_it' => app()->getBasePath() . 'lang/it_it.php',
        'ko_kr' => app()->getBasePath() . 'lang/ko_kr.php',
        'mn_mn' => app()->getBasePath() . 'lang/mn_mn.php',
        'vi_vn' => app()->getBasePath() . 'lang/vi_vn.php',
        'zh_ft' => app()->getBasePath() . 'lang/zh_ft.php',
    ],
    // Accept-Language转义为对应语言包名称
    'accept_language' => [
        'zh-hans-cn' => 'zh_cn',
        'en-hans-us' => 'en_us',
        'ja-hans-jp' => 'ja_jp',
        'fr-hans-fr' => 'fr_fr',
        'th-hans-th' => 'th_th',
        'it-hans-it' => 'it_it',
        'ko-hans-kr' => 'ko_kr',
        'mn-hans-mn' => 'mn_mn',
        'vi-hans-vn' => 'vi_vn',
        'zh-hans-ft' => 'zh_ft',
    ],
    // 是否支持语言分组
    'allow_group' => true,
];
