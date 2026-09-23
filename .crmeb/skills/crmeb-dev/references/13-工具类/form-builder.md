# FormBuilder 表单构建器（form-create）使用说明

> 源码：`backend/crmeb/utils/FormBuilder.php`（继承 vendor 包 `FormBuilder\Factory\Elm`，额外提供 `FormBuilder::setOptions()` 与富文本 `FormBuilder::editor()`）。全部组件能力来自 `vendor/xaboy/form-builder/src/`（v2.0，官方文档 http://php.form-create.com）。

## 一、定位与作用

- **作用**：在**后端纯 PHP** 生成前端可直接渲染的表单（el-form 规则 JSON），前端管理端全局封装渲染，**零前端代码**产出"新增/编辑弹窗、整页表单"
- **两大使用场景**：
  1. **传统控制器**：`create_form($title, $field, $url, $method)` 输出表单结构，配合插件自有路由提交
  2. **通用页面系统**（SchemaPage）：`form()` 内组装字段，`create_form()` 的 `$action` 传空字符串 → 自动提交网关 save 端点（推荐，零路由，见 [12-通用页面/schema-page.md](../12-通用页面/schema-page.md)）
- **使用习惯**：`use crmeb\utils\FormBuilder as Form;` 后以静态工厂方法 `Form::input(...)` 生成组件对象，链式配置返回 `$this`

## 二、create_form() 帮助函数

定义位置：`backend/app/common.php:882-910`

```php
function create_form(string $title, array $field, $url, string $method = 'POST'): array
// 返回 compact('rules', 'title', 'action', 'method', 'info', 'status')
// $method 不在 ['POST','PUT','GET','DELETE'] 内时抛 ValidateException
```

| 参数 | 说明 |
|------|------|
| `$title` | 表单标题（前端弹窗标题） |
| `$field` | 字段规则数组，每个元素为 `Form::xxx()` 生成的组件对象 |
| `$url` | 提交地址。**传 `''`**：SchemaPage 场景自动提交网关 save 端点（body 自动携带 id）；传统场景传 `$this->url('/xxx/save')` |
| `$method` | 提交方式，默认 POST |

**典型完整示例**（传统控制器场景，真实代码 `backend/app/services/personnel/ServicePersonnelServices.php:104`）：

```php
public function form(int $id = 0): array
{
    $data = $id ? $this->dao->get($id) : [];
    $field = [];
    $field[] = Form::input('name', '人员名称', $data['name'] ?? '')->required();
    $field[] = Form::radio('status', '状态', $data['status'] ?? 1)
        ->options([Form::option(1, '开启'), Form::option(0, '关闭')]);
    return create_form('添加服务人员', $field, $this->url('/setting/service_personnel/save'), 'POST');
}
```

**SchemaPage 场景**（`$action` 留空，编辑回填直接填第 3 参）：

```php
public function form(int $id = 0): array
{
    $data = $id ? $this->services->getInfo($id) : [];
    $field[] = Form::input('name', '名称', $data['name'] ?? '')->required();
    return create_form('编辑', $field, '', 'POST');   // action 留空 → 自动提交网关 save
}
```

## 三、组件工厂方法全集（以 vendor 源码为准）

> 注意易错点：数字是 **`number()`**（无 inputNumber）、开关是 **`switches()`**、颜色是 **`color()`**（无 colorPicker）。均支持链式调用。

### 3.1 输入类（InputFactoryTrait）

| 方法 | 说明 |
|------|------|
| `input($field, $title, $value = '', $type = TYPE_TEXT)` | 通用输入框，`$type` 可传 `Input::TYPE_PASSWORD` / `TYPE_TEXTAREA` / `TYPE_URL` / `TYPE_EMAIL` / `TYPE_DATE` |
| `text` / `password` / `textarea` / `url` / `email` / `idate` | 各类型快捷方法，签名同 `($field, $title, $value = '')` |

常用链式：`->placeholder('请输入')`、`->maxlength(100)`、`->autoSize($minRows, $maxRows)`（textarea）、`->required('必填提示')`

### 3.2 数字（InputNumberFactoryTrait）

```php
Form::number($field, $title, $value = null)
    ->min(0)->max(100)->step(1)->precision(2)   // 常用 props 魔法方法
```

### 3.3 单选/多选（Radio / CheckBox）

```php
Form::radio($field, $title, $value = '')->options([
    Form::option(1, '开启'), Form::option(0, '关闭'),
]);
Form::checkbox($field, $title, array $value = [])->options([...]);
```

- `options()` 传二维数组也可：`[['value' => 1, 'label' => '开启'], ...]`
- **条件联动**（真实示例 `backend/modules/service/kefu/service/service/StoreServiceAutoReplyServices.php:104`）：

