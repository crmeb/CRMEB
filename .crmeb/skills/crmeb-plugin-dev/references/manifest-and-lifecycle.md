# 插件清单与生命周期（业务模块插件）

> 本文覆盖清单与身份等**所有插件共用**的约定，以及业务模块插件（`backend/modules/`）的 Installer、菜单与 SQL 去重契约；第三方驱动插件（`backend/driver/`）的专属内容见 [driver-plugin.md](driver-plugin.md)。

## 发现与身份

`crmeb/plugin/PluginManifestRepository.php` 扫描：

```text
backend/modules/*/plugin.json
backend/modules/*/*/plugin.json
backend/modules/*/*/*/plugin.json
backend/driver/*/*/plugin.json
```

每个清单必须包含非空字符串字段：

```json
{
  "key": "activity_example",
  "name": "示例活动",
  "version": "1.0.0",
  "type": "marketing",
  "path": "modules/marketing/example"
}
```

规范化后的 `path` 不得包含 `..`，必须以 `modules/` 或 `driver/` 开头，并且解析结果必须与清单实际目录一致。

Canonical ID 用于避免相同 key 冲突：

- 分组模块：`module:<category>:<directory-name>`
- 扁平模块：`module:<category>:<manifest-key>`
- 驱动：`driver:<category>:<driver.driver_key>`

只有不存在歧义时，旧 key 才能继续使用。新的管理代码和需要区分插件的错误信息应使用 canonical ID。

当前系统使用的可选清单字段包括：

- `description`、`author`
- `required`：保护必需插件，防止不安全的安装/卸载行为
- `default_install`：默认安装选择
- `require`：依赖和版本要求
- `driver`：驱动插件必需，且必须包含 `driver_key`（详见 [driver-plugin.md](driver-plugin.md)）
- `diy`：插件向 DIY 装修系统声明提供的组件（见下节）

版本号须兼容 `version_compare()`；当前版本与升级流程的适用阶段以 [架构规范.md](../../../rules/架构规范.md)「当前发布阶段（强约束）」为准。

## DIY 组件声明

插件通过清单的 `diy.components` 声明自己提供的 DIY 装修组件，该清单是组件可用性判定的**唯一真源**：后端结合模块安装/启用状态计算可用性（[DiyComponentServices.php](../../../../backend/app/services/diy/DiyComponentServices.php)），按消费方向输出两种口径：

- **管理端编辑器**：`diy/component_status` 下发**可用组件白名单**（仅含已安装且已启用模块声明的组件）。管理端组件源码须在 `defineOptions` 带 `pluginComponent: true` 标记声明插件归属，编辑器对标记组件按白名单放行——插件代码被裁剪、未安装或未启用时组件一律隐藏、画布已存在节点渲染占位（节点保留，保存不丢失，插件重新启用后自动恢复）；不带标记的组件视为核心组件恒显示
- **移动端 C 端**：`themeInfo` / `getDiy` 数据出口按不可用清单直接剔除组件节点（运行时过滤，不落库），前端拿到的即干净数据，插件重新启用后自动恢复

```json
{
  "key": "activity_example",
  "diy": {
    "components": [
      { "name": "example", "title": "示例组件" }
    ]
  }
}
```

约束：

- 清单 `name` 须与 DIY 数据节点 `name` 一致：管理端对应组件的 `defaultName`，移动端对应组件的静态 `name`（组件文件名如 `home_seckill.vue` 与数据名 `seckill` 不同，以 `defaultName` 为准）
- `title` 为中文名
- 未声明 `diy` 字段的插件不提供组件；内置组件（轮播/魔方/标题等）无需声明、不带 `pluginComponent` 标记、恒可用
- 管理端组件的 `defineOptions` 须带 `pluginComponent: true`（与清单声明成对维护）：缺标记时 `php think plugin:manifest-check` 报错，因为缺标记组件会被编辑器当核心组件恒显示，插件不可用时不会隐藏
- `php think plugin:manifest-check` 校验清单声明的组件存在于前端组件源码（管理端 defaultName / 移动端 name 均未命中即报错）及管理端组件的归属标记，防止声明悬空

## DIY 页面链接（链接选择器数据）

插件提供 uni-app 独立页面时，应把这些页面注册到管理端 DIY 装修的链接选择器（`eb_page_categroy` / `eb_page_link`），供商家在组件配置中选择跳转链接。链接数据由插件 Installer 自管：安装 `seed()` 写入、卸载 `cleanData()` 清理；核心安装 SQL（`public/install/crmeb_data.sql`）不预置插件专属分类与链接。

使用基类 `crmeb/module/BaseModuleInstaller` 提供的三个辅助方法：

