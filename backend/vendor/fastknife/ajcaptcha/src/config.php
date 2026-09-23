<?php
declare(strict_types=1);

return [
    'font_file' => '', //自定义字体包路径， 不填使用默认值（现在是SourceHanSansCN-Normal.ttf）
    //文字验证码
    'click_word' => [
        'backgrounds' => [],
        'word_num' => 3, //目标字数量（2-5）
        'distract_num' => 2, //干扰字数量
        
        // Unicode 图标配置：图标字符 => 文字说明
        // 支持直接使用Unicode字符或转义序列
        // 如: '⚓' 或 "\u{2693}" (十进制9875)
        'icons' => [
            '☎' => '电话',
            '★' => '星星',
            '☀' => '太阳',
            '☂' => '雨伞',
            '♪' => '音符',
            '♥' => '红心',
            '♠' => '黑桃',
            '✓' => '对勾',
            '☁' => '云朵',
            '☃' => '雪人',
            '♬' => '音乐',
            '♻' => '回收',
            '⚽' => '足球',
            '⚠' => '警告',
         
        ],
        // 图标模式: 'random'=随机出现, 'always'=每次都有, 'never'=从不出现
        'icon_mode' => 'random',       // 统一控制图标显示行为：'never'=纯汉字, 'always'=必有图标, 'random'=随机出现
        'min_icons' => 0,              // 最少图标数量 (icon_mode='random'时生效)
        'max_icons' => 1,              // 最多图标数量
        'icon_font_size_scale' => 1.3, // 图标字体放大比例（相对于汉字）
    ],
    //滑动验证码
    'block_puzzle' => [
        // 模式: 'drawing' (原生绘图, 抗锯齿, 推荐), 'resource' (原图模板)
        'mode' => 'drawing',

        // 形状类型 (仅在 mode=drawing 时生效): 
        // 'jigsaw' (拼图), 'red_heart' (红桃), 'diamond' (方片), 'spade' (黑桃), 'club' (草花)
        'shape_type' => 'jigsaw',

        /*背景图片路径， 不填使用默认值， 支持string与array两种数据结构。string为默认图片的目录，array索引数组则为具体图片的地址*/
        'backgrounds' => [],

        /*模板图,格式同上支持string与array (仅在 mode=resource 时生效)*/
        'templates' => [],

        'offset' => 5, //容错偏移量

        // 是否开启像素缓存 (仅在 mode=resource 时生效，能提升响应性能)
        'is_cache_pixel' => true,

        'is_interfere' => true, //开启干扰图 (仅在 mode=resource 时生效, Drawing 模式暂不支持干扰)

        'blur_num' => 3, //扣图模糊系数。选值1~10之间。数值越大越模糊
    ],
    //水印
    'watermark' => [
        'fontsize' => 12,
        'color' => '#ffffff',
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
