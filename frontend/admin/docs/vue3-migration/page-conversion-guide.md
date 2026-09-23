# Vue3 迁移：单页/组件转换规范（所有批次必须遵守）

本仓库正在从 Vue2.7 迁移到 Vue3 + `<script setup>`。codemod 已批量处理过模板语法（slot/.sync/.native/deep/$set/require 资产/生命周期名），你负责把指定范围的 `.vue` 文件转为 Vue3 script setup 并修复残留问题。**禁止 TypeScript。保留文件顶部 CRMEB 版权注释。**

先读这三份资料再动手：

1. `/Users/from/.zcode/skills/vue2-to-vue3-migration/references/migration-patterns.md`（API 映射表）
2. `docs/vue3-migration/store-mapping.md`（Vuex→Pinia 映射，本项目 store API 名单）
3. 本文件

## 转换规则

### script 结构

- `<script>` → `<script setup>`；`name` → `defineOptions({ name: 'Xxx' })`（keep-alive 依赖组件名，**必须保留原 name**）。
- `data` → `ref()/reactive()`；`methods` → 普通 function；`computed` → `computed()`；`watch` → `watch()`（监听 props 必须用 getter：`watch(() => props.x, ...)`）。
- 生命周期：`created` → 顶层代码；`mounted` → `onMounted`；`beforeUnmount/unmounted`（codemod 已改名）→ `onBeforeUnmount/onUnmounted`；`activated/deactivated` → `onActivated/onDeactivated`。
- `this.$refs.x` → `const xRef = ref(null)` + 模板 `ref="xRef"`（**模板 ref 名必须与变量名一致**，原有模板 `ref="x"` 时把变量命名为 `x` 对应 `const x = ref(null)` 最省事——但注意与 data 变量重名冲突）。
- `this.$emit` → `defineEmits`；`this.$nextTick` → `nextTick`；`this.$set/$delete` 已被 codemod 处理（残留多为注释）。
- `this.$route`/`this.$router` → `useRoute()`/`useRouter()`。
- `props` → `defineProps`；`components: {...}` 局部注册删除（直接 import 即可用）；异步组件 `() => import('...')` → `defineAsyncComponent`。
- 组件内 `filters: {...}` 选项：转为普通 function（codemod 已把模板改成 `fname(expr)` 直接调用）。
- `mixins: [...]`：内联展开为组合逻辑；`provide/inject`（选项式）→ `provide()`/`inject()`。
- `<script setup>` 顶层 import 的组件名必须与模板标签一致（如模板用 `<draggable>` 就必须 `import draggable from 'vuedraggable'`）。
- watch `$route` → `watch(() => route.path, ...)`。

### store（Vuex → Pinia）

- `this.$store.state.xxx.yyy` → `const xxxStore = useXxxStore()` 后 `xxxStore.yyy`（解构用 `storeToRefs`）。
- `this.$store.commit('menu/setActivePath', p)` → `useMenuStore().setActivePath(p)`；`dispatch` 同理。
- `mapState/mapMutations/mapActions` 删除，改直接调用；函数名以 `docs/vue3-migration/store-mapping.md` 为准（注意 userInfo store 的 setter 都加了 `set` 前缀、menus 的 `setChildMenuList`）。
- 非命名空间模块（user/app）的历史裸调用 `commit('setUserId')` → `useUserStore().setUserId()`。

### 事件总线

- `this.bus.$on('e', fn)` → `import bus from '@/utils/bus'` + `bus.on('e', fn)`；`$emit` → `bus.emit`；`$off` → `bus.off`（**必须具名函数**，不能内联箭头）。
- wangEditor 目录里的 `import bus from '@/utils/bus'`（原来是 new Vue() 实例）调用点 `$on/$emit` → `on/emit`。

### 全局属性替代（script setup 无 this）

