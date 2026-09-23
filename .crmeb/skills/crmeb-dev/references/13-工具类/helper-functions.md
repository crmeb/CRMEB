# 系统全局函数说明（backend/app/common.php）

> `common.php` 定义全局帮助函数，**app/api/adminapi 全局可用**（ThinkPHP 自动加载，无需 use）。`app/adminapi/common.php` 另有 5 个后台专用函数。表单函数 `create_form()` 详见 [form-builder.md](form-builder.md)。

## 一、系统配置与数据（二开高频）

```php
// 单个配置：带缓存；纯数字自动转 int/float；含 /uploads/system/ 的路径自动补全域名
sys_config(string $name, $default = '');

// 批量配置：缺失的 key 自动补默认值
sys_config_more(['store_brokerage_statu', 'store_brokerage_price']);

// 组合数据（eb_system_group）：带缓存；$isCaChe=true 跳过缓存直接查库
sys_data(string $name, int $limit = 0, bool $isCaChe = false);
```

> 修改配置走后台/`SystemConfigServices`，配置缓存 key 为 `system_config_{name}`，改库后需清缓存。

## 二、URL 与图片路径处理（高频）

| 函数 | 说明 |
|------|------|
| `set_file_url($image, $siteUrl = '')` | 相对路径补全站点域名（`site_url` 配置）；字符串/数组均可；已带 http// 开头的不处理 |
| `set_http_type($url, $type = 0)` | http/https 互转：`0`→https，`1`→http；非完整 URL 原样返回 |
| `get_file_link(string $link)` | 补全为带当前请求 domain 的完整路径；已是 http 开头原样返回 |
| `make_path($path, $type = 2, $force = false)` | 生成 `public/uploads` 下上传目录：`$type` 1=按年/2=按年月/3=按年月日，自动 mkdir，失败返回 ''（`$force=true` 抛异常） |
| `url_to_path($url)` / `path_to_url($path)` | URL ↔ 磁盘绝对路径互转（基于项目根目录 public） |
| `curl_file_exist($url)` | 远程文件是否存在（404/403 视为不存在） |
| `get_image_thumb($filePath, $type = 'all', $is_remote_down = false)` | 单图缩略图地址（走 UploadService 驱动）；`$type`：all/big/mid/small |
| `get_thumb_water($list, $type = 'small', $field = ['image'], $is_remote_down = false)` | **列表批量缩略图**；`$field` 索引数组用统一 `$type`，关联数组 `[ 'mid' => 'images' ]` 指定字段不同尺寸；驱动未开缩略图直接原样返回 |
| `image_to_base64($avatar, $timeout = 9)` | 图片转 base64（本站文件直读，外站经 curl；含内网 IP 防护） |
| `put_image($url, $filename = '')` | 下载图片存到 `uploads/qrcode/`，仅支持 jpg/jpeg/png，成功返回路径 |

**列表出图标准写法**（商品列表等真实先例）：

```php
$list = get_thumb_water($list, 'small', ['image', 'slider_image']);
// 二维列表：把 image（单图）与 slider_image（图集）字段全部替换为 small 缩略图
```

## 三、验证类

| 函数 | 说明 |
|------|------|
| `check_card($card)` | 身份证校验（18 位含末位校验码、省份码），通过 true |
| `check_phone($phone)` | 手机号 `/^1[3456789]\d{9}$/` |
| `check_link(string $link)` | http/https/ftp 链接格式 |
| `verify_domain(string $domain)` | 域名合法性（含 http(s) 前缀）★后台专用 common.php |

## 四、字符串/数组处理

| 函数 | 说明 |
|------|------|
| `toIntArray($data, $separator = ',')` | **ids 处理高频**：`"1,2,3"` 或数组 → 去重、去 0 的 int 数组 |
| `array_bc_sum(array $data)` | bcadd 高精度求和（保留 2 位），**金额计算必用**，禁用 `array_sum` 浮点 |
| `filter_emoji($str)` | 过滤 emoji 表情（4 字节字符） |
| `sensitive_words_filter($str)` | 敏感词检测，返回命中的词；空串=通过（词库 app/public/statics/plug/censorwords/） |
| `anonymity($name, $type = 1)` | 昵称脱敏：`1`=首+`**`+尾；`2`=不足 3 位处理 |
| `sql_filter(string $str)` | 剥离 select/insert/drop 等 SQL 关键词 |
| `filter_str($str)` | 参数敏感字符过滤（配置 `param_filter_type`：1=命中直接抛异常） |
| `array_unique_fb($array)` | 二维数组去重 |
| `object2array($object)` | 对象转数组 |
| `str_middle_replace($string, $start, $end)` | 中间打星（源码注明"系统未使用"，慎用） |

## 五、树结构处理