```php
protected function seed(): int
{
    $count = 0;
    // 分类按"名称路径"逐级定位/创建，不硬编码分类 id；末级分类即插件独有命名空间
    $cateId = $this->ensurePageLinkCategory(['商城页面', '营销链接', '秒杀链接']);
    $count += $this->seedPageLinks($cateId, [
        ['name' => '秒杀列表', 'url' => '/pages/activity/goods_seckill/index'],
        [
            'name'    => '秒杀详情',
            'url'     => '/pages/activity/goods_seckill_details/index',
            'param'   => 'id,time_id',
            'example' => '/pages/activity/goods_seckill_details/index?id=1&time_id=1',
        ],
    ]);
    return $count;
}

protected function cleanData(): int
{
    $count = 0;
    // ...清理自有业务表/配置...
    $count += $this->cleanPageLinks(['秒杀链接'], [
        '/pages/activity/goods_seckill/index',
        '/pages/activity/goods_seckill_details/index',
    ]);
    return $count;
}
```

约束：

- 页面路径必须与 `frontend/uni-app/src/pages.json` 实际注册一致（含分包，如积分商城 `pages/points_mall`）；详情页 `param` 以页面 `onLoad` 实际读取的参数为准，不要照抄旧版字典数据。
- 分类命名沿用现有惯例（`秒杀链接`/`砍价链接`/`分销链接` 等），挂在共享父分类"营销链接"下；分类名即清理边界，不要使用与其他插件可能冲突的名称。
- `seedPageLinks()` 按 url 全局查重：旧站点预置在共享分类（如"个人中心链接"）下的插件链接不会重复插入，因此这类链接直接写在插件自己的链接配置里即可（如砍价记录、我的推广），卸载时同样按 url 清理。
- `cleanPageLinks()` 按 url 白名单删除链接；独有分类仅在分类下已无链接时删除，避免误删商家在插件分类下自建的链接。
- 基类 `onEnable()` 默认幂等补跑 `seed()`：存量站点升级新增链接数据时，对已安装插件执行一次禁用/启用即可落库，无需重装。
- 禁止绕过辅助方法直接写 `INSERT INTO eb_page_link` 而无对应清理；禁止把插件专属分类/链接预置进核心安装 SQL。

## 路径与类型映射

上传控制器对常见类型的映射如下：

| 清单 type | 安装目标 |
|---|---|
| `product`、`product_type` | `modules/product/<name>` |
| `activity`、`mode` | `modules/trade/<name>` |
| `marketing` | `modules/marketing/<name>` |
| `channel` | `modules/channel/<name>` |
| `user` | `modules/user/<name>` |
| `driver` | 清单声明的 `driver/<category>/<name>` 路径，驱动类别与扩展要求见 [driver-plugin.md](driver-plugin.md) |

## 模块 Installer 契约

模块 `Installer` 实现当前接口，通常继承 `crmeb/module/BaseModuleInstaller`：

```php
public function getKey(): string;
public function getName(): string;
public function getType(): string;
protected function runMigrate(): void;
protected function rollbackMigrate(): void;
protected function getMenuConfig(): array;
```

可选覆盖方法包括 `checkRequirements()`、`seed()`、`cleanData()`、`onEnable()` 和 `onDisable()`。消息插件还可覆写基类的 `getNoticeDefinitions(): array` 返回通知初值；渠道插件覆写 `getNoticeChannelDefinitions(): array` 返回渠道定义。两者默认返回空数组，所属插件在已有 `seed()` 中调用 `NoticeSeedServices::seed()` / `seedChannels()` 登记，在 `cleanData()` 中按 canonical ID 清理，不另建声明文件。完整的消息架构、渠道契约、调用链与 mark 速查见 [../../crmeb-dev/references/10-消息通知/notice.md](../../crmeb-dev/references/10-消息通知/notice.md)。

基础流程有意把 DDL 放在事务外执行，把菜单、种子和日志等 DML 放在事务内执行。不得在整个生命周期外层再包一层事务。

以下行为必须幂等：

- `runMigrate()` 创建表或字段前先检查是否存在。
- `seed()` 写入前检查稳定 key。
- 菜单插入由基类生成稳定 `menu_key` 幂等写入，核心父级通过 `parent_key` 引用。
- 插件安装后仍可能再次调用 `onEnable()`。
- 清理只能移除插件独占的行、表和资源。

生命周期代码可以直接使用 `Db`，但这不代表普通模块 Service 可以直接调用数据库 Facade。

## 菜单权限

当前菜单模型使用：

- `auth_type = 1`：可见路由/菜单，通过 `menu_path` 和通常存在的 `unique_auth` 标识
- `auth_type = 2`：API 权限，通过 `api_url` 和 HTTP `methods` 标识，并参与后端接口鉴权
- `auth_type = 3`：操作按钮/权限容器，通过 `unique_auth` 标识，不直接参与接口鉴权

`menu_key` 是系统生成且不可修改的菜单稳定标识。核心菜单使用
`core:<unique_auth>`（如 `core:admin-product`、`core:admin-marketing`）；插件菜单由基类按
canonical ID 自动生成，也可以在声明中通过 `key` 指定插件内局部标识。插件引用核心父级时使用
`parent_key`，同批次子菜单可继续用 `pid => '@父菜单名'`：

```php
[
    'key'        => 'activity',
    'parent_key' => 'core:admin-marketing',
    'menu_name'  => '示例活动',
    'auth_type'  => 1,
    'menu_path'  => '/marketing/example',
]
```

