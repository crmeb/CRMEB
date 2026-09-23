# 通用页面系统开发指南（SchemaPage：插件纯 PHP 生成管理页面）

> 本文是 `crmeb-dev` 技能的子文档，完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)。内容基于 v8.1 实际代码整理（`backend/crmeb/services/schema/` + `backend/app/adminapi/controller/schema/` + `frontend/admin/src/components/SchemaPage/`）。

## 一、定位与适用场景

插件只需编写 PHP（页面配置 + 数据方法），即可获得标准化的后台管理页面——**无需编写任何 Vue/前端代码、无需注册前端路由**。

- 访问地址约定：`/admin/plugin/{插件目录名}/{pageKey}`（如 `/admin/plugin/mcp/list`）
- 覆盖四类页面：**列表页**（搜索 + 表格 + 抽屉操作 + 分页 + 列排序）、**整页表单页**（form-create 规则 + 页脚提交）、**详情页**（提示条 + 字段描述 + 页面级动作）、统计页（待交付，容器已预留分发）
- 交互规范：新增/编辑/详情统一**右侧抽屉**，删除统一**二次确认**

**何时不用**：高度定制交互页面仍走 UMD 扩展（[references/04-前端扩展/backend-frontend.md](../04-前端扩展/backend-frontend.md)）；DIY 装修页面走 DIY 组件。组件集是系统级封闭集，插件不能注册新组件类型。

### 1.1 插件页面方案二选一，禁止混合写法（强约束）

**同一插件内 schema 与 UMD 不得混用**，选型在开发前一次性确定：

- **选 schema 的前提**：该插件全部管理页面均能被通用页面系统完整覆盖（列表/整页表单/详情）。任何一处需要二级弹窗子列表、推广二维码、导出、高度定制交互等 schema 无法承载的能力，则**整个插件全部页面改走 UMD**，不保留任何 schema Provider；
- **选 UMD 的插件**：不得存在 `schema/` 目录；
- **选 schema 的插件**：必须删除全部 UMD 残留（`ext.json`、`index.umd.js`、`routes.js`、`App.vue`、`pages/`），并同步清理静态路由（`router/modules/*.js`）与菜单 `menu_path` 中指向 UMD 路由的引用，以及数据库 `eb_system_menus` 中的存量路径；
- 先例：distribution 因推广人/推广订单二级弹窗、推广二维码、导出等无法覆盖，整体回退纯 UMD（schema Provider 全部删除，菜单指回 UMD 路由）；signin / newuser / recharge / mcp 页面能力简单，采用纯 schema。

## 二、整体架构

```
插件 PHP                          后端网关（一次性建设）              前端通用容器（一次性建设）
modules/{c}/{n}/schema/XxxPage    crmeb/services/schema/             /admin/plugin/:pluginKey/:pageKey
 extends BaseSchemaPageProvider   ├─ SchemaPageRegistry  约定式扫描  components/SchemaPage/
  config()  页面结构配置           ├─ SchemaConfigValidator 校验降级   ├─ index.vue       按 type 分发
  list()    列表数据              └─ SchemaPageController 六端点      ├─ ListTemplate    搜索+表格+分页
  form()    create_form 规则                                          ├─ SchemaDrawer    右侧抽屉
  save() / detail() / action()                                       └─ SchemaForm      复用 form-create
```

### 2.1 网关端点（插件无需注册路由）

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `schema/plugin/:pluginKey/:pageKey/config` | 页面结构配置 |
| GET | `schema/plugin/:pluginKey/:pageKey/list` | 列表数据（query: page/limit/where[字段]/order[字段]） |
| GET | `schema/plugin/:pluginKey/:pageKey/form` | 表单规则（query: id，0=新增） |
| GET | `schema/plugin/:pluginKey/:pageKey/detail` | 详情数据（query: id） |
| POST | `schema/plugin/:pluginKey/:pageKey/save` | 保存（body: id + 表单字段平铺） |
| POST | `schema/plugin/:pluginKey/:pageKey/action` | 通用动作（body: action + payload） |

中间件四件套（登录 + RBAC + 日志）已内置；接口未登记 api_url 不受 RBAC 管控（与系统一致，按需在菜单中登记 auth_type=2）。

## 三、插件侧开发（唯一需要做的事）

### 3.1 文件约定（自动发现）