```php
Form::radio('type', '回复类型', $data['type'] ?? 'text')
    ->appendControl('text', [   // 当值 = text 时显示下方字段
        Form::input('data', '回复内容', $data['data'] ?? '')->required(),
    ])
    ->appendControl('image', [  // 当值 = image 时显示图片选择
        Form::frameImage('data', '回复图片', $src, $data['data'] ?? '')
            ->icon('el-icon-picture-outline')->width('950px')->height('560px')
            ->props(['footer' => false]),
    ]);
```

### 3.4 下拉选择（Select）

```php
Form::select($field, $title, $value = '')        // value 传数组自动 multiple
Form::selectMultiple($field, $title, array $value = [])
    ->options([...])->multiple(false)->filterable(true)   // 常用链式
```

### 3.5 开关（Switches）

```php
Form::switches($field, $title, $value = '0')
    ->openStr('开启')->closeStr('关闭')   // 文案；取值注意库中存 '1'/'0'
```

### 3.6 日期时间（DatePicker / TimePicker）

```php
Form::datePicker($field, $title, $value, $type)   // 通用，type 取 DatePicker::TYPE_*
Form::date / dates / dateRange / dateTime / dateTimeRange / year / month / monthRange
Form::timePicker($field, $title, $value = '', $isRange = false)
Form::time / timeRange($field, $title, $startTime, $endTime)
```

### 3.7 评分/颜色/滑块

```php
Form::rate($field, $title, $value = 0)->allowHalf(true)->max(5);
Form::color($field, $title, $value = '');
Form::slider($field, $title, $value = 0);        // value 传数组自动 range
Form::sliderRange($field, $title, $start = 0, $end = 0);
```

### 3.8 级联（Cascader）

```php
Form::cascader($field, $title, $value, $type)->options([...])->props(['checkStrictly' => true]);
Form::city($field, $title, $province = [], $city = '');                       // 省市二级（内置数据）
Form::cityArea($field, $title, $province = [], $city = '', $area = '');       // 省市区三级（内置数据）
```

### 3.9 树形（Tree）

```php
Form::tree($field, $title, $value = [], $type = Tree::TYPE_CHECKED);
Form::treeSelected($field, $title, $value = []);   // 取选中值模式
Form::treeChecked($field, $title, $value = []);    // 取勾选值模式
Form::treeData($id, $title, array $children = []); // 构造树节点
```

### 3.10 上传（Upload，action 为上传接口）

```php
Form::uploadImage($field, $title, $action, $value = '');    // 单图
Form::uploadImages($field, $title, $action, array $value = []);  // 多图
Form::uploadFile / uploadFiles                              // 单/多文件
Form::upload($field, $title, $action, $value, $type)        // 通用
    ->accept('.jpg,.png')->limit(5)->headers([...])->data([...])  // 常用链式
```

### 3.11 弹窗选择 frame（素材/用户/商品回填，二开高频）

| 方法 | 值类型 |
|------|--------|
| `frameInput($field, $title, $src, $value = '')` | 单值字符串 |
| `frameInputs($field, $title, $src, array $value = [])` | 多值数组 |
| `frameImage($field, $title, $src, $value = '')` | 单图 |
| `frameImages($field, $title, $src, array $value = [])` | 多图 |
| `frameFile` / `frameFiles` | 单/多附件 |

系统现成选择页与回填写法（目标页面需支持 `fodder` 参数；**src 必须带前导斜杠 + .html 后缀**，缺前导斜杠会被 iframe 拼成相对路径，缺 .html 会 404）：

```php
$adminPrefix = rtrim(config('app.admin_prefix', 'admin'), '/');
// 素材库选图
Form::frameImage('pic', '封面图', '/' . $adminPrefix . '/widget.images/index.html?fodder=pic', $data['pic'] ?? '')
    ->width('950px')->height('560px')->props(['footer' => false]);
// 选择用户
Form::frameInputs('uid', '选择用户', '/' . $adminPrefix . '/system.User/list.html?fodder=uid')
    ->width('950px')->height('560px')->props(['footer' => false]);
// 选择商品
Form::frameInputs('product_id', '选择商品', '/' . $adminPrefix . '/store.StoreProduct/index.html?fodder=product_id')
    ->width('950px')->height('560px')->props(['footer' => false]);
// 选择优惠券（标签选择器：el-tag 展示券名可删除 + 弹窗选择，值为对象数组 [{id,title,...}]，发券消费端已兼容）
Form::couponSelect('coupon_ids', '选择优惠券', $value);
```

> 常用链式：`->width('950px')->height('560px')->icon('el-icon-picture-outline')->modal(['title' => '选择'])`。更多现成选择页与先例见 schema-page.md 4.4.1 节。

### 3.12 其他

