# 网络与媒体工具类使用说明（Http / DownloadImage / Canvas / Captcha）

> 源码均在 `backend/crmeb/utils/`。覆盖：外部 HTTP 请求、远程图片下载入库、GD 海报合成、图形验证码。

---

## 一、Http curl 请求类

源码：`backend/crmeb/utils/Http.php`（全静态方法，轻量 curl 封装）。

### 1.1 API

```php
use crmeb\utils\Http;

Http::getRequest($url, $data = [], $header = false, $timeout = 10);   // GET
Http::postRequest($url, $data = [], $header = false, $timeout = 10);  // POST
Http::request($url, 'get'|'post', $data, $header, $timeout = 15);     // 通用

Http::getCurlError();   // curl 错误信息
Http::getStatus();      // HTTP 状态码
Http::getHeaderStr();   // 响应头字符串
Http::getHeader();      // 响应头数组
```

- 成功返回 body（header 已剥离）；非 200 返回 `false`
- `$header` 可传布尔（是否携带/返回头）或自定义 header
- 固定 HTTP/1.1；**失败判断用 `=== false`** 并配合 `getCurlError()` 排错

### 1.2 使用示例（真实调用 `backend/app/services/system/UpgradeServices.php:136`）

```php
use crmeb\utils\Http;

$res = Http::getRequest('https://api.example.com/v1/query', ['page' => 1]);
if ($res === false) {
    throw new ValidateException('接口请求失败：' . Http::getCurlError());
}
$data = json_decode($res, true);
```

> 复杂场景（重试/证书/并发）建议用 Guzzle；Http 适合轻量一次性和系统内部对接（升级中心）。

---

## 二、DownloadImage 远程图片下载类

源码：`backend/crmeb/utils/DownloadImage.php`。下载远程图片/文件到本地 uploads（经 UploadService 存储，按年/月/日分目录），`__call` 魔法链式配置。

### 2.1 API

```php
use crmeb\utils\DownloadImage;

/** @var DownloadImage $download */
$download = app()->make(DownloadImage::class);

// 链式配置（可选）
$download->thumb(true)->thumbWidth(300)->thumHeight(300)->path('attach');

// 下载
$res = $download->downloadImage($url, $name = '');
// 返回 ['path' => 相对路径, 'name' => 文件名, 'size' => 大小, 'mime' => 类型, ...]，失败抛异常/返回空

// 解析远程图片扩展名（md5 命名）
[$filename, $ext] = $download->getImageExtname($url, 'jpg');
```

| 链式方法 | 说明 |
|----------|------|
| `->thumb(true)` | 是否生成缩略图 |
| `->thumbWidth(n)` / `->thumHeight(n)` | 缩略图尺寸 |
| `->path('attach')` | 存储位置（对应 uploads 子目录） |

### 2.2 使用示例（真实调用 `backend/modules/channel/wechat_mini/service/LiveGoodsServices.php:81`）

```php
$download = app()->make(DownloadImage::class);
foreach ($goodsList as &$goods) {
    if ($goods['cover_img'] && strpos($goods['cover_img'], 'http') === 0) {
        $res = $download->downloadImage($goods['cover_img']);
        $goods['cover_img'] = $res['path'] ?? $goods['cover_img'];   // 失败保留原地址
    }
}
```

> 典型场景：直播商品/第三方同步数据时把外链图片本地化；替换微信临时素材链接。

---

## 三、Canvas GD 海报合成类

源码：`backend/crmeb/utils/Canvas.php`。基于 GD 库把多张图片 + 多段文字合成一张图（邀请海报、线下收款码），保存到 uploads 并返回相对路径。`__call` 魔法提供全部 `set*` 配置方法。

### 3.1 使用流程

```php
use crmeb\utils\Canvas;

$canvas = Canvas::instance();
// ① 画布
$canvas->createTrueColor(750, 1334);
// ② 配置一个元素（背景图），push 压入绘制队列
$canvas->setBackgroundImage($bgPath)->setBackgroundWidth(750)->setBackgroundHeight(1334)
    ->pushImageValue();
// ③ 再配置下一个元素（文字），push 压入
$canvas->setFontText('邀请码：ABC123')->setFontSize(28)->setFontColor('#333333')
    ->setFontLeft(40)->setFontTop(120)->pushFontValue();
// ④ 头像/二维码图片同理 setImage* + pushImageValue()
// ⑤ 绘制保存
$path = $canvas->starDrawChart();          // 返回相对路径；出错默认返回空串
$path = $canvas->starDrawChart(true);      // $force = true 时出错抛异常
```

### 3.2 要点

- **一组 `set*` 配置 + 一次 `pushImageValue()`/`pushFontValue()` = 绘制一个元素**，顺序即图层顺序
- 全部可用 `set*` 方法见类头 `@method` 注释（`setBackground*`/`setImage*`/`setFont*` 系列）
- `createFrom($file)` 按 png/jpg/gif 类型创建图像资源；`createTrueColor($w, $h)` 建真彩画布
- 失败处理：`starDrawChart()` 默认返回空串（调用方需判空），传 `true` 则抛异常
- 典型场景：分销邀请海报、线下收款码（`backend/app/adminapi/controller/user/member/OfflineReceiptController.php:73`）

---

## 四、Captcha 图形验证码

源码：`backend/crmeb/utils/Captcha.php`。生成 PNG 验证码（think Response 输出），答案 bcrypt 哈希入缓存，**一次性使用**（验证通过即删缓存）。

### 4.1 API

```php
use crmeb\utils\Captcha;

// 生成并输出（真实调用 backend/app/adminapi/controller/LoginController.php:53）
return app()->make(Captcha::class)->create();

// 自定义配置
$captcha = new Captcha([
    'length' => 4,          // 位数
    'width'  => 130, 'height' => 40,
    'zhCap'  => false,      // 中文验证码
    'expire' => 1800,       // 过期秒数，默认 1800
]);
$res = $captcha->generate();   // ['value' => 明文, 'key' => bcrypt hash]（同时写入缓存）
$ok  = $captcha->check($code); // 校验，通过即删除（一次性）
```

### 4.2 要点

- 校验需同时比对缓存 key 与输入码，业务侧配合 `CacheService` 的验证码缓存约定使用
- 前端通过 `<img src="接口地址">` 直接展示，接口需返回图片流（`create()` 已处理）
