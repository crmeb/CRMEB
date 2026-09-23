# CRMEB 官方图标库（iconfont 项目 5121724）

本目录专门存放 CRMEB 官方图标字体资源，使用字体类 `crmeb-icon`（如菜单图标 `MenuIcon`、选择图标组件 `SelectIcon`）。同级的 `iconfont.*`（项目 1945396，DIY 编辑器）与 `mobile_iconfont.*` / `mobile.json`（项目 993865，移动端 DIY）不属于官方图标库，请勿混入本目录。

## 文件说明

| 文件 | 说明 |
| --- | --- |
| `crmeb_iconfont.css` | `@font-face` 声明 + 图标类名规则（font-family 与基础类名固定为 `crmeb-icon`） |
| `crmeb_iconfont.woff2` / `.woff` / `.ttf` | 字体文件 |
| `crmeb_iconfont.json` | 官方下载包自带的元数据，`icons.js` 由此生成图标清单 |
| `icons.js` | 图标清单（按类名序号从小到大排序）+ `resolveIconfontIcon` 存量值解析，组件统一从这里取数 |

## 官方图标更新步骤

1. 在 iconfont.cn 项目 5121724 页面「下载至本地」，得到 `iconfont.css`、`iconfont.woff2`、`iconfont.woff`、`iconfont.ttf`、`iconfont.json`。
2. 将上述文件重命名为 `crmeb_iconfont.*`，覆盖本目录同名文件。
3. 调整 `crmeb_iconfont.css` 两处（官方原文件用的是 `iconfont`，与 DIY 依赖的项目 1945396 冲突）：
   - `@font-face` 的 `font-family` 与基础类 `.iconfont` 改为 `crmeb-icon`；
   - `src` 的字体 url 改为本目录文件名（`./crmeb_iconfont.woff2` 等）。
4. 图标类名规则（`.CRMEB-xxx-*:before`）原样保留，无需逐条修改。
5. JS 代码零改动：`icons.js` 自动从新 `crmeb_iconfont.json` 生成排序后的图标清单；`src/styles/index.scss` 已引入本目录 css，亦无需变动。

> 注意：若官方更新调整了类名（如去掉 `a-` 前缀），已存储的旧类名由 `icons.js` 的 `resolveIconfontIcon` 做兼容解析；仅当无法唯一匹配时才会失效。