- 位置：`modules/{分类}/{插件}/schema/{Xxx}Page.php`（一级模块 `modules/{插件}/schema/` 同样支持）
- 文件名：**大驼峰 + `Page.php` 结尾**（排除 Base/Abstract/Interface/Contract/Trait 开头）
- 插件目录名（schema 上级目录名）即 URL 中的 pluginKey；启用校验读该模块 `plugin.json` 的 key（未安装/未启用的模块页面不注册）
- **放置文件即生效，零注册**

### 3.2 Provider 写法

继承 `BaseSchemaPageProvider`（`crmeb\services\schema\`），按页面类型**只覆写需要的方法**（未覆写方法被调用时抛 AdminException）：

| 页面类型 | 需覆写 |
|---|---|
| 列表页 | `config()` + `list()`（含增删改再覆写 `form()`/`save()`/`action()`/`detail()`） |
| 整页表单页 | `config()` + `form()` + `save()`（编辑场景经 URL `?id=xx` 传主键回填） |
| 详情页 | `config()` + `detail()`（含页面动作再覆写 `action()`） |
| 统计页（待交付） | `config()` + `stats()` |

```php
<?php
declare(strict_types=1);

namespace modules\agent\mcp\schema;           // 二级模块；一级模块用 modules\{name}\schema

use crmeb\services\schema\BaseSchemaPageProvider;
use crmeb\utils\FormBuilder as Form;           // 表单组件工厂（注意不是 Form 类）
use app\dao\agent\McpToolDao;                  // 数据走 DAO 层

/**
 * 工具列表页（访问 /admin/plugin/mcp/list 自动渲染）
 */
class ToolListPage extends BaseSchemaPageProvider
{
    /** 构造注入 DAO（注册中心经容器实例化，自动解析依赖） */
    public function __construct(McpToolDao $dao)
    {
        $this->dao = $dao;
    }

    /** 页面标识：静态方法（扫描阶段免实例化注册），同插件内唯一，如 list/stats/setting */
    public static function pageKey(): string
    {
        return 'list';
    }

    public function config(): array
    {
        return [
            'type'  => 'list',                       // 封闭：list/form/stat
            'title' => '工具列表',
            'search' => [...],                       // 见 4.1
            'actions' => [...],                      // 顶部操作，见 4.3
            'columns' => [...],                      // 列定义，见 4.2
            'row_actions' => [...],                  // 行操作，见 4.3
        ];
    }

    /** 列表数据：$where 为搜索栏提交值；返回 ['list' => [], 'count' => int] */
    public function list(array $where, int $page, int $limit, array $order = []): array
    {
        return $this->dao->getSchemaList($where, $page, $limit, $order ?: ['add_time' => 'desc']);
    }

    /** 表单规则：复用 create_form() 标准结构；action 留空 → 前端默认提交网关 save 端点 */
    public function form(int $id = 0): array
    {
        $data = $id ? $this->dao->get($id) : [];
        $field[] = Form::input('name', '名称', $data['name'] ?? '')->required('请输入名称');
        $field[] = Form::radio('status', '状态', (int)($data['status'] ?? 1))->options([
            ['label' => '启用', 'value' => 1],
            ['label' => '禁用', 'value' => 0],
        ]);
        return create_form($id ? '编辑' : '新增', $field, '', $id ? 'PUT' : 'POST');
    }

    /** 保存（新增/编辑统一入口；$data 已剥离 id） */
    public function save(int $id, array $data): void
    {
        $id ? $this->dao->update($id, $data) : $this->dao->save($data);
    }

    /** 详情抽屉数据：返回 {fields: [{label, value, type?, map?}]} */
    public function detail(int $id): array
    {
        $row = $this->dao->get($id);
        return ['fields' => [
            ['label' => '名称', 'value' => $row['name']],
            ['label' => '状态', 'value' => $row['status'], 'type' => 'tag',
             'map' => [1 => ['label' => '启用', 'theme' => 'success'], 0 => ['label' => '禁用', 'theme' => 'info']]],
        ]];
    }