```php
Form::hidden($field, $value);          // 隐藏域（只存值不渲染）
Form::group($field, $title, $value);   // 嵌套数组/重复表单组
Form::setOptions($call);               // CRMEB 扩展：数组直接返回，闭包则执行取值（选项惰性加载）
Form::editor($field, $title, $value);  // 富文本（type=SchemaEditor，仅 SchemaPage 渲染链路可用，见 6.5）
```

**注意**：富文本使用 `Form::editor()`（CRMEB 扩展的自定义组件），**仅 SchemaPage 渲染链路支持**；传统 `from.vue`/`modalForm.js` 链路未注册该组件，UMD 前端扩展（见 [04-前端扩展/backend-frontend.md](../04-前端扩展/backend-frontend.md)）中需自行处理富文本。

## 四、通用链式方法（所有组件可用）

| 方法 | 说明 |
|------|------|
| `->field($field)` / `->title($title)` / `->value($value)` | 修改字段名/标签/默认值 |
| `->info($info)` | 标签旁的提示说明文字 |
| `->col(24)` | 栅格布局（传 `Form::col(['span' => 24])` 或整数） |
| `->className('xxx')` | 自定义样式类 |
| `->required('必填提示')` | 快捷必填（内部生成 Validate） |
| `->validate([...])` / `->appendValidate($v)` | 挂载校验规则 |
| `->control([...])` / `->appendControl($value, array $rule)` | 值联动显示其他字段 |
| `->options([...])` / `->setOptions(closure)` | 选项（select/radio/checkbox/cascader），支持闭包惰性求值 |
| `->props([...])` / `->prop($name, $value)` | 透传组件 props |
| `->emit([...])` / `->emitPrefix(...)` | 前端事件 |
| `->hiddenStatus(true)` / `->visibilityStatus(true)` | 隐藏/显示控制 |
| `->style([...])` | 内联样式 |

**props 魔法方法**：各组件 props 中声明的属性可直接当方法调用（自动类型转换），如 `->width('950px')`、`->placeholder('请输入')`、`->accept('.jpg')`、`->limit(5)`、`->multiple(true)`、`->filterable(true)`、`->min(0)`、`->max(100)`、`->step(1)` 等。

## 五、验证规则（ValidateFactoryTrait）

工厂方法生成 Validate 对象，链式配置后经 `->validate()` / `->appendValidate()` 挂到组件：

```php
$field[] = Form::input('phone', '手机号', '')->appendValidate(
    Form::validateStr()->required()->pattern('/^1[3-9]\d{9}$/')->message('手机号格式不正确')
);
$field[] = Form::number('sort', '排序', 0)->appendValidate(
    Form::validateNum()->required()->range(0, 99999)
);
```

| 工厂方法 | 类型 |
|----------|------|
| `validateStr` / `validateArr` / `validateNum` / `validateInt` / `validateFloat` | 字符串/数组/数值/整数/浮点 |
| `validateDate` / `validateEmail` / `validateUrl` / `validateHex` / `validateEnum` / `validateObject` | 日期/邮箱/URL/十六进制/枚举/对象 |

Validate 对象链式：`->required()`、`->range($min, $max)`、`->min()` / `->max()`、`->length($n)`、`->enum([...])`、`->pattern(正则)`、`->message('提示')`、`->trigger('blur'|'change')`

## 六、前端配合使用说明

> 前端在 `frontend/admin/src/main.js` 全局注册 `app.use(formCreate)`（`@form-create/element-ui`），页面模板可直接写 `<form-create>`。后端 `create_form()` 输出的 `{rules, title, action, method, info, status}` 是所有前端载体共同消费的标准协议。

### 6.1 数据流协议

| 返回字段 | 前端用途 |
|----------|---------|
| `rules` | form-create 渲染规则（组件类型/字段名/默认值/校验），绑定到 `:rule` |
| `title` | 弹窗/抽屉标题 |
| `action` | 提交地址。传统链路**必须有值**；SchemaPage 链路为空 → 自动提交网关 save 端点 |
| `method` | 提交方式（POST/PUT/GET/DELETE），前端转小写调 axios |
| `info`/`status` | 附加说明/启用标记（前端基本不消费） |

`create_form()` 本身**不输出 config**；前端渲染时会自动补默认 config（见 6.6）。

### 6.2 三种前端载体对比

| 载体 | 文件（frontend/admin/src/） | 使用场景 | 提交方式 |
|------|------------------------------|----------|---------|
| 命令式弹窗 | `utils/modalForm.js` | 传统页面 `proxy.$modalForm(apiPromise)` 一行调起（**最常用**） | `request(data.action, data.method)`，action 必须有值 |
| 弹窗组件 | `components/from/from.vue` | 传统页面以 `$refs.edits.modals` 控制的 el-dialog | `request(FromData.action, FromData.method)` |
| SchemaPage 表单 | `components/SchemaPage/components/SchemaForm.vue` | 通用页面系统的抽屉表单 + 整页表单 | **只渲染不提交**，暴露 `submit()` 由外层决定目标 |

