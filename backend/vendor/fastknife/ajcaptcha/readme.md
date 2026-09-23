# AJ-Captcha for PHP

![Version](https://img.shields.io/badge/version-2.1.0-blue.svg)
![PHP](https://img.shields.io/badge/php-%3E%3D7.1-green.svg)
![License](https://img.shields.io/badge/license-MIT-orange.svg)

这个类库使用 PHP 实现了行为验证码（滑动拼图、点选文字）。
v2.0 版本基于 **Strategy Pattern（策略模式）** 彻底重构了底层架构，移除了第三方图像库依赖，引入了原生 GD 绘图与抗锯齿技术，带来了更极致的体验与更高的性能。

Java实现： https://gitee.com/belief-team/captcha

PHP实现： https://gitee.com/fastknife/aj-captcha

文档地址：https://ajcaptcha.beliefteam.cn/captcha-doc/captchaDoc/html.html
##### 预览效果

![block](https://gitee.com/anji-plus/captcha/raw/master/images/%E6%BB%91%E5%8A%A8%E6%8B%BC%E5%9B%BE.gif) &emsp;&emsp;![click](https://gitee.com/anji-plus/captcha/raw/master/images/%E7%82%B9%E9%80%89%E6%96%87%E5%AD%97.gif)

## ✨ 核心特性 (v2.1)

*   **轻量零依赖**：彻底移除 `intervention/image`，完全基于 PHP 原生 GD 库，体积更小，兼容性更强（PHP 7.1 ~ 8.5+）。
*   **极致抗锯齿 (Anti-Aliasing)**：
    *   全新的 `Drawing` 模式，抛弃了传统的图片模板抠图方案。
    *   采用 **6 倍超采样 (Super Sampling) + 高斯模糊 (Gaussian Blur)** 技术实时绘制滑块。
    *   生成的滑块边缘平滑细腻，自带内阴影与半透明质感，完美融合背景。
*   **多形状支持**：内置多种滑块形状，支持一键切换：
    *   🧩 拼图 (`jigsaw`)
    *   ❤️ 红桃 (`red_heart`)
    *   ♠️ 黑桃 (`spade`)
    *   ♦️ 方片 (`diamond`)
    *   ♣️ 草花 (`club`)
*   **Unicode 图标验证码**：点击验证码支持 Unicode 图标，实现图文混合验证。图片上显示图标（如 ☕），前端提示显示文字说明（如 `<杯子>`），提升趣味性和安全性。
*   **安全增强**：
    *   **干扰图**：滑动验证码支持随机生成干扰滑块（位置、形状随机），增加机器识别难度。
    *   **干扰字**：点击验证码支持生成随机干扰文字。
    *   **智能布局**：采用 **随机坐标 + 碰撞检测算法**，确保文字不重叠、不越界。
*   **双模式兼容**：保留了旧版"图片模板"模式 (`resource`)，老用户可无缝切换。

## 📸 效果预览

### 滑动验证码 (Drawing 模式)
*(无需任何图片素材，纯代码实时绘制)*

> 效果图占位：建议运行 testImage.php 查看实际效果

### 点击验证码
支持文字点选，自带干扰文字与随机布局。

## 🛠 安装

### 要求
*   PHP >= 7.1
*   ext-gd
*   ext-openssl

### Composer 安装
```bash
composer require fastknife/ajcaptcha
```

## 🚀 快速开始

### 1. 原生 PHP 使用
```php
<?php
require 'vendor/autoload.php';

use Fastknife\Service\BlockPuzzleCaptchaService;
use Fastknife\Service\ClickWordCaptchaService;

// 加载配置
$config = require 'src/config.php';

// --- 获取验证码 ---
$service = new BlockPuzzleCaptchaService($config);
$data = $service->get(); 

// --- 一次验证 (前端滑动/点选后调用) ---
$token = $_REQUEST['token'];
$pointJson = $_REQUEST['pointJson'];
try {
    // 验证成功会返回加密的 captchaVerification
    $captchaVerification = $service->check($token, $pointJson);
    
    // 将 captchaVerification 返回给前端
    // echo json_encode(['success' => true, 'repData' => ['captchaVerification' => $captchaVerification]]);
} catch (\Exception $e) {
    // 验证失败
}

// --- 二次验证 (业务接口登录/注册时调用) ---
// 前端将上一步获取的 captchaVerification 传给业务接口
$captchaVerification = $_REQUEST['captchaVerification'];
try {
    $service->verificationByEncryptCode($captchaVerification);
    // 验证通过，执行业务逻辑 (登录/注册...)
} catch (\Exception $e) {
    // 二次验证失败，拦截业务请求
}
```

### 2. 框架集成
本库无任何全局变量与单例依赖，完美支持 ThinkPHP, Laravel, Hyperf, Swoole 等现代框架。只需在控制器中实例化 Service 即可。

## ⚙️ 详细配置说明

在 `src/config.php` 中进行配置：

```php
return [
    // --------------------------------------------------------------------
    // 基础配置
    // --------------------------------------------------------------------
    
    // 自定义字体包路径，不填使用默认值 (resources/fonts/WenQuanZhengHei.ttf)
    'font_file' => '', 

    // 水印配置
    'watermark' => [
        'fontsize' => 12,
        'color' => '#ffffff',
        'text' => '我的水印'
    ],

    // --------------------------------------------------------------------
    // 滑动验证码配置 (Block Puzzle)
    // --------------------------------------------------------------------
    'block_puzzle' => [
        // 模式: 'drawing' (推荐, 原生绘图), 'resource' (旧版图片模板)
        'mode' => 'drawing',

        // 形状类型 (仅在 drawing 模式下生效)
        // 可选: 'jigsaw' (拼图), 'red_heart' (红桃), 'spade' (黑桃), 'diamond' (方片), 'club' (草花)
        'shape_type' => 'jigsaw',

        // 开启干扰图 (在 drawing 模式下生成干扰凹槽，在 resource 模式下生成干扰拼图)
        'is_interfere' => true, 
        
        // 容错偏移量 (px)
        'offset' => 10,

        // 背景图路径
        // 支持 string (目录路径) 或 array (文件路径列表)
        // 'backgrounds' => '/path/to/images/', 
        'backgrounds' => [], 

        // 模板图路径 (仅在 resource 模式下生效)
        'templates' => [],

        // 是否开启像素缓存 (仅在 resource 模式下生效，提升性能)
        'is_cache_pixel' => true,
    ],
    
    // --------------------------------------------------------------------
    // 点击验证码配置 (Click Word)
    // --------------------------------------------------------------------
    'click_word' => [
        // 干扰字数量 (混淆视觉，增加破解难度)
        'distract_num' => 2, 
        
        // 目标字数量 (需要点击的文字数量)
        'word_num' => 4,
        
        // Unicode 图标配置：图标字符 => 文字说明
        'icons' => [
            '☎' => '电话',
            '★' => '星星',
            '☀' => '太阳',
            '☂' => '雨伞',
            '☺' => '笑脸',
            '♪' => '音符'
        ],
        
        // 图标模式: 'random'=随机出现, 'always'=每次都有, 'never'=从不出现
        'icon_mode' => 'random',
        
        // 最多图标数量
        'max_icons' => 1,
        
        // 图标字体放大比例（相对于汉字）
        'icon_font_size_scale' => 1.3,
        
        // 背景图路径
        'backgrounds' => [], 
    ],
    
    // --------------------------------------------------------------------
    // 缓存配置
    // --------------------------------------------------------------------
    // 默认使用内置文件缓存 (\Fastknife\Utils\CacheUtils)
    'cache' => [
        'constructor' => \Fastknife\Utils\CacheUtils::class,
        'method' => [
            // 如果您的缓存驱动符合 PSR-16 规范 (如 Laravel, TP6, Hyperf)，则无需配置此项
            // 如果是不兼容的旧框架 (如 TP5)，需在此处做方法映射
            'get' => 'get',      // 获取缓存方法名
            'set' => 'set',      // 设置缓存方法名
            'delete' => 'delete',// 删除缓存方法名 (TP5 为 'rm')
            'has' => 'has'       // 检查存在方法名
        ],
        'options' => [
            'expire' => 300, // 300秒有效期
            'prefix' => '',
            'path' => '', // 缓存目录 (仅内置缓存有效)
        ]
    ]
];
```

### 框架缓存集成详解

默认使用的 `\Fastknife\Utils\CacheUtils` 是一个基于 ThinkPHP 文件缓存改写的轻量级缓存驱动。它会在项目工作目录下自动创建 `runtime/cache` 目录来存储缓存文件。

*   **轻量场景**：如果文件缓存符合您的目录权限要求，且项目单机运行，您完全可以继续使用内置缓存，无需配置。
*   **高性能/分布式**：如果您追求更高的性能（如 Redis）或运行在分布式环境中，强烈建议替换为框架自带的缓存驱动或外部缓存引擎。

#### 1. 方法映射 (适配非 PSR-16 框架)
本库默认期望缓存驱动遵循 PSR-16 规范。如果您的框架缓存方法名不同（例如 ThinkPHP 5.0 使用 `rm` 而不是 `delete`），您需要通过 `method` 配置项进行映射：

```php
// ThinkPHP 5.0 示例
'cache' => [
    'constructor' => [think\Cache::class, 'store'],
    'method' => [
        'get' => 'get',
        'set' => 'set',
        'delete' => 'rm', // TP5 删除缓存的方法是 rm
        'has' => 'has'
    ],
    // ...
]
```

#### 2. 常用框架配置示例

**Laravel / Lumen**:
```php
'cache' => [
    'constructor' => [Illuminate\Support\Facades\Cache::class, 'store'],
    // Laravel 符合 PSR-16，无需配置 method
]
```

**ThinkPHP 6**:
```php
'cache' => [
    'constructor' => [think\Facade\Cache::class, 'instance'],
    // TP6 符合 PSR-16，无需配置 method
]
```

**Hyperf**:
```php
'cache' => [
    'constructor' => function () {
        return \Hyperf\Utils\ApplicationContext::getContainer()->get(\Psr\SimpleCache\CacheInterface::class);
    },
]
```

## 💻 前端集成注意事项

前端请求时，请确保 `Content-Type` 设置为 `application/x-www-form-urlencoded`。

**Axios 示例**:
```javascript
import axios from 'axios';
import qs from 'qs';

axios.defaults.baseURL = 'https://your-api.com/captcha-api';

const service = axios.create({
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
})

service.interceptors.request.use(config => {
    if (config.data) {
        config.data = qs.stringify(config.data)
    }
    return config
})
```

## 🏗 架构设计

v2.0 引入了**策略模式 (Strategy Pattern)**，将滑块的生成逻辑解耦。

### 核心目录结构

```
src/
├── Domain/
│   ├── Template/                   # 策略层
│   │   ├── TemplateProviderInterface.php  # 策略接口
│   │   ├── DrawingTemplateProvider.php    # 策略A: 原生绘图 (抗锯齿核心)
│   │   ├── ResourceTemplateProvider.php   # 策略B: 图片资源 (兼容旧版)
│   │   │
│   │   └── Shape/                  # 形状绘制子策略
│   │       ├── ShapeDrawerInterface.php
│   │       ├── ShapeFactory.php           # 工厂模式
│   │       ├── JigsawShapeDrawer.php      # 拼图形状
│   │       ├── RedHeartShapeDrawer.php    # 红桃形状
│   │       ├── SpadeShapeDrawer.php       # 黑桃形状
│   │       ├── DiamondShapeDrawer.php     # 方片形状
│   │       └── ClubShapeDrawer.php        # 草花形状
│   │
│   └── Logic/                      # 业务逻辑层
│       ├── BlockImage.php          # 滑动验证码合成逻辑 (Alpha混合, 挖槽)
│       ├── WordImage.php           # 点击验证码图像处理 (新增drawWordList支持Unicode图标)
│       └── ...
│
├── Utils/
│   └── ImageUtils.php              # 基础设施层: GD 库底层封装 (屏蔽 PHP 版本差异)
└── ...
```

### 绘图原理 (Drawing Mode)

1.  **大画布绘制**：系统首先创建一个 6 倍于目标尺寸的透明画布。
2.  **矢量路径**：使用 `imagefilledpolygon` 等函数在画布上绘制高精度的矢量形状（拼图、心形等）。
3.  **光影渲染**：在形状内部绘制半透明的内阴影、外发光，模拟立体感。
4.  **高斯模糊**：应用轻微的高斯模糊 (`IMG_FILTER_GAUSSIAN_BLUR`) 柔化边缘。
5.  **下采样**：使用 `imagecopyresampled` 将大图缩放回原尺寸，生成高质量的 Alpha Mask。
6.  **混合渲染**：利用 Alpha Blending 将 Mask 与背景图混合，实现完美的抠图效果。



## 🧪 开发环境快速启动

在本仓库中已内置 `test/` 目录作为本地联调与演示入口，使用 PHP 内置开发服务器即可快速启动。

### 准备

- 安装依赖：在项目根目录执行
```bash
composer install
```
- 确认 PHP 环境满足要求：`PHP >= 7.1`，扩展 `ext-gd`、`ext-openssl` 可用

### 启动服务

- 在项目根目录运行（以 8001 端口为例）：
```bash
php -S localhost:8001 -t ./test
```
- 浏览器访问：
  - 前端演示页：`http://localhost:8001/index.html`
  - 滑动/点击验证码图像直出调试：`http://localhost:8001/testImage.php`（点击验证码：加上 `?mode=word`）
  - 当前渲染尺寸检查：`http://localhost:8001/inspectSizes.php`

### 本地接口说明

`test/` 目录已提供最小后端接口，前端 `index.html` 的交互将直接请求这些接口：
- 获取验证码：`GET /get.php?captchaType=blockPuzzle` 或 `GET /get.php?captchaType=clickWord`（f:\php-code\aj-captcha\test\get.php:1）
- 一次验证：`POST /check.php` 表单参数 `captchaType`、`token`、`pointJson`（f:\php-code\aj-captcha\test\check.php:1）
- 二次验证：`POST /verification.php`
  - 方式一：参数 `captchaVerification`（推荐，来自一次验证返回值）（f:\php-code\aj-captcha\test\BlockPuzzleController.php:60）
  - 方式二：参数 `token` + `pointJson`（f:\php-code\aj-captcha\test\BlockPuzzleController.php:63）

对应控制器实现可参考：
- 滑动验证码控制器：`test/BlockPuzzleController.php`（f:\php-code\aj-captcha\test\BlockPuzzleController.php:7）
- 点击验证码控制器：`test/ClickWordController.php`（f:\php-code\aj-captcha\test\ClickWordController.php:9）

### 调试配置

开发时可直接修改 `src/config.php` 来观察不同效果：
- 切换绘制模式：`block_puzzle.mode = 'drawing' | 'resource'`（原生绘图/旧版图片模板）
- 形状类型：`block_puzzle.shape_type = 'jigsaw' | 'red_heart' | 'spade' | 'diamond' | 'club'`
- 干扰开关：`block_puzzle.is_interfere = true | false`
- 点击验证码干扰字与目标字数量：`click_word.distract_num`、`click_word.word_num`
- Unicode图标配置：`click_word.icons`、`click_word.icon_mode`、`click_word.max_icons`

### 常见问题
- 图片不显示或异常：确认 `ext-gd` 已启用；Windows 下可在 `php.ini` 中开启 `extension=gd`。
- 接口 404：确认以 `-t ./test` 作为站点根目录启动，且访问路径与上述接口一致。


## 📝 变更日志

查看 [CHANGELOG.md](./CHANGELOG.md)

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！如果你想添加新的滑块形状，只需实现 `ShapeDrawerInterface` 并在 `ShapeFactory` 中注册即可。

## 📄 License

MIT