    /** 通用动作（删除/启停等）；返回响应指令集 */
    public function action(string $action, array $payload): array
    {
        if ($action === 'delete') {
            $this->dao->delete((int)$payload['id']);
            return ['msg' => '删除成功'];
        }
        throw new AdminException('不支持的动作: ' . $action);
    }
}
```

完整真实示例：`backend/modules/service/mcp/schema/ToolListPage.php`（MCP 工具列表 + 详情抽屉，数据源为 McpToolRegistry）。

## 四、页面配置结构（config）

### 4.0 统计卡片（cards）与状态分类头（tabs）

**统计卡片**（渲染在页面顶部，数据由 `list()` 响应的 `summary` 键下发，对象格式 `{field: value}`）：

```php
'cards' => [
    ['field' => 'sum_price', 'label' => '充值总金额', 'type' => 'amount'], // type: text/number/amount（缺省 text）
    ['field' => 'count',     'label' => '充值笔数',   'type' => 'number'],
],
// list() 返回：['list' => [...], 'count' => n, 'summary' => ['sum_price' => 123.45, 'count' => 88]]
```

**状态分类头**（渲染在表格上方，选中项 value 以 `where[field]` 提交；切换自动重置分页。顶部 actions/批量按钮一律渲染在表格卡内（有 tabs 时在其下方，无 tabs 时在表格上方），与常规后台页"操作按钮 → 表格"同卡布局一致，不独立成卡。动作配置 `follow_tab: true` 时按钮文案拼接当前分类标签，如"添加"+"累积签到奖励"）:

```php
'tabs' => [
    'field'      => 'status',                     // 提交字段名
    'options'    => [                              // 首项为默认选中项（页面加载即带上其值查询）
        ['label' => '连续签到奖励', 'value' => 0],
        ['label' => '累积签到奖励', 'value' => 1],
    ],
    'with_count' => false,                        // true 时计数由 list() 响应 tabs_count 键下发（键为 value 字符串形式）
],
```

**新增表单默认值跟随分类头**：声明 tabs 的页面，前端新增（create）请求 form 端点时自动附带当前选中分类值（query 参数 `tab`）；提供者在 `form()` 中以 `app('request')->param('tab', 0)` 读取作为新增默认值（编辑场景以数据本身值为准），先例见 `modules/marketing/signin/schema/SignRewardListPage.php`。

### 4.1 搜索区（search，独立自研组件，非 form-create 规则）

```php
'search' => [
    ['field' => 'keyword', 'label' => '关键词', 'type' => 'input', 'placeholder' => '...'],
    ['field' => 'status',  'label' => '状态', 'type' => 'select', 'options' => [['label' => '启用', 'value' => 1]]],
    ['field' => 'date',    'label' => '创建时间', 'type' => 'daterange'],
    // 远程下拉：options 声明省略，挂载时 GET options_url 加载选项
    ['field' => 'category_id', 'label' => '分类', 'type' => 'select', 'options_url' => 'agent/mcp/categories'],
    // 远程搜索：remote 声明后按输入关键字实时查询（接口需支持 keyword 参数），适合大表（用户/商品）
    ['field' => 'uid', 'label' => '用户', 'type' => 'select', 'options_url' => 'agent/mcp/user_options', 'remote' => true],
],
```

| type | 提交格式 |
|---|---|
| input / number / select / date | `where[field] = 值`（select 多选为数组；空值不提交） |
| daterange | 自动拆分 `where[field_start]` / `where[field_end]`（YYYY-MM-DD）；面板内置快捷选项（今天/昨天/本月/上月/最近7天/最近30天/最近90天/最近1年/本年/去年），前端强制展示、无需配置 |

**select 附加属性**（Validator 对 search 字段仅规范化 type，附加属性原样透传）：

| 属性 | 说明 |
|---|---|
| `options` | 静态选项 `[{label, value}]`，与 options_url 二选一（声明 options_url 时忽略 options） |
| `options_url` | 远程选项地址（相对 `/adminapi`，如插件自有路由 `agent/mcp/categories`）；GET 请求，响应约定 `data` 直接为 `[{label, value}]` 数组或为 `{options: [...]}`；加载失败静默降级为空选项 |
| `remote` | 声明 true 时为远程搜索模式：挂载不加载，输入关键字触发 `options_url?keyword=关键字`；适合大表（用户/商品），由插件接口自行完成 keyword 检索 |
| `multiple` | 多选，提交为数组 |
| `placeholder` | 占位文案 |

下拉均自带本地过滤（filterable）。

### 4.2 列定义（columns）

```php
['prop' => 'status', 'label' => '状态', 'type' => 'tag', 'width' => 100,
 'map' => [1 => ['label' => '启用', 'theme' => 'success'], 0 => ['label' => '禁用', 'theme' => 'info']]],