```php
// 平铺转树（引用传值，性能好），自定义字段名
get_tree_children(array $data, string $childrenname = 'children', string $keyName = 'id', string $pidName = 'pid');

// 平铺转"分级排序+缩进前缀"列表（下拉选择用），html 默认 '|-----'
sort_list_tier($data, $pid = 0, $field = 'pid', $pk = 'id', $html = '|-----', $level = 1, $clear = true);

// 树转 children+expand 结构（后台菜单/分类树）
tidy_tree($menusList, $pid = 0, $navList = []);

// 城市数据树（parent_id/v 结构，转 c 嵌套）
sort_city_tier($data, $pid = 0, $navList = []);

// 树取值链：取某值的父级/子级 value 集合（级联选择回显）
get_tree_value(array $data, $value);            // 向上取父链
get_tree_children_value(array $data, $value);   // 取匹配节点的 value 集
```

## 六、调试与日志

```php
crmebLog($msg);                                    // 写 'crmeb' 通道日志（runtime/log）
debug_file($content, $fileName = 'error', $ext = 'txt');  // 追加写 runtime/{fileName}.{ext} 调试文件
dump_sql();                                        // 监听并 var_dump 全部 SQL（仅调试环境）
exception($msg, $code = 0, $exception = '');       // 快捷抛异常，默认 \think\Exception
```

## 七、业务辅助

| 函数 | 说明 |
|------|------|
| `success($msg, ?array $data, ?array $replace)` / `fail(...)` | 统一 JSON 响应（status 200/400），见 [basic-tools.md](basic-tools.md) |
| `getLang($msg, array $replace = [])` | 多语言翻译：中文标识→当前语言文本；语言取请求头 `cb-lang` → 系统默认 → Accept-Language → zh-CN；支持 `{:name}` 变量替换；异常兜底返回原文 |
| `msectime(): float` | 毫秒时间戳 |
| `time_tran($time)` | 时间戳人性化："3天前" |
| `passwordHash(string $password)` | bcrypt 密码哈希 |
| `getWorkerManUrl(): array` | 长连接统一网关：`['admin' => 'wss://域名/ws?service=admin', 'chat' => '...kefu_chat']` |
| `is_brokerage_statu(float $price): bool` | 按分销配置判断该金额/用户能否成为推广人 |
| `yihaotong_config(array $extra = []): array` | 一号通账号配置（快递面单/发票/复制商品等扩展用） |
| `upload_type(): int` | **当前启用上传驱动的 image_type 值**（v8.1 从 eb_driver 表取，已替代旧 `sys_config('upload_type')`），写入素材库 image_type 字段时用 |
| `out_push(string $pushUrl, array $data, string $tip = ''): bool` | JSON POST 推送到外部地址，code!=0 记 error 日志 |
| `proxy_event_result($results, $default = null)` | **多模块监听同一事件**时提取第一个非 null 返回值（event() 默认返回数组） |
| `aj_captcha_create(string $captchaType)` / `aj_captcha_check_one($type, $token, $pointJson)` / `aj_captcha_check_two($type, $captchaVerification)` | 滑块/点选验证码（aj-captcha）：创建、一次校验、二次校验；`$captchaType`：blockPuzzle/clickWord |

## 八、adminapi 专用函数（backend/app/adminapi/common.php）

仅后台模块加载：

| 函数 | 说明 |
|------|------|
| `attr_format($arr): array` | **商品 SKU 规格笛卡尔积组合**：属性组 → 全部组合值 + 属性名列表 |
| `get_this_class_methods($class, $unarray = [])` | 获取类自身方法（剔除父类与排除项） |
| `setconfig($name, $pat, $rep)` | 正则改写 config/{name}.php 文件值（**危险操作**，不能改数组值，谨慎使用） |
| `arrayToText($array)` | 数组转 PHP 配置文件文本 |

`attr_format` 用法示例：

```php
// 入参：每个属性组含 value（属性名）与 detail（属性值数组，元素为字符串或含 value 键的数组）
[$combo, $attrs] = attr_format([
    ['value' => '颜色', 'detail' => ['红', '蓝']],
    ['value' => '尺码', 'detail' => ['S', 'M']],
]);
// $combo = ['红,S', '红,M', '蓝,S', '蓝,M']（SKU 组合）
// $attrs = ['颜色', '尺码']（属性名列表）
```

## 九、使用注意

- 所有函数均有 `function_exists` 守卫，插件内**可定义同名函数覆盖**（不建议，易冲突）
- `sys_config`/`sys_data` 走缓存（CacheService::remember），单元测试/队列中注意缓存一致性
- 金额运算统一 `array_bc_sum`/`bcadd`，金额比较用 `bccomp`，禁浮点直算
- 树函数对字段名有约定（id/pid/parent_id/value），传入前先确认数据结构
- `get_thumb_water` 依赖驱动配置（eb_driver.thumb_watermark_config），未开启时零开销原样返回
