---
name: crmeb-admin-ui
description: CRMEB v8 管理后台（frontend/admin）UI 规范技能 - 设计 Token（颜色/字号/间距/圆角/遮罩/动画）、组件全局定制（按钮/弹窗/抽屉/卡片/表格/表单/输入框/标签/分页/树/时间选择/下拉/消息等约 30 类）、页面骨架、表格/搜索/分页写法、按钮与权限指令、弹窗/抽屉交互、表单规范、公共组件速查、图标体系、SchemaPage 通用页面系统。开发或修改管理后台页面/组件/样式、新增插件管理页面、审查前端 UI 一致性时加载本技能。
---

# CRMEB v8 管理后台 UI 规范

> 提炼自 `frontend/admin/src` 真实代码（2026-09），技术栈：**Vue 3.5 + Element Plus 2.9 + Pinia 2 + vue-router 4 + Vite 5，纯 JS（不用 TS），全部 `<script setup>`**。
> 设计验收值以 [前端规范第四节](../../rules/前端规范.md#四管理端-ui-设计验收标准) 为准；本技能维护实现方法与代码现状。存量业务修复不做无差别风格迁移。

## 设计落地流程（先读）

1. 确定任务是新页面、UI 整改还是存量业务修复，再读取 [设计验收标准](../../rules/前端规范.md#四管理端-ui-设计验收标准)。下文全局样式表和存量示例用于定位代码，不作为与规则竞争的第二套设计标准。
2. 复用同类页面或 SchemaPage 的数据、权限与交互逻辑；对照规则检查实际尺寸，不能仅复制旧类名就认定满足设计。
3. 在浏览器检查 computed style 和最终盒模型，按 `theme/index.scss` → `styles/index.scss` 覆盖链定位。局部整改用页面根类下的 scoped 样式；公共整改在共享层处理并验证消费者，避免每页复制全套组件覆盖。
4. 只在任务包含样式实施时改 CSS。未适配的公共类可用局部修饰类补齐，不因单页任务改变全局工具类或 SchemaDrawer 默认契约。传送到 body 的 dialog/tooltip 使用有业务前缀的专用 class，确认作用到真实挂载节点。
5. 验证长内容、搜索折叠/展开、窄容器、亮/暗主题和非默认主题，以及权限过滤、失败回滚与提交中状态。交付分别说明文档标准、实际实现和已验证范围。

### 参考文档冲突裁定与采用边界

来源为用户提供的 `prev-admin/docs/design-spec.md`（2026-09-15），未检查其引用的原始图片。以下记录本仓库采用理由，最终取值只维护在规则第四节。

| 原文冲突或歧义 | 处理依据 |
| --- | --- |
| 表头高度、列表按钮 padding 正文与速查不同 | 采用组件正文明确参数，速查不覆盖正文 |
| 通用字号与弹窗标题不同；抽屉与弹窗 Tabs 字号不同 | 采用对应组件专节，详情抽屉沿用弹窗 Tabs |
| 标签与大型弹窗圆角多值 | 标签采用令牌总览专用档，弹窗采用模态专节，不另增大型弹窗档 |
| 开关/选项、说明间距在清单与全局说明不同 | 采用表单全局说明与间距总表一致的参数 |
| 按钮示例居中、模态 footer 右对齐等布局不同 | 按列表/表单/模态场景区分，顶部动作组不作为 footer 范式 |
| 参考文档 `text-primary` 被解释为主文本 | 按本项目真实令牌映射，见第一节，不交换全局变量定义 |
| `--prev-bg-hover`、`--prev-bg-disabled`、`--bg-page` 等示例变量 | 当前主题未定义；hover/禁用浅底可映射 `--prev-bg-color`、页面底映射 `--prev-bg-main-color`；确需新令牌时在主题层补齐亮暗定义与桥接，不直接引用空变量 |
| `12px/400`、字重 `500px`、`--input-heigh` | 拆成合法字号/字重属性并修正拼写，不照抄伪代码 |
| 上传最多 10 张、首图主图、拖拽；分页“20 页” | 属业务示例，上限、排序与主图契约由业务/API 决定；需要该上传外观时可参考 60px 预览格并复用上传能力 |
| 导航宽度、遮罩、阴影、动画、步骤完成色等推断 | 保留项目布局与组件实现，不升级为强制约束 |
| 参考项目第 16 节称已全局落地 | 仅描述 prev-admin，不构成本仓库完成证明 |

### 当前适配定位（2026-09-15 静态核对）

| 当前实现 | 定位 | 实施时关注 |
| --- | --- | --- |
| 筛选宽 250px、范围子项 117px | `src/styles/style.scss` 的 `.form_content_width / .form_range_content_width` | 复合控件整体与子项分别适配 |
| 缩略图 36×36px | `src/styles/style.scss` 的 `.tabBox_img` | 图片与容器一起检查 |
| 表头 47px、单元格 50px | `src/styles/style.scss`、`src/theme/element.scss` | 检查行高、padding、边框与覆盖，区分纯文本和复杂行 |
| dialog/drawer 多档宽度与不同 header/body padding | 第六节、`src/theme/element.scss`、`src/theme/variables.scss` | 先分类详情/编辑，再选型，校验短视口滚动 |
| SchemaDrawer 默认 size=720 | `src/components/SchemaPage/` | 共享组件现状不是详情设计目标，改变默认值需验证消费者 |

本次规范整理未改上述源码；后续实施完成后更新现状说明并附验证范围。

## 一、设计 Token 与全局样式现状

### 1.1 样式架构与加载顺序

```
main.js 引入顺序（后者覆盖前者）：
  element-plus/dist/index.css
  @/theme/index.scss        ← 主题：聚合 base/app/other/element/media/variables/dark
  ./styles/index.scss       ← 全局工具类：聚合 common.scss + style.scss
```

- **主题定制层**（`theme/`）：`app.scss` 定义 `--prev-*` 变量并反向桥接 EP 的 `--el-*`；`element.scss` 定制 EP 组件默认样式；`variables.scss` 存量组件适配；`dark.scss` 暗色。
- **工具类层**（`styles/`）：`style.scss` 是主力（acea-row、间距、字号、表格样式等），`common.scss` 滚动条等（勿随意改），`el-icon-compat.css` 脚本生成勿手改。
- **换肤机制**：`layout/navBars/breadcrumb/theme.js` 内置 5 套主题（蓝 `#0256FF` / 绿 `#41b584` / 紫等），JS 动态下发 `--prev-*` 到 `:root`。**业务代码禁止写死主题色 hex，一律用 `--prev-*` 变量**。

### 1.2 颜色体系

**主题色（app.scss `:root`，默认蓝）**：

| 变量 | 值 | 用途 |
|---|---|---|
| `--prev-color-primary` | `#0256FF` | 主色（按钮/链接/选中态/进度条） |
| `--prev-color-primary-light-9` | `#e5eeff` | 主色浅底（表头背景、选中行背景、标签浅底）。全档 `light-1..9` + `dark-2` 已在 `app.scss` 用 `color-mix` 从 `--prev-color-primary` 推导，换肤时 JS 行内样式覆盖 |
| `--el-color-primary` | `var(--prev-color-primary)` | EP 全套 `--el-color-primary-*` 已用 `color-mix` 桥接到 `--prev-*` |

**文本色**（注意：`--prev-*` 命名与 EP 相反，实际主文本是 `text-regular`）：

| 变量 | 值 | 用途 |
|---|---|---|
| `--prev-color-text-regular` | `#303133` | 主文本（标题、正文、弹窗标题） |
| `--prev-color-text-primary` | `#606266` | 次级文本（描述列表 label、tree 文字） |
| `--prev-color-text-secondary` | `#909399` | 辅助/提示文字 |
| `--prev-color-text-placeholder` | `#c0c4cc` | 占位符、禁用 |

**背景 / 边框**：

| 变量 | 值 | 用途 |
|---|---|---|
| `--prev-bg-main-color` | `#f0f2f5` | 页面底色 |
| `--prev-bg-white` | `#ffffff` | 卡片/弹窗底（同时桥接为 EP 的 `--el-bg-color`） |
| `--prev-bg-color` | `#f5f7fa` | 悬浮/选中浅底（表格 hover 行、树节点 hover、禁用底、input-number 控件底） |
| `--prev-color-hover` | `rgba(0, 0, 0, 0.04)` | 下拉项/autocomplete hover 浅底（半透明） |
| `--prev-bg-menuBar` | `#282c34` | 侧边菜单深色底 |
| `--prev-border-color-base/light/lighter/extra-light` | `#dcdfe6 / #e4e7ed / #ebeef5 / #f2f6fc` | 边框四档 |
| —（存量硬编码） | `#f2f2f2`、`#eee`、`#e8eaec` | 浅分割线（`:deep` 内常见，新代码优先变量） |

**状态/语义色**：EP 的 `--el-color-success/warning/danger/info`（默认绿/橙/红/灰）直接可用；`--prev-color-success/warning/danger/info` 已于 2026-09-09 在 `app.scss` 补齐为同值，`.color-success / .color-warning / .color-danger / .color-info` 工具类可用。存量硬编码状态色（跟随现状、新代码不新增）：`.c-red: #f56464`、`.plusColor: #e83323`（红）、`.reduceColor: #16ac57`（绿）。

### 1.3 字号体系

**字号基准**：`theme/app.scss` 设 `html, body, #app { font-size: 14px }`，随后 `styles/style.scss` 的 `html, body { font-size: 12px }` 覆盖了 html/body——但 **`#app` 仍是 14px，页面内容实际继承 14px**；控件层由全局规则统一压到 12px（见下表）。

| 场景 | 字号 | 依据 |
|---|---|---|
| 全局基准 / 表格内容 / 表格表头 / 按钮 / 提示文字 / **表单与搜索 label** / 输入框 / 下拉 / checkbox 文字 | **12px** | `style.scss`、`variables.scss` 统一覆盖 |
| 弹窗标题、抽屉标题、卡片标题（header-title）、tree 节点 | **14px**（500 字重） | `element.scss` / `variables.scss` / `style.scss` |
| 页面大标题（`.ivu-page-header-title`） | **18px**（500 字重，`#17233d`） | `style.scss` |
| 表头固定样式 | 47px 高、500 字重、`#515a6e`、背景 `--prev-color-primary-light-9` | `style.scss` |
| 提示文字 `.tip / .tips-info`（全局） | 12px / 18px 行高 / `--prev-color-text-secondary` | `style.scss` |
| 字号工具类 | `.font10` ~ `.font32`（`app.scss` @for 生成，均带 `!important`） | — |

### 1.4 间距与尺寸

**间距工具类**（`app.scss` @for 生成 `mt/mr/mb/ml/pt/pr/pb/pl` × 5~35px，均 `!important`；`style.scss` 另有补充）：

| 常用档位 | 场景约定 |
|---|---|
| 5 / 10 | 元素内小间距（图标与文字、标签间距） |
| 14 / 15 / 16 | 组件间距：**卡片之间统一 16px**（`.ivu-mt` / `.mt16`）、el-button 间距 14px（EP 定制） |
| 20 | **卡片内边距基准**（el-card body 默认 20px、搜索卡 `.padding-add: 20px 20px 0`） |
| 22 | 分页区 `.page { margin-top: 22px }`（固定写法） |
| 25 / 30 / 35 | 大区块分隔，少用 |

**固定尺寸约定**：

| 场景 | 值 | 类/来源 |
|---|---|---|
| 搜索控件宽度 | 250px / 范围 117px | `.form_content_width` / `.form_range_content_width` |
| 输入框高度 | 32px（small 档，行高同步 32px） | `variables.scss` |
| 表格缩略图 | 36×36、圆角 4px | `.tabBox_img` |
| 状态开关 | 宽 55px、label 12px 白字 | `.defineSwitch` |
| 表格首列左内边距 | 26px | `style.scss` 全局 |
| 表格行高 | 单元格 50px 定高（`element.scss`） | `element.scss` |
| 抽屉结构 | header 高 50px（`padding: 0 15px` + 底边线）；body 内边距 `20px 35px` | `variables.scss` |
| 树节点行高 | 48px | `.tree` |
| 侧边菜单宽度 | 180px（收起 64px） | `layout-aside-width-default` |
| 布局留白 | 主内容区 `padding: 16px` | `.layout-scrollbar` / `.content-wrapper` |

**宽度百分比类**：`.perW100/90/50/35/30/20`、`.w-80` / `.w-100`（80/100px）、`.w100`。

### 1.5 圆角 / 阴影 / 遮罩

| Token | 值 | 用途 |
|---|---|---|
| 圆角 | **4px**（按钮、缩略图、卡片内容块）/ **6px**（dialog、MessageBox 表单、empty-box） | 统一两档，勿用其他值 |
| 卡片阴影 | `shadow="never"`（页面卡片一律无阴影，靠底色区分） | el-card 用法 |
| 菜单阴影 | `1px 1px 4px rgba(0, 21, 41, 0.08)` | 侧边栏 |
| 遮罩 | `rgba(0, 0, 0, 0.5)`（`.maskModel`）/ 弹窗 EP 默认 | — |
| 滚动条 | 宽 6px、滑块 `#808695` 圆角 4px、轨道透明 | `.i-scrollbar`（隐藏用 `.i-scrollbar-hide`） |
| 过渡动画 | 路由切换 `slide-left / slide-right / opacitys`，均 `0.3s ease`（`theme/common/transition.scss`） | `<transition>` name |

### 1.6 EP 组件全局定制（写页面时无需重复设置）

`theme/element.scss` + `theme/variables.scss` + `styles/style.scss` 已统一覆盖（variables.scss 后加载，覆盖 element.scss 同名规则）：

**布局容器类**

| 组件 | 定制结果 |
|---|---|
| `el-button` | 字号 12px、`padding: 9px 15px`、圆角 4px、按钮间距 14px、字重 400；primary 悬浮/聚焦 `light-3`；内嵌 iconfont 图标 14px + 右距 5px |
| `el-dialog` | 圆角 6px、根内边距归零、去阴影、header `padding:15px` + 底边线 `#eee`、标题 14px/500/`#303133`、body `padding:15px` 且 `max-height:720px` 自动滚动、footer `10px 15px 15px`、关闭按钮 hover 主色 |
| `el-drawer` | header 高 50px、`padding: 0 15px`、底边线 lighter、标题 14px/500/`#303133`；body `padding: 20px 35px` 自动滚动；关闭按钮 hover 主色 |
| `el-card` | 全局无边框（`border: none`）、header `padding: 15px 20px`、header 底边线 `--prev-color-primary-light-9`；`:bordered="false"` 是 iView 历史写法（EP 无此属性，可留可不写） |
| `el-message-box` | 垂直偏上定位（top 20%）；标题 text-primary、内容 text-regular、关闭 hover 主色 |

**数据展示类**

| 组件 | 定制结果 |
|---|---|
| `el-table` | 字号 12px、表头 47px 高/12px/500/浅蓝底（`--prev-color-primary-light-9`）、单元格 50px 定高、行边框 `#f5f5f5`、悬浮行 `--prev-bg-color`、首列 `padding-left:26px`、展开行 `padding-left:60px`、表头 wrapper 圆角 4px |
| vxe-table | 字号 12px（`--vxe-ui-font-size-default`）、表头浅蓝底 + 圆角 4px + 500 字重 `#303133`、无边框线 |
| `el-tag` | 默认（主色）tag：白底 + 主色描边 + 主色文字，close 按钮 hover 主色底白字；success/warning/danger/info 语义 tag 用 EP 默认配色 |
| `el-tabs` | 页面页签 54px 高 / 14px 字号 / 选中 500 加粗、激活条 3px；激活/悬浮主色、nav 底线 1px lighter、默认项文字 text-primary；弹窗/抽屉内 13px / 40px（`theme/element.scss` 全局定制，页面不重复覆盖字号/高度/激活条，见 13.2） |
| `el-pagination` | 当前页主色底白字（is-background 模式）、页码 hover 主色、总数/跳转 text-regular |
| `el-tree` | 白底、文字 text-regular、节点 hover `--prev-bg-color` |
| `el-progress / el-loading` | 进度条/加载 spinner 主色 |
| `el-divider` | 分割线底色 `--prev-color-primary-light-9`、文字 text-primary 且不换行 |
| 空数据/展开 | 表格展开行左距 60px；空态用 `.empty-box`（见第九节） |

**表单控件类**

| 组件 | 定制结果 |
|---|---|
| `el-form` | label 12px、text-regular 色、右距 6px；small 档行高 31px |
| `el-input / el-textarea` | 字号 12px、边框 base、hover 加深（border-hover）、文字 text-regular；small 高 32px；原生 number 已去上下箭头 |
| `el-input-number` | 默认宽 180px（`element.scss`，覆盖 EP 150/120px；页面显式宽度类与 form-create 的 100% 仍优先）；数字左对齐（`style.scss`，绝对值 left + 0,3,0 特异性覆盖 EP 居中，不依赖加载顺序）；small 档隐藏加减控件、左右内边距 15px；控件底 `--prev-bg-color` |
| `el-select` | wrapper 字号 12px；下拉 max-height 274px、item hover `--prev-color-hover`、选中项主色文字 |
| `el-radio / el-checkbox` | 选中态主色、文字 12px（checkbox）/text-primary（radio/checkbox 通用色）、bordered 边框 base |
| `el-switch.defineSwitch` | 宽 55px、开关文字 12px 白色、开/关文字切换显示、选中态主色 |
| `el-date-picker / el-time-picker` | 面板白底、边框 light；today/悬浮/选中主色、选中日主色底白字、区间选中浅蓝底；表单行内 range 编辑器已全局 `flex-grow: 0`（`element.scss`），不再被 flex 拉伸占满，无显式宽度时按 EP 默认（daterange 350px / datetimerange 640px） |
| `el-upload` | 拖拽区/卡片白底 light 边框、hover 主色；tip 文字 text-regular |
| form-create MessageBox（`.modal-form`） | 宽 540px、圆角 6px、header 15px、标题 14px/500/`#333`、内容 `30px 15px 10px`、labelWidth 105px |
| form-create 内 `el-select / el-input-number` | 宽度 100% |

**反馈提示类**

| 组件 | 定制结果 |
|---|---|
| `el-message` | `padding: 15px`、宽度自适应内容（variables.scss 覆盖了 element.scss 的 min-width 100px） |
| `el-dropdown` | 字号 12px、item hover 主色文字 + 浅蓝底（primary-light-9） |
| `el-popper`（tooltip/popover） | 边框 `#f2f2f2` |
| `el-alert` | ⚠️ 存量陷阱：info 描述/关闭按钮被硬编码为黄色 `#f7ba1e`，勿仿、新代码不依赖 |
| `el-breadcrumb` | 链接黑色 70% 透明度、分隔符边框色 |
| `el-step` | 已完成步骤主色 |
| `a` 链接 | 主色 + cursor:pointer |

## 二、页面文件组织

页面统一放 `src/pages/<module>/<feature>/`，三种形态：

| 形态 | 结构 | 适用 |
|---|---|---|
| 简单单文件 | `index.vue`（表格卡+抽屉）+ `edit.vue`（抽屉内表单组件） | 驱动类等简单页面（如 `pages/driver/smsDriver/`） |
| 复杂多文件 | `index.vue` + `components/`（或 `handle/`）+ `tableFrom.vue` / `tableList.vue` | 核心业务页（如 `pages/user/list/`） |
| 左树右表 | `el-row`/`el-col` 左树右表 + 表格卡 | 分类/标签管理（如 `pages/user/label/`） |

- 目录/页面/组件命名小驼峰；路由 `meta.auth: ['权限串']`，按钮级权限 `v-auth="['权限串']"`。
- 新增插件管理页面**优先走 SchemaPage 通用页面系统**（见第八节），不写前端代码。

## 三、`<script setup>` 写法约定

```js
<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import SmsDriverEdit from './edit.vue';
import { smsDriverListApi, smsDriverSetStatusApi } from '@/api/driver/smsDriver';

defineOptions({ name: 'smsDriver' });   // 必写

const list = ref([]);
const loading = ref(false);
const showEditDrawer = ref(false);

onMounted(() => { getList(); });
</script>
```

- **`defineOptions({ name })` 必写**（keep-alive/多标签依赖）。
- 数据以 `ref` 为主；查询对象 `ref({ page: 1, limit: 15, ... })` 或 `reactive({...})` 均可。
- **消息提示统一 `import { ElMessage } from 'element-plus'`**；不使用 `$msgErr`（不存在）。
- 子组件方法需被父级 `$refs` 调用时 `defineExpose`（如 `defineExpose({ modals })`）。
- API 函数模式：`export function userList(data) { return request({ url: 'user/user', method: 'get', params: data }); }`，统一 `import request from '@/libs/request'`（`libs/axios.js` 是废弃遗留勿用）。

## 四、列表页骨架（搜索卡 + 数据卡 + 分页区）

标准结构 = **搜索卡 + 表格卡 + 分页区**。以下为存量结构示意，旧控件宽度、图片尺寸和搜索按钮位置需按规则适配后再用于新页面：

```html
<template>
  <!-- 0. 页面标题头：紧凑 52px 标准（用 pages-header 组件或直接写该结构，勿用 el-card 包标题） -->
  <div class="i-layout-page-header header-title">
    <span class="ivu-page-header-title">用户列表</span>
  </div>

  <!-- 1. 搜索卡：body padding 归零，内容交给 .padding-add -->
  <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
    <div class="padding-add">
      <el-form :model="userFrom" label-width="80px" label-position="right" @submit.prevent inline>
        <el-form-item label="用户搜索：">
          <el-input v-model="userFrom.nickname" placeholder="请输入用户" clearable class="form_content_width" />
        </el-form-item>
        <el-form-item class="search-form-sub">
          <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          <el-button class="ResetSearch" v-db-click @click="reset('userFrom')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
  </el-card>

  <!-- 2. 表格卡：ivu-mt mt16 拉开间距 -->
  <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
    <el-table :data="list" v-loading="loading" highlight-current-row row-key="id">
      <el-table-column label="头像" min-width="80">
        <template #default="scope">
          <div class="tabBox_img" v-viewer><img v-lazy="scope.row.avatar" /></div>
        </template>
      </el-table-column>
      <el-table-column label="状态" min-width="100">
        <template #default="scope">
          <el-switch :active-value="1" :inactive-value="0" v-model="scope.row.status"
            @change="handleStatusChange(scope.row)" class="defineSwitch" />
        </template>
      </el-table-column>
      <el-table-column label="操作" fixed="right" width="120">
        <template #default="scope">
          <el-button link type="primary" v-db-click @click="edit(scope.row)">编辑</el-button>
          <el-button link type="danger" v-db-click @click="del(scope.row, '删除', scope.$index)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </el-card>

  <!-- 3. 分页区：固定容器类 -->
  <div class="acea-row row-right page">
    <pagination v-if="total" :total="total" v-model:page="tableFrom.page"
      v-model:limit="tableFrom.limit" @pagination="getList" />
  </div>
</template>
```

要点：

- **页面标题头统一 52px 紧凑标准**：全局类组合 `.i-layout-page-header.header-title`（白底、`10px 20px`、圆角 5px、下距 16px）+ `.ivu-page-header-title`（18px/500/`#17233d`/line-height 32px）；带返回用 `<pages-header :title :back-url>`（组件内部即该结构），右侧说明文字放组件默认 slot。禁止再用 el-card 包页面标题（旧实现总高约 67px）。
- **顶部操作按钮与表格同卡**：新增等顶部动作按钮渲染在数据卡内部、表格上方；间距为「上 14px / 下 14px」。实现：按钮行/按钮挂 `.action-row`（全局类，`margin-top: -6px` 抵消卡 20px 上内边距；见 `styles/style.scss`）+ 下距 `mb14` 或表格 `mt14`（等效）。`.action-row` 仅用于卡内首个动作元素；上方有搜索行/tabs 时不可挂。SchemaPage 动作区已内置同款偏移（跟在分类头后自动归零）。禁止按钮独立成卡、禁止下距超过 14px（历史问题见 13.2）。
- 搜索表单 `inline` + `label-width="80px"` + `@submit.prevent`；搜索控件宽度统一用 `.form_content_width`（250px）/ `.form_range_content_width`（117px）。
- 多条件搜索用 `div.acea-row.search-form` 包裹 + `collapse` 布尔做「展开/收起」。
- 表格默认 **el-table**（样式已全局定制，见 1.6）；**仅树形数据用 vxe-table**（`<vxe-table-column ... tree-node>`，插槽 `v-slot="{ row }"`）。
- 多选列 `<el-table-column type="selection">`，选中后顶部 `<el-alert show-icon>` 显示「已选择 N 项」。
- 页面卡片一律 `shadow="never"`，卡片间距 `.ivu-mt` / `.mt16`（16px）。

### 4.1 自定义列渲染固定写法

| 场景 | 写法 |
|---|---|
| 状态开关 | `el-switch :active-value="1" :inactive-value="0"` + `class="defineSwitch"` + change 里调接口，**失败回滚** `row.status = row.status === 1 ? 0 : 1` |
| 图片 | `div.tabBox_img`（36×36 圆角 4px）+ `v-viewer` + `img v-lazy` |
| 枚举/标签 | `el-tag`，`:type` 用映射函数返回 success/info/warning/danger |
| 超长文本 | `line1`（单行省略）/ `line2`（两行省略）或 `one-text-overflow / two-text-overflow` |
| 金额/时间 | 前端格式化展示，与周边列一致 |

## 五、按钮与指令

- **`v-db-click` 所有按钮/可点击元素几乎必带**（点击后 1 秒内禁用，防重复提交）。
- 权限按钮：`<el-button v-auth="['admin-user-save']" type="primary" v-db-click @click="...">添加</el-button>`。
- 按钮规格（全局已定制，见 1.6）：12px 字号、`9px 15px` 内边距、4px 圆角；**按钮组间距由 `.el-button + .el-button` 统一 14px**，不要手写 margin。
- 行内操作：新写法统一 `<el-button link>`（primary=编辑/详情，danger=删除），不再用旧 `<a> + el-divider` 与已废弃的 `type="text"`。
- 操作列 `fixed="right"`；动作多于 3 个时用 `el-dropdown` 折叠为「更多」。
- 顶部操作区：主操作 `type="primary"`，其余默认按钮；导出等次要操作可加 `class="mr10"`。

## 六、弹窗与抽屉交互模式

### 6.1 form-create 动态表单弹窗（modalForm）— 新增/编辑首选

后端下发 form-create schema（`create_form()`），前端一行调用：

```js
import modalForm from '@/utils/modalForm';

function add() { modalForm(groupAddApi(0)).then(() => getList()); }     // 新增
function edit(id) { modalForm(groupAddApi(id)).then(() => getList()); } // 编辑回填
```

内部走 `ElMessageBox` + `.modal-form` 全局类（540px 宽、labelWidth 105px、`closeOnClickModal: false`），成功自动 `ElMessage.success`，调用方只需 `.then()` 里刷列表。

### 6.2 删除确认（modalSure）

```js
import { modalSure } from '@/utils/public';

function del(row, tit, num) {
  modalSure({ title: tit, num, url: `user/user_label/del/${row.id}`, method: 'DELETE', ids: '' })
    .then((res) => { ElMessage.success(res.msg); getList(); });
}
```

未传 `success` 字段时自动走通用删除接口；删除类确认文案必须明确（如「删除后不可恢复」）。

### 6.3 dialog / drawer 存量模式与适配

下表记录存量尺寸，不是新增页面的推荐档位；新建和 UI 整改按规则第四节选型。表单抽屉示例保留现有编辑模式，不用于推导详情宽度。

| 容器 | 场景 | 尺寸档位 |
|---|---|---|
| `el-dialog` | 小表单、选择器、轻交互 | 400-470px（备注/改名）、540-600px（中小表单）、720px（中型）、1000-1200px（选择器/规格弹窗） |
| `el-drawer` | 编辑表单（字段多/含分组）、详情查看（内容长） | `720`（表单/详情）、`50%`、`80%`（配置类）、`1100`（大型详情） |

固定习惯：

- 表单类必带 `:close-on-click-modal="false"`；嵌套弹窗加 `append-to-body`。
- dialog 存量结构已全局定制（header 15px+底线、body 15px/max-height 720 滚动、footer `10px 15px 15px`）；业务修复复用现状，UI 整改按开头的设计落地流程处理差异。
- 抽屉含表单时 `:wrapper-closable="false"` 防误关；内容惰性渲染 `v-if="visible"` 以重置状态；子组件经 `@close` / `@success` 与父级通信。
- 抽屉底部固定按钮区：`div.fix_footer acea-row row-center`（取消 + 提交）。

```html
<el-drawer v-model="modals" size="720" title="用户信息填写" :wrapper-closable="false">
  <div class="demo-drawer__content">
    <UserEdit ref="userEditRef" v-if="modals" :user-data="userData" />
    <div class="fix_footer acea-row row-center">
      <el-button v-db-click @click="modals = false">取消</el-button>
      <el-button type="primary" v-db-click @click="setUser">提交</el-button>
    </div>
  </div>
</el-drawer>
```

### 6.4 消息与确认

- 提示只用 `ElMessage.success/error(res.msg)`；确认类一律 `ElMessageBox`（已被 modalSure / SchemaPage runAction 封装，不要手写裸 MessageBox）。

## 七、表单页规范

```html
<el-form ref="formRef" :model="editForm" :rules="editRules" label-width="120px" v-loading="editLoading">
  <el-divider content-position="left">基本信息</el-divider>
  <el-form-item label="驱动名称：" prop="driver_name">
    <el-input v-model="editForm.driver_name" placeholder="请输入驱动名称" />
  </el-form-item>
  <el-form-item label="状态：" prop="status">
    <el-switch :active-value="1" :inactive-value="0" v-model="editForm.status" class="defineSwitch" />
    <div class="tips-info">短信驱动为互斥模式，启用当前驱动将自动禁用其他驱动</div>
  </el-form-item>
  <el-form-item label="排序：" prop="sort">
    <el-input-number v-model="editForm.sort" :min="0" :max="9999" />
  </el-form-item>
  <div class="form-actions">
    <el-button v-db-click @click="$emit('close')">取消</el-button>
    <el-button type="primary" v-db-click @click="handleSubmit">保存</el-button>
  </div>
</el-form>
```

- `label-width` 分场景：搜索表单 80px / modalForm 内 105px / 配置表单 120px。
- 同屏字段分组可用 `<el-divider content-position="left">`；独立业务分区可用 el-tabs（如商品基础信息/规格/详情），保留切换时的输入与校验状态；提示复用 `.tips-info`，整改时核对说明间距。
- 校验 rules 常规 EP 写法（`trigger: 'blur' / 'change'`），提交前 `formRef.value.validate()`。
- 左右分栏用 `el-row` + `el-col` 响应式断点：`:xl="4" :lg="4" :md="6" :sm="8" :xs="0"`。
- 表单底部操作区用 `.form-actions`（或抽屉 `.fix_footer`），主按钮 primary + 取消默认。
- 图片选择用 `div.picBox > div.pictrue / div.upLoad` + 上传组件占位。
- 另一类是 form-create schema 弹窗组件 `edit-from`（`components/from/from.vue`，720px dialog），父级 `$refs.edits.modals = true` 打开。
- 统计页字段标签用 `.label_text`（12px `#606266`、80px 宽右对齐、右距 12px）。

## 八、SchemaPage 通用页面系统（新插件页面首选）

**插件零前端代码**：后端 `modules/{category}/{name}/schema/*Page.php`（继承 `BaseSchemaPageProvider`）声明页面配置，前端一条通配路由自动渲染：

- 访问路径：`/admin/plugin/:pluginKey/:pageKey`（如 `/admin/plugin/mcp/list`）；
- 容器 `src/components/SchemaPage/index.vue` 按 `config.type`（list / form / stat / detail）分发模板；
- 页面组成：SchemaCards 统计卡 → SchemaSearchBar 搜索 → 表格卡（顶部/批量动作区 → el-tabs 状态分类头 → SchemaTable → 全局 pagination，动作与表格同卡不独立成卡）→ SchemaDrawer（表单/详情抽屉，size=720）；
- 行操作模式封闭枚举：`drawer_form / drawer_detail / confirm_action / direct_action / navigate`；删除统一 `confirm_action` + 必填 confirm 文案；
- 组件库与交互细节见 `crmeb-dev` 技能 references/12-通用页面/schema-page.md 及仓库根《管理端通用页面组件系统设计方案.md》。

手写页模式仅用于：存量维护、复杂专属交互页（商品编辑、DIY 可视化等）。

## 九、样式写法规范

- 组件样式一律 `<style lang="scss" scoped>`；穿透 EP 内部类用 `:deep()`。
- **颜色引用优先用 `--prev-*` 变量**（EP 的 `--el-*` 已被桥接，EP 组件配色自动跟随，换肤只改 `--prev-*`）；禁止写死主题色 hex。
- **布局优先复用全局工具类，不自写 flex**：

| 类 | 用途 |
|---|---|
| `.acea-row` + `row-middle / row-right / row-between / row-center / row-center-wrapper / row-between-wrapper / row-column / row-column-between` | flex 布局全家桶 |
| `.padding-add` | 搜索卡内容容器（20px 20px 0） |
| `.ivu-mt` / `.mt16` / `.ivu-mb-16` | 卡片/模块间距 16px |
| `.page` | 分页容器（配合 `acea-row row-right`） |
| `.form_content_width` / `.form_range_content_width` | 搜索控件统一宽度 |
| `.tabBox_img` / `.defineSwitch` | 表格缩略图 / 状态开关 |
| `.tip` / `.tips-info` | 表单/卡片内提示文字 |
| `.fix_footer` | 抽屉底部固定按钮区 |
| `.label_text` | 描述列表字段标签 |
| `.line1` / `.line2` | 单行/两行省略 |
| `.empty-box` | 空状态占位（底 `#f3f5f7`、圆角 6px、iconfont 30px `#bbbfc8`） |
| `.i-scrollbar` / `.i-scrollbar-hide` | 自定义滚动条 / 隐藏滚动条 |
| `.cup` / `.pointer` | 手型光标 |
| `.w100 / .perW*` | 宽度百分比 |
| `.f-w-b` / `.font10~32` | 字重 / 字号 |
| `.fixed-card` | 底部固定提交卡（长表单页） |
| `.i-layout-page-header.header-title` | 页面标题头容器（白底、`10px 20px`、圆角 5px、下距 16px，总高 52px） |
| `.ivu-page-header-title` | 页面标题文字（18px / 500 / `#17233d` / line-height 32px） |

- `styles/common.scss` 勿随意改；`styles/el-icon-compat.css` 脚本生成勿手改。

## 十、公共组件速查

### 10.1 全局注册（main.js，模板直接用）

| 组件 | 用途 |
|---|---|
| `<pagination>` | 分页（`:total` 必填，`v-model:page/limit`，`@pagination`，默认 pageSizes `[15,20,30,50]`，翻页自动回顶） |
| `<pages-header>` | 页面标题头（统一 52px 紧凑标准；props `title` / `backUrl` / `description`）：description 渲染于标题右侧、12px 次要色、前置 14px 主色 `InfoFilled` 图标、与标题基线对齐；默认 slot 置于最右侧（动作按钮等）；描述文案仅说明性内容，不放操作 |
| `<date-range-picker>` | 日期范围搜索统一入口（v-model 值为 `[start, end]`）：内置 `libs/timeOptions` 快捷项（今天/昨天/本月/上月/最近N天/本年/去年）、分隔符「至」、默认 320px 对齐筛选控件基准宽并强制 `flex-grow: 0`（EP `.el-input__wrapper` 自带 `flex-grow: 1`，不压制会在 flex 搜索行内被拉伸占满）；`format`/`value-format`/`clearable` 等经 attrs 透传可覆盖。时间范围筛选**不要直接裸写 `el-date-picker` daterange**，避免快捷项缺失与宽度不一（快捷项数据唯一真源在 `libs/timeOptions.js`，Element Plus 格式 `{text, value: () => [start, end]}`） |
| `<icon>` | iView/EP 兼容图标组件（菜单/面包屑/DIY 历史模板用） |
| EP 全部图标 | `@element-plus/icons-vue` 逐个全局注册 |

### 10.2 高频按需引入

| 组件 | 用途 |
|---|---|
| `uploadImg` / `uploadPictures` / `uploadVideo2` | 图片上传 / 素材库选择（左分类树+右列表，`@getPic` 回传）/ 视频上传 |
| `wangEditor` | 富文本（wangeditor v5；自定义工具栏按钮经 `Boot.registerMenu` 模块级只注册一次；回调走组件事件） |
| `cropperImg` | 图片裁剪 |
| `echartsNew` | 图表封装（props `optionData`/`styles`，deep watch 重绘） |
| `dateRadio` | 「今天/昨天/最近7天…」快捷时间按钮组（`@selectDate`） |
| `cards-data` | 统计卡片行（props `cardLists: [{name, count, className}]`，按数量自适应栅格） |
| `iconFrom` | EP 图标选择器（form-create 场景经 `form_create_helper.set` 回填） |
| `goodsList` / `couponSelect` / `customerInfo` | 选商品 / 选优惠券 / 选商城用户弹窗 |
| `edit-from` | form-create schema 编辑弹窗 |
| `mobilePage` / `mobileConfig` / `rightConfig` | DIY 可视化三件套 |

## 十一、图标体系（四套并存，新代码首选 EP 图标）

1. **Element Plus 图标（新代码首选）**：`<el-icon :size="24"><Edit /></el-icon>`；动态渲染 `<component :is="iconName" />`。
2. **element-ui 类名兼容层（存量可继续用，不必迁移）**：`<i class="el-icon-arrow-down" />` 等，由 `styles/el-icon-compat.css`（mask 方案）映射为 EP 同名 SVG，颜色随 currentColor。
3. **iconfont 业务图标**：`<span class="iconfont" :class="item.className">`（全局 `styles/font/iconfont.css`）；客服专用 `<span class="iconfontYI icon-guanbi">`；按钮内 iconfont 图标 14px（element.scss 定制）。
4. **`<Icon type="ios-people">` 兼容组件**：iView 时代写法 → EP 图标映射，仅菜单/面包屑/DIY 历史模板使用。

## 十二、UI 一致性检查清单

先按 [前端规范第四节](../../rules/前端规范.md#四管理端-ui-设计验收标准) 验收设计参数，再检查以下实现项；公共类存在不等于其尺寸已合规：

- [ ] `defineOptions({ name })` 已写；按钮带 `v-db-click`；权限按钮带 `v-auth`
- [ ] 搜索卡、数据卡、分页区职责清楚；卡片 `shadow="never"`，旧 `:bordered="false"` 是无效历史属性，新示例无需添加
- [ ] 页面标题头用 `<pages-header>` 或 `.i-layout-page-header.header-title` 紧凑结构（52px），未用 el-card 包标题
- [ ] 表格默认 el-table（树形才 vxe-table）；状态列 `defineSwitch` + 失败回滚；图片列 `tabBox_img + v-viewer + v-lazy`；超长文本 `line1/line2`
- [ ] 字体、颜色、间距和尺寸按规则核对 computed style，未误用参考项目变量或存量尺寸
- [ ] 筛选条件按实际行数折叠，窄容器按钮不覆盖字段，重置/搜索分页状态正确
- [ ] 时间范围筛选用全局 `<date-range-picker>`（自带快捷项与 320px 基准宽），未裸写 `el-date-picker` daterange
- [ ] dialog/drawer 在短视口仍能操作，表单宽度适配容器，长内容/错误提示不被裁切
- [ ] 新增/编辑优先 `modalForm(api(id)).then(getList)`；复杂表单拆 `edit.vue` 放抽屉（`wrapper-closable=false`，`v-if` 惰性渲染）
- [ ] 删除走 `modalSure`（含明确确认文案）；dialog 表单类带 `:close-on-click-modal="false"`
- [ ] 消息只用 `ElMessage`；确认只用 `ElMessageBox` 封装
- [ ] 新插件管理页面优先走 SchemaPage（零前端代码），确认不适合才手写
- [ ] 完整处理加载（v-loading）、空数据（empty-box）、错误、提交中、分页状态，不改全局布局行为

## 十三、样式文件职责与整改记录

### 13.1 样式文件职责（改之前先定位层级）

| 文件 | 职责 | 风险 |
|---|---|---|
| `theme/app.scss` | `--prev-*` 令牌定义、`--el-*` 桥接、布局外壳、flex/间距工具类 | 高（全局） |
| `theme/element.scss` | Element Plus 组件全局微调（按钮/弹窗/菜单/表格） | 高（全局） |
| `theme/variables.scss` | 组件级主题覆盖（按钮/标签/表格/树等状态色），后加载覆盖 element.scss | 高（全局） |
| `theme/dark.scss` | `[data-theme='dark']` 深色模式覆盖 | 中 |
| `theme/media/*.scss` | 响应式断点与各区块降级 | 中 |
| `styles/style.scss` | 遗留通用类（`.acea-row`、`.padding-add`、`.form_content_width`、间距/字号类） | 中 |
| `styles/common.scss` | 滚动条、iView 遗留类 | **勿随意改** |
| 页面 `<style scoped>` | 仅本页面样式，穿透用 `:deep()` | 低 |

### 13.2 巡检整改记录（2026-09-09）

**已修复**（2026-09-23，全站页面标题头补充描述）：

- 全站带标题头页面统一补充 `description` 说明文字：`<pages-header>` 用法直接加 `description` 属性（44 处，含存量模型管理、小程序一键上传共 46 处），直写 `.i-layout-page-header.header-title` 的 16 处改为「`.header-main` 包裹标题 + `.header-description`（前置 `<el-icon>` + `InfoFilled`）」结构，与组件渲染结果一致。仅 setApp 占位页（空壳）与 SchemaPage DetailTemplate（标题由后端 config 下发）未加。
- `.header-main` / `.header-description` / `.header-description-icon` 三类样式真源由 `pagesHeader/index.vue` scoped 迁移至 `styles/style.scss` 全局（组件内定义已删），直写页面直接复用全局类与全局注册的 `InfoFilled` 图标，页面无需自带样式。
- 注意 `header-title` 是 flex space-between，直写时描述必须与标题同包在 `.header-main` 内，否则会被推到容器最右侧。

**已修复**（2026-09-23，表单数字输入框宽度与对齐）：

- 「添加优惠券」数字框（面值/天数/领取数量等）挂了为文本框设计的 `content_width`（414px），且 EP 数字默认居中显示。同类问题全站共 6 文件 17 处（优惠券 ×5、拼团 ×7、砍价 ×3、秒杀 ×1、渠道码 ×1）。
- 宽度：`theme/element.scss` 全局 `.el-input-number { width: 180px }`（EP 默认 150px、small 120px；带单位后缀场景 180px 更协调），摘除上述 17 处 input-number 上的 `content_width`；商品编辑等页面的 `input_width`（160px）、`columnsBox`（200px）、`priceBox`（100%）与 `.form-create .el-input-number`（100%）不受影响。
- 对齐：原 `style.scss` 的 `text-align: unset` 是继承语义（会沿父链取计算值）且与 EP `.el-input-number .el-input__inner{text-align:center}` 同特异性、胜负依赖注入顺序，实际表现为居中；改为绝对值 `text-align: left` 并加 `.el-form .el-input-number .el-input__inner`（0,3,0）双选择器，不依赖加载顺序；顺带删除 form-create 场景的冗余覆盖条（已被 `.el-form` 选择器覆盖）。

**已修复**（2026-09-23，`src/theme/element.scss` 表单行内日期范围选择器宽度）：

- EP 的 range 编辑器（daterange/datetimerange/timerange）根元素复用 `.el-input__wrapper`，自带 `flex-grow: 1`，在 `.el-form-item__content`（flex）内即使写了 `width: 380px` 也会被拉伸占满整行——「添加优惠券」有效期/领取时间即此问题。
- 修复：全局 `.el-form-item__content .el-range-editor { flex-grow: 0 }` 一条规则全站生效。写了宽度的（380/460px、各页 `content_width`）按宽度显示；未写宽度的回落 EP 默认（daterange 350px / datetimerange 640px）；`width: 100%`（DIY 配置面板 2 处）与全局 `<date-range-picker>`（自带 inline `flex-grow: 0`）不受影响。
- 全站摸底：`src` 下 13 处裸写 range 选择器，实际被拉伸 5 处实例（优惠券 create ×2、`setting/systemStore` daterange、`components/systemStore` 营业时间 time-picker、小程序直播 datetimerange），其余因外层包普通 div（宽度由内容决定）侥幸未拉伸。搜索场景仍统一用 `<date-range-picker>`，不因此条规则改变。

**已修复**（2026-09-22，列表默认每页条数全站统一为 15）：

- 全站扫描 `src/pages` 的查询对象初始 `limit`：48 个列表页的 `limit: 20/30/100`（含 reset 函数内硬编码）统一改为 15，`const limit = ref(20/30)` 形态同步；与 SchemaPage ListTemplate、`<pagination>` 组件默认值（15）及默认档位 `[15,20,30,50]` 对齐。
- 刻意不改的非分页 limit：拉全量下拉/选择器的 `limit: 0`（productProtectionListApi、会员卡下拉）、导出取数的 `limit: 200/50`（`excelData.limit`、网盘订单导出）、`app/upload` 的非分页 limit。

**已修复**（2026-09-22，列表分页全站统一为全局 `<pagination>` 组件）：

- 全站扫描 `src/pages`：94 个列表页已用标准写法（表格卡内 `<div class="acea-row row-right page">` + 全局 `<pagination>`），11 个文件直接写原生 `<el-pagination>` 造成样式分裂，已全部替换为标准组件：中台商品、优惠券、卡密商品/订单/库存、网盘商品/订单、实物商品、AI 任务（含弹窗内运行记录两处）、跨版本升级日志。
- 差异点一并抹平：`page-sizes [20,50,100]` → 组件默认 `[15,20,30,50]`；无 background / 极简 layout（`total, prev, pager, next`）→ 默认 background + `total, sizes, prev, pager, next, jumper`；「仅一页时隐藏」（`v-if="total > limit"`）→ 标准 `v-if="total"`；自建容器类（`pagination-box` / `task-pagination` / `pagination-wrap`）→ 标准 `.page` 容器，对应 scoped 样式与 `onPage/onPageSize` 型 handler 已删。
- 扩展 UMD 页（cardkey/virtual/normal/aiagent/center）可用全局组件：扩展运行时挂在宿主 app 内解析全局注册，seckill/combination/distribution 等扩展页已有先例。
- 未动：`diy/` 下两处 `el-pagination`（`mobileConfigRight/c_article.vue`、`editTheme/components/StyleConfig.vue`）是 DIY 可视化编辑器配置面板内嵌选择器，非后台数据列表页。

**已修复**（2026-09-22，列表页动作按钮行全站排查）：

- 按模式 `el-row.mb15/20/25/30/35` + 按钮行、表格 `mt15/20+`、独立按钮卡全量扫描 `src/pages`：7 处按钮行下距超标改为 `mb14`（服务人员、APP 版本、物流公司嵌入表、客服、小程序链接、抽奖列表、事业部列表）；事业部列表原 `el-row.ivu-mt box-wrapper` 上距 36px/下距 0 一并修正。其余列表页已合规（`mb14` 或表格 `mt14`）；搜索条件行与表单项的 `mb20` 不属动作行，未改。
- 动作按钮行上间距统一 14px：新增全局类 `.action-row`（`styles/style.scss`，`-6px` 抵消卡 20px 上内边距），全站 25 处卡内首个动作行/按钮挂类（`mb14` 行 10 处、无类 `el-row` 行 8 处、裸按钮 7 处）；SchemaPage 列表模板动作区内置同款偏移且跟在分类头后自动归零。上方有搜索行/tabs 的动作行（如抽奖列表）不挂类。
- 兜底补扫（按钮文本在标签外的裸按钮形态首次正则漏配）：补挂 27 处（系统配置分类/配置列表、文章分类、优惠券、管理员、核销员、会员等级、数据字典×2、运费模板、商品规格、代码生成、商品保障、用户等级、秒杀商品/活动/配置、商品参数、关键字、砍价/拼团/积分商品、自动回复、会员卡批次、同步物流、抽奖活动等）；同行动作行的多个并排按钮需全部挂类（inline 元素 baseline 对齐，只挂首个负 margin 不生效）；`lotteryList.vue` 表格缺下距补 `mt14`。
- 收尾修正：`router-link` 包裹的动作按钮（文章列表）同样在按钮上挂 `action-row`（router-link 的 a 为 inline 不吃 margin，类挂按钮上）；清理 `ivu-mt mt14` 并存 8 处——`style.scss` 后于 `app.scss` 加载，`.ivu-mt`（16px）会覆盖 `.mt14`（14px），表格下距只保留 `mt14`，卡片上的去掉冗余 `mt14`（卡间距 16px 由 `ivu-mt` 承担）。新增间距类时避免同轴双类并存。
- 根因修正：`theme/app.scss` 布局清零规则 `.el-scrollbar__wrap .el-row:first-child { margin-top: 0 !important }` 本意防页面首位元素顶距叠加，但后代匹配误伤了「数据卡内首个动作行」，`!important` 压过 `.action-row` 的 `-6px` 导致所有 el-row 型动作行上距仍为 20px；已改为 `.el-row:first-child:not(.action-row)` 精准排除。排查此类失效时优先核对布局层 `!important` 清零/覆盖规则。

**已修复**（2026-09-22，`src/components/SchemaPage/templates/ListTemplate.vue`）：

- SchemaPage 列表页顶部动作区统一与表格同卡：删除「无分类头时按钮独立成卡」分支（`schema-list__actions` 独立 el-card），顶部/批量按钮一律渲染进表格卡（有 tabs 时在其下方、无 tabs 时在表格上方），与常规后台页"按钮 → 表格"同卡布局一致；按钮行下距按原型（商品分类页 `mt14`）定为 14px，初版 20px 偏大已收紧（打印机管理页按钮行 `mb20`→`mb14` 同步）；同步更新 crmeb-dev 技能 `references/12-通用页面/schema-page.md` 的分类头描述。

**已修复**（2026-09-22，`src/components/pagesHeader/index.vue`）：

- 页面标题头统一为 52px 紧凑标准：组件内部由 el-card 包裹（body 20px、总高约 67px）改为全局类 `.i-layout-page-header.header-title` + `.ivu-page-header-title`，与「电子发票」等页面的直写结构一致；使用该组件的约 30 个页面（driver/setting/plugin 等）头部高度一次性拉齐。左侧「返回」分隔线行为不变；标题左距由固定 `ml10` 改为仅在带 `backUrl` 时保留（与直写结构 20px 内边距对齐）。已直写 `.i-layout-page-header.header-title` 的存量页面不受影响。

**已修复**（`src/theme/app.scss`）：

- `--prev-color-primary-light-1..9` / `dark-2` 此前只由 `setings.vue` 在 `window.load` 后写行内样式，首屏与纯 CSS 消费方取不到值（按钮 hover/disabled、`.el-table` 表头背景）。已用 `color-mix` 按 `getLightColor` 同算法补齐，行内样式仍可覆盖。
- `--prev-color-success/warning/danger/info` 全项目无定义，`.color-*` 工具类是空规则。已补齐为 EP 默认语义色。

**已修复**（2026-09-22，`src/pages/system/crontab/index.vue`）：

- 定时任务页提示条上下间距超标，两处叠加：提示条与表格间距 26px（表格误用 `ivu-mt` 吃到全局 16px margin，页面 scoped `.ivu-mt { padding-top: 10px }` 又同时命中卡片与表格）；页签下划线到提示条约 35px（`el-tabs__header` EP 默认 margin-bottom 15px 留在 Tabs 卡内，叠加数据卡 body 顶部 20px）。
- 修复：表格改 `mt14`；Tabs 卡顶部留白移入 `body-style`（`10px 20px 0`）并删除 scoped `.ivu-mt` 规则；新增 scoped `:deep(.el-tabs__header) { margin-bottom: 0 }`；数据卡 `body-style` 顶部收窄为 10px。验收值见 [前端规范 4.4「页签与提示区」](../../rules/前端规范.md)。

**已修复**（2026-09-22，`src/pages/system/file/index.vue`）：

- 文件管理页页签与其他页面不统一：横向留白用 `p-x-20` 工具类挂在 `el-tabs` 根节点、页签头部默认 margin-bottom 15px 未去除，且初版 54px 页签高致文字上方留白偏大（用户验收否决 54px 方案）。最终对齐：scoped `:deep(.el-tabs__header) { margin: 0; padding: 0 20px }`，卡顶部不留白（body 顶 padding 0，页签自身垂直居中留白已足够），内容区 `.box` 自补 15px 上边距。验收值见 [前端规范 4.4「页签与内容同卡」](../../rules/前端规范.md)。
- 同步删除订单售后页（`order/afterSale/index.vue`）与物流设置页（`setting/express/index.vue`）局部的 54px 页签高覆盖；54px 不作为验收值（页签高度此后经全局定制统一，见下条）。

**已修复**（2026-09-22，`src/theme/element.scss`）：

- 页签视觉规格全站统一（用户以「小程序配置」参考稿裁定，先后否决 14px 与 54px 两版）：`theme/element.scss` 新增全局 Tabs 定制——页面页签 48px 高 / 16px 字号 / 选中 500 加粗 / 激活条 3px（经 `--el-tabs-header-height` 变量控制高度）；弹窗/抽屉内页签维持 13px / 40px 紧凑规格。`order/afterSale/index.vue` 局部 2px 激活条覆盖一并删除。页签布局（header margin/padding、卡顶留白）仍由各页 scoped 处理，验收值见 [前端规范 4.4「Tabs」](../../rules/前端规范.md)。存量页面的局部字号/高度覆盖如发现，按此行清理。
- 例外档补充（用户反馈接口管理页字偏大后裁定）：工具型工作台页面（左侧树+右侧详情、页签为独立白条的页面，如 `system/backendRouting`）不适用 16px 大字，scoped 覆盖为紧凑档 14px/40px（覆盖 `--el-tabs-header-height` + 字号，并注释原因）；该页原存量 54px `!important` 覆盖一并清除。

**已修复**（2026-09-22，`src/theme/element.scss` 定稿）：

- 16px/48px 方案全站铺开后用户验收仍偏大（「tab 字太大」），最终定稿：页面页签 **14px / 44px 高**，选中 500 加粗、激活条 3px 不变；`backendRouting` 的工具页紧凑档例外随全局字号回落到 14px 而删除（例外不再需要，全站页面页签单档）。最终验收值见 [前端规范 4.4「Tabs」](../../rules/前端规范.md)。裁定历程：16px（参考稿）→ 偏大；54px → 偏大；14px/44px 为定稿，后续调页签只改 `theme/element.scss` 一处。

**已修复**（2026-09-22，`src/components/pagesHeader/index.vue`）：

- 页面标题头说明文字统一前置 info 图标（用户参考 el-alert 蓝色圆底 i 样式）：`description` 描述前渲染 EP 全局图标 `InfoFilled`（14px、`--prev-color-primary`、`vertical-align: -2px`、右距 2px），组件层统一，所有使用 `description` 的页面自动生效；直写 `.i-layout-page-header.header-title` 结构的页面若加说明文字需自带同款图标。验收值见 [前端规范 4.2「页面结构」](../../rules/前端规范.md)。

**已修复**（2026-09-23，`src/theme/element.scss`）：

- 页签高度重新裁定：用户以「实物订单」页现效果为准，页面页签高度 44px → 54px（`--el-tabs-header-height`），字号 14px / 选中 500 / 激活条 3px 不变，[前端规范 4.4「Tabs」](../../rules/前端规范.md) 定稿值同步更新（此前 2026-09-22 的 44px 定稿记录见上，54px 为当前验收值）。同批清理订单中心（`order/orderCenter/index.vue`）、电子发票（`order/invoice/index.vue`）、实物订单列表（`plugin/normal/order/components/OrderTable.vue`）残留的 54px 局部高度覆盖，页签视觉规格继续只由 `theme/element.scss` 一处提供。

**待处理**：

- 成功色不统一：`.el-button--success` 被 `variables.scss` 改成 `#19be6b`，而 `--el-color-success`（标签/文字/进度）仍是 EP 默认 `#67c23a`。需二选一统一。
- `theme/variables.scss` 是 element-ui 时代的覆盖文件，部分规则与 EP 默认重复或已失配，清理前需逐条核对。
- iView 遗留类分散在 `styles/style.scss`、`styles/common.scss`（`.ivu-*`、`.i-table-no-border` 等），在当前 EP 下已失效。
- `theme/media/index.scss` 顶部注释写 `$xs <768px`，实际 `$xs: 576px`。
- `theme/other.scss` 为空文件，可删。

### 13.3 维护约定

设计验收标准只维护在 [前端规范](../../rules/前端规范.md)，本技能维护实施流程、冲突裁定和代码现状。admin 端入口指向这些真源；不另建 UI 规范副本，源码适配后同步更新对应现状说明。