```

| type | 渲染 | 附加配置 |
|---|---|---|
| text（默认） | 文本 | — |
| ellipsis | 超长省略 + tooltip | — |
| number | 千分位 | — |
| amount | ¥ 两位小数 | — |
| datetime / date | time() 整型自动格式化 | — |
| tag | el-tag | `map`：值 → {label, theme: success/info/warning/danger}；**空字符串可作为合法 map 键** |
| image | el-image 可预览 | — |
| switch | el-switch，切换即调 action（缺省 `action: 'status'` 由 Validator 规范化补齐，payload {id, value}） | `on_value`/`off_value`（默认 1/0）、`action` |
| link | el-link 文本（P2 增强行为） | — |

通用项：`width`/`minWidth`/`align`/`fixed`/`sortable`（true 时列头出现排序箭头，排序事件经 `order` 参数透传给 `list()`，取消排序传空数组恢复默认）。未知 type 由后端 Validator 降级为 text。

**tag 配色与主题切换（重要）**：系统在 `frontend/admin/src/theme/variables.scss` 中对主色 tag（无 type 的 `.el-tag`）做了主题化处理（`color/border-color` 跟随 `--prev-color-primary`，可随后台主题切换换色）；success/warning/danger/info 四类语义色 tag 通过 `:not()` 排除，保持 Element Plus 默认语义配色（#67c23a/#e6a23c/#f56c6c/#909399），**不随主题漂移**。因此：表达状态语义时必须显式指定 `theme`（success/info/warning/danger），不要省略 theme 依赖默认主色 tag。

### 4.3 操作配置（actions / row_actions）

```php
// 顶部操作：action=create 固定为"新增抽屉"；纯跳转场景用 mode=navigate + path（前端直接 router.push，不发请求），
// path 必须写完整路由路径（含 /admin 前缀，如 '/admin/marketing/lottery/create'，缺前缀无法命中路由且无任何反馈）
'actions' => [
    ['action' => 'create', 'label' => '新增', 'theme' => 'primary'],
    ['action' => 'go_create', 'label' => '创建抽奖活动', 'mode' => 'navigate', 'path' => '/admin/marketing/lottery/create'],
],
// 行操作：全部在操作列（链接按钮，delete 默认 danger 色）
'row_actions' => [
    ['action' => 'edit',   'label' => '编辑', 'mode' => 'drawer_form'],
    ['action' => 'detail', 'label' => '详情', 'mode' => 'drawer_detail'],
    ['action' => 'delete', 'label' => '删除', 'mode' => 'confirm_action', 'confirm' => '删除后不可恢复，确认删除？'],
    // 行级显示条件 visible：[{field, value, op}] 全部满足才渲染该按钮（条件操作场景，如"退款"仅已支付行显示）
    ['action' => 'refund', 'label' => '退款', 'mode' => 'drawer_form',
     'visible' => [['field' => 'paid', 'value' => 1, 'op' => 'eq'], ['field' => 'refund_price', 'value' => 0, 'op' => 'lte']]],
],
```

visible 支持的 op：`eq` / `neq` / `gt` / `lt` / `gte` / `lte`（数值比较）/ `empty` / `not_empty`，缺省 `eq`。

| mode | 行为 | 对应 Provider 方法 |
|---|---|---|
| drawer_form | 右侧抽屉 form-create 表单，提交调 save | `form()` + `save()` |
| drawer_detail | 右侧抽屉详情描述列表 | `detail()` |
| confirm_action | 确认弹窗（`confirm` 文案）→ 调 action | `action()` |
| direct_action | 直接调 action | `action()` |
| navigate | 跳转（`path` 支持 `:字段` 占位替换行数据） | — |

**批量操作（batch_actions）**：声明后表格自动出现多选列（首列），批量按钮渲染在顶部操作区（与顶部动作之间有分隔线，未与顶部动作共存时独立显示）：

```php
'batch_actions' => [
    ['action' => 'delete', 'label' => '批量删除', 'mode' => 'confirm_action', 'confirm' => '确认删除选中项？'],
    ['action' => 'enable', 'label' => '批量启用', 'mode' => 'direct_action'],
],
```

- mode 仅支持 `confirm_action` / `direct_action`（其他模式由 Validator 剔除）
- 点击批量按钮未勾选行时前端提示"请先勾选要操作的数据"
- payload 约定：`{ids: [选中行 id 数组]}`（主键约定为 `id`），`action()` 内自行处理；批量删除示例：`$this->dao->deleteAll((array)$payload['ids'])`
- 操作成功后默认刷新列表（勾选状态随数据重载自动清空）

**动作响应指令集**（action/save 返回值，前端查表执行）：

```php
return ['msg' => '删除成功'];                    // 默认：提示后自动刷新列表
return ['msg' => '已提交', 'refresh' => false];  // 显式不刷新
return ['msg' => '已提交', 'redirect' => '/admin/plugin/x/y'];  // 跳转（不再刷新）
return ['msg' => '导出成功', 'download' => 'https://...'];      // 触发下载
```

### 4.4 表单规则（form，复用现有 form-create 体系）

- 用 `Form::xxx()`（即 `crmeb\utils\FormBuilder`）声明字段，`create_form($title, $field, $action, $method)` 输出标准结构
- **`$action` 传空字符串** → 前端自动提交网关 save 端点（body 自动携带 id），插件零路由；传具体地址则按 `method` 提交到插件自有路由
- 编辑回填：`form($id)` 时字段第 3 参填库中值

#### 4.4.1 选择器组件（frame：素材/用户/商品）

表单可使用 form-create 的 frame 组件弹窗打开系统后台页面选择并回填，**无需任何前端代码**（SchemaForm 渲染全局 form-create，frame 为其内置组件）。系统已有现成目标页面，写法与系统内 create_form 完全一致：

```php
use crmeb\utils\FormBuilder as Form;