父级稳定标识不存在时安装必须失败，禁止静默回退到顶级；不得按中文显示名查找核心父级，
也不得硬编码特定环境中的数字菜单 ID。卸载由基类按 `owner_plugin_id` 物理删除插件菜单，
`cleanData()` 不得再次按菜单名、动态路径等条件自行删除菜单。

后端可见 `menu_path` 不带 `/admin`；前端路由通过 `setting.routePre` 添加。API 路由模板和菜单 `api_url` 必须一致，包括路径参数和 HTTP 方法。

`menu_path` 使用约束：

- 父级 `menu_path` 不得与任何子菜单重复：`breadcrumb.vue` 与 `navMenu` 组件以 `path` 作为 `v-for` 的 `:key`，父子同路径会触发 Vue "Duplicate keys" 警告。父级用基础路径（如 `/marketing/example`），子级用完整路径（如 `/marketing/example/list`）。
- 可见菜单（`auth_type=1`）的 `menu_path` 必须有对应前端路由（`routes.js` 中 `routePre + menu_path`），否则侧边栏点击后 404；编辑/创建页（如 `/product/create/:id?`）通过代码内 `router.push()` 进入，不需要菜单项。
- 带 `api_url` 的权限项必须声明 `auth_type=2` 并提供 `methods`，否则后端 `verifyAuth` 不识别、接口不会被鉴权；操作按钮用 `auth_type=3` + `unique_auth`。禁止用 `auth_type=3` 声明接口权限项，也禁止声明缺少 `unique_auth` 的按钮项。

## 系统配置项种子（system_config）

模块需要全局开关/参数（后台「系统配置 → 某页签」）时，由 Installer 自建 `system_config_tab` 分类并写入 `system_config` 行（正确范例：`modules/service/mcp/Installer.php` 的 `mcp_enabled`；反面案例：`modules/aiagent/Installer.php` 的 `seedTaskConfig()` 曾写错已修复）。字段语义：

- `type` 是**控件类型**，`SystemConfigServices::createForm()` 按它 `switch` 分发渲染，合法值：`text` / `radio` / `textarea` / `checkbox` / `select` / `switch` / `upload`；`text` 控件再按 `input_type`（`input` / `number`）区分输入框形态。
- `info` 是表单标签，`desc` 是说明文案；`parameter` 是选项映射，每行 `值=>标签`（如 `"0=>关闭\n1=>开启"`），radio/checkbox/select 必填。
- `value` 存 JSON 可解析串：radio 存 `0`/`1`、文本存 JSON 字符串；`json_decode` 后供表单回显与 `sys_config()` 运行时判定。
- 运行时读取走 `sys_config()`，带缓存键 `system_config_<menu_name>`；直接改数据库后必须清缓存（后台保存任意配置会触发 `CacheService::clear()`）。

> 高频踩坑：把 `type` 写成中文说明（如「允许内网 AI 服务地址」）而非控件类型时，`createForm` 的 `switch` 无匹配分支，该页签渲染**空白表单且无任何报错**——配置项在界面上不可见、保存自然无效，极易误判为「写入有问题」。新增配置种子后，必须实际打开对应配置页签确认字段渲染与回显，再联调业务判定逻辑（如 `sys_config('xxx') === 1`）。

## 与默认安装 SQL 去重

Installer 中凡涉及写库操作（建表、种子、菜单、配置项），必须确保系统默认安装 SQL 中没有重复数据：系统功能模块转为插件后，其默认数据应从 `backend/public/install/` 的 `crmeb_table.sql`（表结构）与 `crmeb_data.sql`（初始数据、菜单、配置）中删除，以 Installer 为唯一来源，保证安装幂等、卸载干净。

检查方法：

```bash
cd backend/public/install
# 表是否重复（含建表与数据两部分）
grep -n "<表名>" crmeb_table.sql crmeb_data.sql
# 菜单是否重复
grep -n "<menu_name>" crmeb_data.sql
# 配置项是否重复
grep -n "<配置 key>" crmeb_data.sql
```

命中默认安装文件的，删除对应记录（建表语句与 INSERT 数据），由 `runMigrate()` / `seed()` / `getMenuConfig()` 统一提供。

## 升级包（发布后维护阶段）

先核对 [当前发布阶段](../../../rules/架构规范.md)，再决定是否适用以下流程。

只有 key、type、path 均一致且版本号更高时，上传到已安装插件目录的包才属于升级包。数据迁移放在：

```text
upgrades/<from>_to_<to>.php
```

每个文件返回一个 callable。只要存在迁移脚本，就必须形成从已安装版本到目标版本的完整递增链。纯代码升级可以不提供该目录。

打包内容可以直接以插件根目录为根，也可以有一个仅包含唯一插件根目录的外层目录。包含源码、清单、Installer、迁移文件和必需构建资源；排除 `.env`、运行时/缓存/日志数据、宿主已提供的依赖及无关仓库文件。

## 生命周期验证

在隔离环境中验证清单发现和元数据、依赖拒绝、首次安装、重复安装、启用、禁用、重新启用、卸载、重新安装、菜单可见性、API 权限、数据清理和资源清理。版本升级验证仅在发布后维护阶段适用。
