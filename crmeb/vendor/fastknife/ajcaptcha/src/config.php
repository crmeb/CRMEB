<?php
declare(strict_types=1);

return [
    'font_file' => '', //自定义字体包路径， 不填使用默认值
    //文字验证码
    'click_world' => [
        'backgrounds' => []
    ],
    //滑动验证码
    'block_puzzle' => [
        /*背景图片路径， 不填使用默认值， 支持string与array两种数据结构。string为默认图片的目录，array索引数组则为具体图片的地址*/
        'backgrounds' => [],

        /*模板图,格式同上支持string与array*/
        'templates' => [],

        'offset' => 10, //容错偏移量

        'is_cache_pixel' => true, //是否开启缓存图片像素值，开启后能提升服务端响应性能（但要注意更换图片时，需要清除缓存）

        'is_interfere' => true, //开启干扰图
    ],
    //水印
    'watermark' => [
        'fontsize' => 12,
        'color' => '#000000',
        'text' => '我的水印'
    ],
    'cache' => [
        //若您使用了框架，并且想使用类似于redis这样的缓存驱动，则应换成框架的中的缓存驱动
        'constructor' => \Fastknife\Utils\CacheUtils::class,
        'method' => [
            //遵守PSR-16规范不需要设置此项（tp6, laravel,hyperf）。如tp5就不支持（tp5缓存方法是rm,所以要配置为"delete" => "rm"）
            /**
            'get' => 'get', //获取
            'set' => 'set', //设置
            'delete' => 'delete',//删除
            'has' => 'has' //key是否存在
             */
        ],
        'options' => [
            //如果您依然使用\Fastknife\Utils\CacheUtils做为您的缓存驱动，那么您可以自定义缓存配置。
            'expire'        => 300,//缓存有效期 （默认为0 表示永久缓存）
            'prefix'        => '', //缓存前缀
            'path'          => '', //缓存目录
            'serialize'     => [], //缓存序列化和反序列化方法
        ]
    ]
];