### 6.3 传统页面用法

**$modalForm（推荐）**——页面 `<script setup>` 中：

```js
const { proxy } = getCurrentInstance();

// 新增：传"获取表单结构"GET 接口的 Promise，自动弹窗渲染，点确定 POST 到 action
proxy.$modalForm(createApi());
// 编辑：同一接口带 id，后端回填默认值；提交成功后刷新列表
proxy.$modalForm(editApi(id)).then(() => getList());
```

`modalForm.js` 内部流程：`ElMessageBox` 开弹窗 → `<form-create :rule="data.rules" :option="data.config">` 渲染 → 确定时 `fApi.submit()` 校验并收集 → `request[data.method.toLowerCase()](data.action, formData)` 提交 → resolve 整个响应（可 `.then()` 刷新列表）。

**from.vue 组件式**——父页面模板放 `<edit ref="edits" :FromData="FromData" @submitFail="getList" />`，请求表单结构后赋值 `FromData` 并 `$refs.edits.modals = true` 控制显隐；点确定在 `onSubmit` 中按 `FromData.action/method` 发请求，成功后默认自动调父级 `getList()`（`update` prop 可关）。

### 6.4 SchemaPage 渲染链路（通用页面系统）

SchemaForm.vue 是**纯渲染层**：隐藏 form-create 内置按钮（`submitBtn/resetBtn: false`），`defineExpose({ submit })` 返回 Promise 收集数据，**渲染与提交解耦**。两处接入点：

- **列表页抽屉**（`templates/ListTemplate.vue`）：`SchemaDrawer`（右侧抽屉容器）内嵌 SchemaForm 承载新增/编辑，`onFormSubmit` 中的提交分支：

```js
const requestPromise = schema.action
  ? request({ url: schema.action, method: schema.method || 'POST', data: formData })
  : schemaSaveApi(pluginKey, pageKey, { id: editId, ...formData });  // action 为空 → 插件网关 save 端点
```

- **整页表单**（`templates/FormTemplate.vue`，Provider 页面声明 `type=form`）：同样内嵌 SchemaForm，路由 `?id=xx` 传主键回填；提交分支同上，成功后停留本页（refresh/redirect/download 指令照常执行）

因此后端 Provider 的 `form()` 中 `create_form()` 第 3 参传 `''` 即可实现插件**零路由**增改；传具体地址则走自定义路由（与传统模式一致）。frame 弹窗选择组件（素材/用户/商品）为 form-create 内置能力，该链路**零前端代码**。

### 6.5 富文本 SchemaEditor

`Form::editor($field, $title, $value)` 生成 `type=SchemaEditor` 自定义组件规则，由 SchemaForm.vue 启动时 `formCreateFactory.component('SchemaEditor', SchemaEditor)` 注册的真实组件渲染（内部适配系统 wangEditor），值与提交行为与普通字段一致。**仅 SchemaPage 渲染链路可用**——SchemaEditor 只在 SchemaForm.vue 中注册，传统 `from.vue`/`modalForm.js` 渲染同规则会因组件未注册而失败。

### 6.6 前端默认注入的 config

前端各载体渲染前会自动补默认配置（`modalForm.js` / `from.vue` 内硬编码）：

- `submitBtn: false`、`resetBtn: false`——提交/重置按钮由弹窗页脚统一接管
- `form.labelWidth: '105px'`（modalForm）/ `'100px'`（from.vue）
- 全局 `upload.onSuccess`：上传成功取 `rep.data.src` 回填 `file.url`
- 每个 rules 元素的 `title` 自动追加中文冒号"："

如需自定义 config（如 `formData` 合并、弹窗 class），`$modalForm(promise, { formData, class })` 支持第二参覆盖。

## 七、易错速记

- 方法名：`number()` 非 `inputNumber`、`switches()` 非 `switch`、`color()` 非 `colorPicker`
- `create_form()` 的 `$method` 仅支持 POST/PUT/GET/DELETE
- frame 回填依赖目标页面支持 `fodder` 参数；`fodder` 值 = 组件 field 名
- 富文本用 `Form::editor()`，**仅 SchemaPage 渲染链路可用**（SchemaEditor 只在 SchemaForm.vue 注册）；传统 from.vue/modalForm.js 链路渲染会失败
- 传统链路（modalForm/from.vue）`action` 必须有值，否则提交落空；SchemaPage 链路 `action` 传 `''` 才走网关 save
- select 的 `$value` 传数组即自动 multiple；slider 同理
- 编辑回填：第 3 参直接填库中值；数组访问用 `??` 防 PHP 8 报错
- 前端会把字段 title 自动追加"："，后端 title 不必自带冒号
