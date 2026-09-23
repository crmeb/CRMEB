# DIY 可视化装修 / 主题模块

参照 v6.0.0 线 `src/diy` 模块化迁移（be758be19 → 44d50d34b 四连提交）在 v8 上建立的同构模块目录。
`@diy` 别名指向本目录，迁移已全部完成，结构与 v6.0.0（a19cd5c6e/44d50d34b）对齐。

## 目录结构

```
src/diy/
├── README.md                     本文件
├── api/index.js                  全部 DIY/theme 接口（自 @/api/diy 原样迁入）
├── mixins/theme.js               主题换色 mixin（getColorChange + colorStyle 色卡）
├── assets/images/                编辑器专用图 39 张（foot-00x 底部导航、sort0x 分类样式、
│                                 shan/noPictrue 占位图、transparents 网格底纹等）
├── components/                   DIY 组件全集
│   ├── mobilePage/               编辑器中间手机预览组件 + index.js 聚合器
│   ├── mobileConfig/             编辑器右侧配置面板（c_*.vue）+ index.js 聚合器
│   ├── mobileConfigRight/        可复用原子控件 + index.js 聚合器
│   ├── CustomDesign/ hotpotModal/ pagesFoot/ rightBtn/
│   └── legacy/                   旧版控件：diyComponents/ rightConfig/ sortList/
├── store/
│   ├── mobildConfig.js           DIY 编辑器主 store（defaultArray/UPDATEARR 等）
│   └── legacy/                   旧版模板 store：fresh/ goodSelect/ moren/ shopping
│                                 （其中 fresh/goodSelect/shopping 在 v8 已无使用方）
└── pages/
    ├── theme/                    主题系统页面（editTheme 主题编辑、myTheme 我的主题、
    │                             mallTheme 商城主题、micro_page 微页面、components/themeSelect）
    ├── devise/                   DIY 编辑器页面（diyIndex 编辑器主页、list 列表页、
    │                             links 页面链接、goodClass/users 由 list 相对引入；
    │                             template 为无引用遗留页）
    └── legacy/                   旧版页面：devisePage/（iframe DIY）、themeStyle/（一键换色）
```

## 路由与菜单

路由注册在 `src/router/modules/setting.js`，path/name/meta 与迁移前一致（后端菜单按 path 鉴权），
组件 import 使用 `@diy` 别名：

| 路由 path                        | 组件                                   |
| -------------------------------- | -------------------------------------- |
| `theme_style`                    | `@diy/pages/legacy/themeStyle/index`   |
| `theme/micro_page`               | `@diy/pages/theme/micro_page/index`    |
| `my_theme`                       | `@diy/pages/theme/myTheme/index`       |
| `mall_theme`                     | `@diy/pages/theme/mallTheme/index`     |
| `edit_theme`                     | `@diy/pages/theme/editTheme/index`     |
| `pages/devise/:type` 等 3 条     | `@diy/pages/devise/list`               |
| `pages/diy_index`                | `@diy/pages/devise/diyIndex`           |
| `pages/links`                    | `@diy/pages/devise/links`              |
| `pages/diy`（旧版 iframe DIY）   | `@diy/pages/legacy/devisePage/index`   |

## 对外依赖与反向引用

- 共享组件（仍在 `src/components/`）：`linkaddress`、`goodsList` 等反向依赖 `@diy/api`（与 v6.0.0 一致）。
- 换色 mixin 使用方：`@diy/components/mobilePage/*`、`@diy/components/pagesFoot`、`@diy/pages/devise/diyIndex.vue`。
- `@/api/diy` / `@api/diy` 引用已全部切换为 `@diy/api`（含 codeGeneration 等业务页面）。
- 后端接口前缀：`diy/*`、`diy_pro/*`、`theme/*`（对应后端 `eb_diy`/`eb_theme` 表）。
- 注意：`src/theme/` 是后台界面自身 SCSS 样式目录，与本模块无关。
