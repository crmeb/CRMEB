# aj-captcha迭代文档

## v2.1.0 2025-12-30
### 新增特性
* **Unicode 图标验证码**: 点击验证码新增 Unicode 图标支持，实现图文混合验证效果。用户点击图标（如 ☕）时，前端提示显示文字说明（如 `<杯子>`），提升验证码趣味性和防机器识别能力。
* **双列表分离机制**: 采用绘制列表和显示列表分离设计，图片上绘制 Unicode 图标，前端显示对应文字说明，无需修改前端代码。
* **灵活配置**: 支持通过 `click_word.icons` 配置图标映射关系，`icon_mode` 控制图标出现策略（随机/始终/从不），`max_icons` 限制图标数量。
* **图标字体优化**: 图标字符支持字体放大比例（默认1.3倍），提升视觉辨识度。

### 变更优化
* **配置增强**: `config.php` 新增 `click_word.icons`、`click_word.icon_mode`、`click_word.icon_font_size_scale` 等配置项。
* **架构优化**: `WordImage` 类新增 `drawWordList` 属性支持双列表机制，`Factory` 类实现图标替换逻辑。
* **兼容性保障**: 保持与前端代码完全兼容，配置为空时自动回退到纯汉字模式。


## v2.0.0 2025-12-18
### 新增特性
* **原生绘图模式 (Drawing Mode)**: 新增 `DrawingTemplateProvider`，支持实时生成滑块，彻底解决边缘锯齿问题。
* **多形状支持**: 滑块形状现在支持 `jigsaw` (拼图), `red_heart` (红桃), `spade` (黑桃), `diamond` (方片), `club` (草花) 等多种样式。
* **点击验证码增强**: 新增 `distract_num` 配置，支持生成干扰字；新增智能碰撞检测算法，防止文字重叠。
* **策略模式架构**: 引入 `TemplateProviderInterface` 和 `ShapeDrawerInterface`，解耦模板生成逻辑，便于扩展。
* **兼容性**: 完美兼容 PHP 8.0+ 及即将到来的 PHP 8.5 (废弃 `imagedestroy` 处理)。

### 变更优化
* **依赖移除**: 彻底移除 `intervention/image` 依赖，完全基于原生 GD 库实现，体积更小，性能更高。
* **配置结构**: `config.php` 新增 `block_puzzle.mode` 和 `block_puzzle.shape_type` 配置项。
* **底层优化**: 重构 `ImageUtils`，统一封装 GD 操作，屏蔽 PHP 版本差异。
* **视觉优化**: 滑块自带内阴影和半透明质感，背景凹槽采用动态变暗逻辑，视觉效果更自然。


## v1.1.2 2021-12-15
* server 增加`verificationByEncryptCode` 方法，兼容前端二次验证`captchaVerification`值

## v1.1.1 2021-12-13
* 去除多余代码、注释

## v1.1.0 2021-12-9
* 重构工厂类与领域层
* 永久缓存图片像素值，增加响应性能

## v1.0.7 2021-11-24
* 增强缓存冗错
* 自定义缓存配置

## v1.0.3 2021-10-11
* 新增二次验证


## v1.0.0  2021-7-16
* 初步实现滑动验证码与文字点选验证码