| 旧写法 | 新写法 |
|---|---|
| `this.$message.success(...)` | `import { ElMessage } from 'element-plus'` + `ElMessage.success(...)` |
| `this.$confirm/$alert/$prompt/$msgbox/$notify` | `ElMessageBox.confirm/alert/prompt` / `ElMessageBox(...)` / `ElNotification`（from 'element-plus'） |
| `this.$modalForm(...)` | `import modalForm from '@/utils/modalForm'` 直接调用 |
| `this.$modalSure(...)` | `import { modalSure } from '@/utils/public'` |
| `this.$HandlePrice` / `$exportExcel` / `$videoCloud` / `$authLapse` | `@/utils/public` / `@/utils/newToExcel.js` / `@/utils/videoCloud` / `@/utils/authLapse` 直接 import |
| `this.$wechat` | `import Auth from '@/libs/wechat'` |
| `this.$dialog.error(...)` | `import dialog from '@/libs/dialog'` |
| `this.$moment(...)` | `import moment from 'moment'` |
| `this.$timeOptions` / `$scroll` | `@/libs/timeOptions` / `@/libs/loading` |
| `this.$tools` | `import * as tools from '@/libs/tools'` |
| `this.$routeProStr` | `import Setting from '@/setting'` 后 `Setting.routePre` |
| `this.$config` | `import config from '@/config'` |
| `this.$validator(rule)` | `import schema from 'async-validator'` + `new schema(rule)` |
| `this.$imgModal(...)` / `this.$videoModal(...)` | `import { getCurrentInstance } from 'vue'` 后 `const { proxy } = getCurrentInstance()` + `proxy.$imgModal(...)`（这两个保持 proxy 调用） |
| `this.$copyText(text)` | 同上走 proxy，或复制逻辑内联 |

### Element Plus 差异（codemod 未覆盖、需要你手工处理）

- `<el-dialog>` 插槽 `#title` → `#header`；`el-drawer` 的 `#title` 不变。
- `el-submenu` 已改 `el-sub-menu`（其 `#title` 插槽保留）。
- size：`medium` → `default`、`mini` → `small`（属性值是字符串的场合）。
- `el-button icon="el-icon-xxx"` 字符串类名**保留不动**（已有 CSS mask 兼容层）；`<i class="el-icon-xxx">` 同样保留。
- `el-select` 等 `:visible.sync` 已被 codemod 处理；若发现组件自定义 `visible` prop 事件为 `update:visible` 逻辑保持。
- `el-table` 作用域插槽已是 `#default="scope"`。
- `el-tooltip`/`el-popover` 的 `slot="content"` 已被 codemod 包成 `<template #content>`。
- `el-form` 的 `:model` 校验、`ref` 调用 `validate` 用法不变；`resetFields` 不变。
- 若遇到 `el-autocomplete` `popper-class` 等样式类在 theme 里未对齐，先不改样式。

### 其他

- 模板里残留的 `require('@/assets/...')`（codemod 已报告，见 scripts/codemods/report.json 的 tpl-require）：改为顶部 `import` + 标识符。
- `v-for` 与 `v-if` 同标签（report.json 有清单）：拆成 `<template v-for>` 内套 `v-if`（注意 Vue3 语义：v-if 优先级更高，先确认原逻辑等价于"对每项判断"还是"整体判断"）。
- `<template v-for>` 的 `:key` 必须放在 `<template>` 上（Vue3 强制）。
- `v-model` 不能绑 v-for 循环变量本身，用 `v-model="array[index].field"`。
- 图片懒加载 `v-lazy="url"` **保留**（已有兼容指令，只支持 img src 场景）。
- `v-viewer`、`v-db-click`、`v-auth`、`v-permission`、`v-clipboard`、`v-draggable` 保留。
- 遇到 `swiper/swiper-slide`、`vuedraggable`、`vue-codemirror`、`vxe-table` 等第三方组件用法：本批只保证语法/编译通过，把 import 改为可解析（swiper11: `import { Swiper, SwiperSlide } from 'swiper/vue'` + `import 'swiper/css'`；vuedraggable4: `import draggable from 'vuedraggable'`，模板需加 `:item-key` 并把循环项包进 `#item="{ element, index }"` 插槽——若工作量大可先保留旧模板结构并在文件顶部加 `// TODO(vue3): draggable API 迁移` 注释，保证编译通过即可）。
- scss 里的 `::v-deep` 已处理；`/deep/` 若残留改 `:deep()`。

## 验证方式（必须执行）

dev server 已在 http://localhost:1619/admin 常驻运行。逐个请求你改过的文件（编译通过=200，失败=500）：

```bash
curl -s -o /dev/null -w '%{http_code} src/xxx.vue\n' 'http://localhost:1619/admin/src/xxx.vue'
```

- 批量脚本示例：`for f in $(git diff --name-only -- 'src/components' | sed 's|^src/|src/|'); do curl -s -o /dev/null -w "%{http_code} $f\n" "http://localhost:1619/admin/$f"; done | grep -v '^200'`
- 非 200 的必须修到 200（500 时 curl 响应体里有 vite 的报错详情，`curl -s 'http://...' | head -30` 查看）。
- 额外用 `npx prettier --check <files>` 确认无语法错误。
- **不要**自己启动/杀掉 dev server；**不要**跑 npm install。

## 提交

完成后：`git add <你负责的目录们> && git commit -m "<给定信息>"`。若 git 报 index.lock 冲突，sleep 5 重试。不要 add 别的目录，不要 push。
