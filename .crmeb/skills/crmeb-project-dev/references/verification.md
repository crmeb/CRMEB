# 验证与运行环境

## 运行环境选择

默认使用宿主机已配置的 PHP、MySQL 和 Redis。运行命令、直接数据库查询和 API 请求都应先以 `backend/.env`、前端环境文件和实际监听地址为准，不假定 Docker 容器名或映射端口。

后端命令从 `backend/` 运行：

```bash
php -l path/to/changed.php
php think list
php think run --host 127.0.0.1 --port 8011
php think queue:listen --queue
php think timer start --d
php think workerman start --d
```

任务不需要时，不要启动 HTTP、queue、timer 或 Workerman 常驻进程。

只有用户明确指定 Docker、目标实例已在容器中运行，或宿主机 PHP 版本/扩展不满足任务时，才使用以下可选命令：

```bash
./run.sh ps
./run.sh start
./run.sh logs
```

Docker 被选中时，若宿主机 PHP 与容器环境不一致，在 `crmeb_php` 中执行等价命令并说明实际测试环境。`./run.sh install` 和 `./run.sh delete` 会清理运行时及数据库数据，不得用于常规验证，未明确获得数据清除授权时绝不能运行。

## 按风险选择检查范围

### 纯后端改动

1. 对每个变更的 PHP 文件运行 `php -l`。
2. 确认每条变更路由都指向存在的控制器和 camelCase 方法。
3. 使用真实中间件和认证上下文请求受影响接口。
4. 持久化改动需要验证空数据、正常、非法、重复和幂等场景。
5. 可选监听器需要验证模块启用和禁用时的行为。

当前仓库没有全局 PHPUnit 配置。除非任务新增或发现了测试套件，否则不得声称运行过单元测试。

### Admin 改动

从 `frontend/admin/` 运行：

```bash
npm run build
npx eslint path/to/changed-file.vue path/to/changed-file.js
```

package 中的 `eslint` 脚本会自动修复整个 `src`。工作区存在无关用户改动时，优先执行定向 lint。Node 版本使用 `>=18`（18/20 验证可用）。

### Admin 扩展

```bash
npm run build:ext -- --ext <key>
```

检查 `dist/static/extensions/<key>/app.umd.js` 和 `app.umd.css`。构建脚本会主动删除其他输出文件。

### Uni-app 改动

用 uni-app CLI 构建：`npm run dev:h5` / `build:h5` / `dev:mp-weixin` / `build:mp-weixin` 等。验证 H5 以及所有明确指定的小程序/App 平台，包括条件编译分支。产物在 `frontend/uni-app/dist/{dev,build}/<platform>`。

## 端到端访问地址

- 宿主机后端默认：`http://localhost:8011`（若实际监听地址不同，以运行配置为准）
- Admin：`http://localhost:8011/admin`
- 用户 H5：`http://localhost:8011/`
- Admin 开发服务器：Vite dev server 使用端口 `1619`（占用时顺延），并把 `/adminapi` 代理到 `http://localhost:8011`

直接访问数据库时，从 `backend/.env` 读取主机、端口、库名和用户；宿主机本地 MySQL 常用 `127.0.0.1:3306`。只有已选择根 Compose 环境时才使用映射端口 `33061`。

验证 API/UI 时，检查真实响应包（`status`、`msg`、`data`）、认证过期、权限、加载/错误/空数据状态，以及浏览器控制台和网络错误。

## 插件和表结构改动

在隔离数据库中测试安装、卸载和迁移，并验证：

- 首次安装和重复安装
- 启用、禁用和重新启用
- 从声明的上一版本升级
- 卸载清理仅影响自有资源
- 卸载后重新安装
- 全新安装与升级后的表结构一致

不得在不可恢复的本地或生产数据上测试破坏性生命周期操作。
