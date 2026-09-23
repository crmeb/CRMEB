# 基础工具类使用说明（Json / Arr / Str / File / JwtAuth / Hook）

> 源码均在 `backend/crmeb/utils/`。覆盖：JSON 响应、数组/字符串处理、文件操作、JWT 鉴权、钩子分发。

---

## 一、Json 统一响应类

源码：`backend/crmeb/utils/Json.php`。统一接口返回结构 `{status, msg, data}`，**不直接 new**，经容器别名 `app('json')` 或全局帮助函数使用。

### 1.1 标准用法

```php
// 全局帮助函数（backend/app/common.php 内部即 app('json')->success/fail）
return success('操作成功', ['id' => 1]);      // status=200
return fail('操作失败');                       // status=400

// 容器别名链式
return app('json')->code(200)->success('成功', $data)->to();
return app('json')->fail('参数有误');
```

### 1.2 方法清单

| 方法 | 说明 |
|------|------|
| `make($status, $msg, ?array $data = null, ?array $replace = [])` | 构造 Response；`$replace` 支持 msg 多语言占位替换 |
| `success($msg, ?array $data)` / `fail($msg, ?array $data)` | 快捷 200/400 |
| `status($status, $msg, $result = [])` | 业务状态塞进 data |
| `code(int $code)` / `header(array $header)` | 链式设置 HTTP 状态码/响应头 |

> 容器绑定见 `backend/app/AppService.php:17`（`'json' => \crmeb\utils\Json::class`）。

---

## 二、Arr 数组帮助类

源码：`backend/crmeb/utils/Arr.php`（全静态）。高频方法：

```php
use crmeb\utils\Arr;

Arr::getTree($data, $pid = 0, $level = 1);        // 平铺数据转树（按 sort 倒序）
Arr::getMenuIviewList($data);                      // 菜单树 + iView 字段转换（后台菜单）
Arr::toIviewUi($data);                             // 菜单数据转 iView key（id/pid/path/title/icon）
Arr::getDefaultValue(array $keys, array $configList = []);  // 按 key 列表生成带默认值数组（系统配置常用）
Arr::getArrayFilterValeu(array $data, string $field);       // 提取二维数组某列，去重并过滤假值
Arr::setValeTime(array $array, $value, $default = 0);       // 补齐缺失键并 float 转换
Arr::unique($data) / getUniqueKey($list, $key);    // 去重
Arr::getArrayRandKey($data);                       // 随机取元素
```

真实先例：菜单生成 `backend/app/services/system/SystemMenusServices.php:82`；系统配置默认值 `backend/app/common.php:178`。

> 注意：方法名存在历史拼写（`getArrayFilterValeu`/`setValeTime`），以源码为准。

---

## 三、Str 字符串帮助类

源码：`backend/crmeb/utils/Str.php`（全静态）。

```php
use crmeb\utils\Str;

Str::getAuthName($action, $controller, $module, $route);  // 权限规则标识（module/controller/action 小写拼接）
Str::paramStr($params);       // 参数数组扁平化为 key1/value1/key2/value2（路由 pathinfo）
Str::substrUTf8($str, 100);   // 按字符数截取 + '....'（中文安全），默认 100 字符
```

真实先例：支付 body 截断 `backend/app/services/pay/OrderPayServices.php:86`；小票打印截断 `backend/app/jobs/notice/PrintJob.php:58`。

---

## 四、File 文件操作类

源码：`backend/crmeb/utils/File.php`。目录/文件全操作集合，**二开常用点：Excel 导入读取**。

### 4.1 常用方法

| 分类 | 方法 |
|------|------|
| 目录 | `mkDir` / `createDir`（递归建目录）、`delDir` / `del_where_dir`（按修改时间条件删）、`copyDir` / `removeDir`、`getDirs` / `getDirInfo`、`emptyDir` / `isEmpty` / `getSize` |
| 文件 | `writeFile` / `readFile` / `createFile` / `unlinkFile`、`handleFile` / `handleDir`（复制/移动）、`rename`、`getExt` / `getBasename`、`setFileName`（hash/time/日期命名） |
| 网络/压缩 | `downRemoteFile`（远程文件下载）、`addZip` / `zipOpen` / `extractFile` |
| Excel | `readExcel($file, $field = [])`：PhpSpreadsheet 读取（商品/卡密/运单导入模板） |
| 其他 | `byteFormat`（字节格式化）、`allowUploadSize`、`batchMakeFiles`（代码生成器用） |

### 4.2 Excel 读取示例

```php
use crmeb\utils\File;

$rows = (new File())->readExcel($excelFile, ['product_name', 'price', 'stock']);
// $field 传空则返回全列数组；内部按首行表头映射
```

真实先例：升级中心 `backend/app/services/system/UpgradeServices.php:21`、CRUD 代码生成 `backend/app/services/system/SystemCrudServices.php:30`。

---

## 五、JwtAuth JWT 鉴权类

源码：`backend/crmeb/utils/JwtAuth.php`。HS256，密钥取 `app.app_key`，默认 30 天有效。**常规二开一般不直接使用**（登录鉴权由系统 AuthTokenVerifier 中间件链处理），扩展自定义登录渠道时才接触。

```php
use crmeb\utils\JwtAuth;

$jwtAuth = app()->make(JwtAuth::class);
$token   = $jwtAuth->createToken($uid, 'user', ['extra' => 1]);  // 签发 + 写入缓存令牌桶（按 type 分缓存）
$token   = $jwtAuth->getToken($uid, 'user');                     // 仅签发（不写缓存）
[$id, $type, $pwd] = $jwtAuth->parseToken($jwt);                 // 不验签快速解析
$jwtAuth->verifyToken();                                          // 验签（leeway 60 秒）
```

真实先例：登录签发 `backend/app/services/user/LoginServices.php:58`；校验基础 `backend/crmeb/auth/AuthTokenVerifier.php:57`；开放接口鉴权 `backend/modules/service/outapi/service/OutAccountServices.php:95`。

---

## 六、Hook 钩子分发器

源码：`backend/crmeb/utils/Hook.php`。把钩子名映射为指定类的「前缀 + 大驼峰方法」并调用，**支付回调按 payType 分发**的标准机制。

### 6.1 API

```php
use crmeb\utils\Hook;

// 构造：挂载目标类名 + 方法前缀
$hook = new Hook(PayNotifyServices::class, 'wechat');
// listen('routines', ...) → 调用 PayNotifyServices::wechatRoutines(...)
$result = $hook->listen($notify['attach'], $notify);
// 方法不存在或执行异常返回 false（静默降级）
```

### 6.2 真实先例

支付回调监听器（`backend/app/listener/pay/NotifyListener.php:36,48`）：

```php
// $notify['attach'] 携带支付类型（wechat/alipay/...），据此分发到对应处理方法
(new Hook(PayNotifyServices::class, 'wechat'))->listen($notify['attach'], $notify);
```

> 自定义支付驱动回调扩展：实现同名「前缀 + 大驼峰」方法即可被分发，无需改监听器。