// ① 素材库选图（单图 frameImage / 多图 frameImages，fodder 为回填标识）
// 注意：src 必须带前导斜杠 + .html 后缀（路由按 xxx.html 注册；缺前导斜杠会被 iframe 拼成相对路径，缺 .html 会 404）
$field[] = Form::frameImage('pic', '封面图', '/' . rtrim(config('app.admin_prefix', 'admin'), '/') . '/widget.images/index.html?fodder=pic', $data['pic'] ?? '')
    ->width('950px')->height('560px')->props(['footer' => false]);

// ② 选择用户（打开后台用户列表页，fodder 触发回填）
$field[] = Form::frameInputs('uid', '选择用户', '/' . rtrim(config('app.admin_prefix', 'admin'), '/') . '/system.User/list.html?fodder=uid')
    ->width('950px')->height('560px')->props(['footer' => false]);

// ③ 选择商品（打开商品列表页）
$field[] = Form::frameInputs('product_id', '选择商品', '/' . rtrim(config('app.admin_prefix', 'admin'), '/') . '/store.StoreProduct/index.html?fodder=product_id')
    ->width('950px')->height('560px')->props(['footer' => false]);

// ④ 选择优惠券（标签选择器：el-tag 展示券名可删除，弹窗打开系统选择优惠券页，页脚确定/取消）
// 值为对象数组 [{id, title, coupon_price, use_min_price}]，发券消费端已兼容
$field[] = Form::couponSelect('coupon_ids', '选择优惠券', $data['coupon_ids'] ?? []);
```

- 方法集：`frameInput`（单值）/ `frameInputs`（多值数组）/ `frameImage`（单图）/ `frameImages`（多图）/ `frameFile` / `frameFiles`（附件）
- 回填机制：frame 弹窗以 el-dialog + iframe 打开目标页（同源），iframe 加载后 form-create 向其 `contentWindow` 注入 `form_create_helper`，目标页选中数据后调 `form_create_helper.set(fodder字段, value)` + `close(字段)` 完成回填关窗（**非 postMessage**）。现成选择页均已支持 `fodder` 参数：素材库 `widget.images/index.html`、用户列表 `system.User/list.html`、商品列表 `store.StoreProduct/index.html`、**优惠券 `marketing.Coupon/list.html`**（`frontend/admin/src/components/couponSelect/index.vue`，数据源 `marketing/coupon/released`，勾选校验余量与领取期，与发券查询条件对齐）
- 值格式：目标页 `set` 的即字段值——多选 frameInputs 为**纯 id 数组**；SchemaCoupon（优惠券标签选择器）为**对象数组** [{id,title,...}]（标签展示券名，发券消费端按需取 id）
- 自定义组件扩展：后端 `Form::createComponent('组件名')` 生成规则（见 `Form::editor()` / `Form::couponSelect()`），前端在 SchemaForm.vue 经 `formCreateFactory.component('组件名', 组件)` 注册（**注意 component 方法在默认导出对象上，$form() 返回的 maker 无此方法**）；自定义组件契约仅需 `modelValue` prop + `update:modelValue` emit
- 表单能力全集（form-create Elm 工厂）：input/textarea/inputNumber/radio/checkbox/select/switch/datePicker/timePicker/rate/colorPicker/slider/cascader/tree/upload/frame/group；**富文本使用 `Form::editor()`**（type=SchemaEditor 自定义组件，前端 SchemaForm 已注册，适配系统 wangEditor，含素材库选图/AI 写作）

### 4.5 整页表单页（type=form）

单例配置页/独立表单页场景（区别于列表页内的抽屉表单）：

```php
public function config(): array
{
    return [
        'type'        => 'form',
        'title'       => 'XX 设置',
        'submit_text' => '保存',   // 可选，默认「保存」
    ];
}

