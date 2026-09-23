# 系统工具类总览（backend/crmeb/utils/）

> `crmeb/utils/` 目录共 18 个工具类，全部为底层公共能力，**不依赖 app/modules 层**（符合三层依赖铁律：app → crmeb → driver）。二开优先复用本目录工具类，禁止重复造轮子。
>
> 详细用法分文档：
> - 表单构建 → [form-builder.md](form-builder.md)
> - **全局函数（sys_config/get_thumb_water/toIntArray 等）→ [helper-functions.md](helper-functions.md)**
> - 异步/并发（Queue/Lock/单号）→ [async-tools.md](async-tools.md)
> - 网络与媒体（Http/DownloadImage/Canvas/Captcha）→ [http-tools.md](http-tools.md)
> - 基础工具（Json/Arr/Str/File/JwtAuth/Hook）→ [basic-tools.md](basic-tools.md)

## 一、按使用频率速查

### 高频（二开必用）

| 类 | 用途 | 一句话用法 | 详解 |
|----|------|-----------|------|
| FormBuilder | form-create 表单构建器 | `Form::input('name','名称','')->required()` + `create_form()` | [form-builder.md](form-builder.md) |
| QueueTrait / Queue | 消息队列投递 | `XxxJob::dispatch('doJob', [$id])`，队列未开自动降级同步 | [async-tools.md](async-tools.md#一queue-消息队列投递) |
| CacheService::lock / Lock | Redis 分布式锁 | `CacheService::lock('key', fn, 6)`，file 缓存时直接执行 | [async-tools.md](async-tools.md#二lock-redis-分布式锁) |
| SnowflakeIdGenerator | 唯一单号 | `SnowflakeIdGenerator::generate('wx')`，禁止 time+rand 拼单号 | [async-tools.md](async-tools.md#三snowflakeidgenerator-唯一单号生成) |
| success()/fail() / Json | 统一 JSON 响应 | `return success('成功', $data); return fail('失败');` | [basic-tools.md](basic-tools.md#一json-统一响应类) |
| Http | curl 请求 | `Http::getRequest($url, $data)`，非 200 返回 false | [http-tools.md](http-tools.md#一http-curl-请求类) |
| DownloadImage | 远程图片本地化 | `app()->make(DownloadImage::class)->downloadImage($url)` | [http-tools.md](http-tools.md#二downloadimage-远程图片下载类) |

### 中频（按场景使用）

| 类 | 用途 | 一句话用法 |
|----|------|-----------|
| Canvas | GD 海报合成 | `Canvas::instance()->createTrueColor()` + set*/push* 链式逐元素绘制 |
| Captcha | 图形验证码 | `app()->make(Captcha::class)->create()`；校验一次性 |
| File | 文件/目录/Excel | `(new File())->readExcel($file, $fields)` 导入读取 |
| Arr | 数组帮助 | `Arr::getTree()` 树化、`Arr::getDefaultValue()` 配置默认值 |
| Str | 字符串帮助 | `Str::substrUTf8($str, 100)` 中文安全截断 |
| JwtAuth | JWT 签发/校验 | 自定义登录渠道时用；常规登录由系统中间件处理 |
| Hook | 钩子分发 | `new Hook(类, 'wechat')->listen($hookName)`，支付回调按 payType 分发 |

### 低频（系统内部使用，二开一般不接触）

| 类 | 用途 | 系统内用途 |
|----|------|-----------|
| Rsa | RSA 分段加解密 | 底层预留能力，业务代码暂无调用点；密钥文件不存在时自动生成 |
| Terminal | proc_open 执行命令 | npm 构建（`crmeb/command/Npm.php`），输出写 runtime/terminal/exec.log |
| Translate | 火山引擎翻译 | 多语言管理（`LangCodeServices`）与异步翻译任务 |
| fileVerification | 项目文件签名 | 升级中心完整性校验（app + crmeb 目录 md5 聚合） |

## 二、通用规范

- **引用方式**：`use crmeb\utils\Xxx;`，多数单例类经容器 `app()->make(Xxx::class)` 获取
- **命名空间铁律**：utils 位于 `crmeb/` 层，app 与 modules 均可引用；utils 内**禁止**引用 app/modules 类
- **静态/实例差异**：`Arr`/`Str`/`Http`/`SnowflakeIdGenerator` 全静态；`Queue`/`Canvas` 单例 `instance()`；`Lock`/`File`/`DownloadImage`/`Captcha` 容器实例化；`Json` 走容器别名 `app('json')`
- **降级行为需注意**：
  - `QueueTrait::dispatch()` 队列未开 → 同步执行；`dispatchSecs()` 队列未开 → **静默丢弃**
  - `CacheService::lock()` file 缓存 → 不加锁直接执行
  - `Hook::listen()` 方法不存在/异常 → 返回 false 静默
- **数据库规范不受工具类影响**：一律走 DAO 层，禁止 `Db::name()`

## 三、低频类速查 API

```php
use crmeb\utils\{Rsa, Terminal, Translate, fileVerification};

// Rsa（构造时密钥文件不存在自动生成 1024 位密钥对）
$rsa = new Rsa();                                   // 默认 cert_public_password.key / cert_private_password.key
$enc = $rsa->privateEncrypt($data);                 // 私钥分段加密（100 字节/段，base64）
$dec = $rsa->publicDecrypt($enc);                   // 公钥解密

// Terminal（npm 命令执行）
$terminal = new Terminal();
$terminal->setOutput($output)->run('npm-build');    // 需 proc_open 扩展

// Translate（火山引擎 V4Curl，需配置密钥）
Translate::make()->langDetect($textList);           // 语种识别
Translate::make()->translateText('zh', 'en', $textList);  // 批量翻译

// fileVerification（升级校验）
$signature = (new fileVerification())->getSignature($rootPath);  // app+crmeb 目录聚合 md5
```