public function form(int $id = 0): array   // id 经 URL ?id=xx 传入（编辑），缺省 0
{
    $field[] = Form::input('name', '名称', $this->getSetting('name'));
    return create_form('XX 设置', $field, '', 'POST');   // action 留空 → 提交网关 save 端点
}

public function save(int $id, array $data): void { /* ... */ }
```

前端行为：页脚统一「保存/重置」按钮，保存成功停留本页（`redirect`/`download` 指令照常执行）。

### 4.6 详情页（type=detail）

单例只读内容页：**顶部提示条（tip）+ 字段描述列表（detail() 的 fields）+ 页面级动作（actions）**，适用于配置说明页、接入文档页、单实体详情页等场景：

```php
public function config(): array
{
    return [
        'type'    => 'detail',
        'title'   => 'XX接入说明',
        'tip'     => '顶部信息提示条文案（可选，info 样式）',
        'actions' => [   // 可选，页面级动作按钮（动作文案可按当前状态动态返回）
            ['action' => 'toggle', 'label' => '开启服务', 'mode' => 'direct_action'],
            ['action' => 'reset', 'label' => '重置', 'mode' => 'confirm_action', 'confirm' => '确定重置？'],
        ],
    ];
}

public function detail(int $id): array   // 单例页面忽略 id；多实体场景经 URL ?id=xx 传入
{
    return ['fields' => [ /* 同列表页详情抽屉字段，json 类型自带一键复制 */ ]];
}

public function action(string $action, array $payload): array { /* 动作响应指令集 */ }
```

前端行为：动作执行成功后自动重载详情数据（字段/动作文案随最新状态刷新）。

### 4.7 详情字段（detail，fields 类型封闭集）

`detail()` 返回 `fields` 的 `type`：text（默认）/ datetime / date / tag（带 map）/ **json**（格式化代码块 + 右上角一键复制，Clipboard API 失败自动回退 execCommand，http 部署可用）。

## 五、菜单与权限集成

Installer `getMenuConfig()` 中 `menu_path` 直接写约定路径即可命中通配路由（前端仅一条 `/admin/plugin/:pluginKey/:pageKey` 路由，已预置）：

```php
[
    'menu_name' => 'MCP 工具',
    'menu_path' => '/plugin/mcp/list',   // = '/plugin/' + 插件目录名 + '/' + pageKey
    'auth_type' => 1,
    'controller' => 'mcp.tools',
    'is_show'    => 1,
],
```

- 菜单生命周期完全复用 `BaseModuleInstaller`（幂等插入/显隐/`owner_plugin_id` 卸载清理）
- 页面级权限 = 菜单显隐；接口级 = 按需登记 auth_type=2（api_url 与网关路由规则一致，如 `schema/plugin/mcp/list/save`）；元素级权限（actions/columns 的 `permission` 声明 + 后端过滤）为 P2 待交付

## 六、验证清单（完成页面后自检）

- [ ] Provider 文件位于 `modules/{...}/{插件}/schema/`，大驼峰 `Page.php` 结尾，命名空间与路径一致
- [ ] `pageKey()` 为**静态方法**且同插件内唯一
- [ ] 列表页已覆写 `config()`/`list()`；表单字段用 `crmeb\utils\FormBuilder as Form`（不是 `Form` 类）
- [ ] `config()` 经 Validator：type 只能 list/form/stat；columns 项必须有 prop+label
- [ ] 数据查询走 DAO 层；时间字段 `time()` 整型；`action()`/`save()` 校验数据存在性
- [ ] 使用 frame 选择器时目标地址已带 `fodder` 参数（素材/用户/商品页原生支持回填）
- [ ] 声明 `batch_actions` 时 `action()` 已处理 `{ids}` 数组 payload；远程搜索 select 的插件接口已支持 `keyword` 参数
- [ ] curl 冒烟：`/adminapi/schema/plugin/{key}/{page}/config` 与 `/list?page=1&limit=15` 返回 status=200
- [ ] 浏览器访问 `/admin/plugin/{key}/{page}` 渲染正常；模块未启用时返回友好提示

## 七、关键文件清单（排障入口）

| 层 | 文件 | 职责 |
|---|---|---|
| 契约（crmeb） | `crmeb/services/schema/SchemaPageProvider.php` | 接口（pageKey 为静态方法） |
| 契约（crmeb） | `crmeb/services/schema/BaseSchemaPageProvider.php` | 默认实现（调用即抛异常） |
| 契约（crmeb） | `crmeb/services/schema/SchemaPageRegistry.php` | 约定式扫描 + 注册 + 容器实例化 |
| 契约（crmeb） | `crmeb/services/schema/SchemaConfigValidator.php` | 类型白名单与降级 |
| 网关（app） | `app/adminapi/controller/schema/SchemaPageController.php` | 六端点 |
| 路由（app） | `app/adminapi/route/schema.php` | 网关路由组（scandir 自动加载） |
| 容器（app） | `app/AppService.php` | SchemaPageRegistry 单例绑定 |
| 前端 | `frontend/admin/src/components/SchemaPage/` | 通用页面容器（index 分发/ListTemplate/SchemaSearchBar/SchemaTable/SchemaTableCell/SchemaDrawer/SchemaForm/SchemaDetail） |
| 前端路由 | `frontend/admin/src/router/modules/schema.js` | 通配路由（routers.js 已引入） |

## 八、已知边界与后续规划

- 列表页（list）、整页表单页（form）与详情页（detail）已交付；统计页（type=stat 指标卡+图表）待交付：后端基类 `stats()` 已就绪，前端容器对 stat 类型显示"开发中"占位
- 已交付通用能力：`config.title` 自动联动路由 meta 与浏览器标签标题；`default_order` 默认排序与列头 `sortable` 排序透传；switch 列缺省 action 由 Validator 规范化为 `status`；详情页 tip 提示条与页面级 actions（文案可动态）；详情 json 字段一键复制；搜索 select 远程下拉（`options_url`）与远程搜索（`remote`）；批量操作（`batch_actions` 多选列 + `{ids}` payload）；表单 frame 选择器（素材/用户/商品，见 4.4.1）；**统计卡片（cards + summary）/ 状态分类头（tabs + tabs_count）/ 行操作显示条件（visible）/ 富文本 `Form::editor()`**（见 4.0、4.3、4.4.1）
- 插件迁移实践（2026-09）：signin / newuser / recharge 已迁移纯 schema 并删除对应 UMD 前端目录（含 signin 遗留的 pages/sign_rewards 孤儿文件）；mcp 本就是纯 schema。**distribution 已整体回退纯 UMD**——其推广人/推广订单二级弹窗、推广二维码、导出等交互 schema 无法承载，按 1.1 节"禁止混合写法"铁律删除全部 schema Provider，Installer 菜单 menu_path 全部指回 UMD 路由（并清理数据库 eb_system_menus 存量 schema 路径与孤儿行）。注意删除插件 UMD 目录时需同步清理 `router/modules/plugin.js` 等 static 路由模块中指向 PageContainer 的遗留静态路由（静态段评分高于 schema 通配路由，否则扩展加载失败页抢占渲染）
- 全局修复：`variables.scss` 中 tag 样式增加 `:not(.el-tag--success/warning/danger/info)` 排除，语义色 tag 恢复 Element Plus 默认配色（此前被全局规则强制变蓝）；经实测验证不影响系统主题切换（主色 tag 与 primary 按钮正常跟随 `--prev-color-primary` 换肤）
- P2 规划：元素级权限过滤、config 缓存、link 列增强、导出动作、CrudSchema 快捷构建器
- 设计文档：项目根目录《管理端通用页面组件系统设计方案.md》（含 P0 实施记录与验证结果）
