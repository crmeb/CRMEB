# 安装部署与环境运维（Install & Deploy）

> 本文是 `crmeb-dev` 技能的 Docker 专题子文档，仅在用户明确选择 Docker 或目标实例已运行在容器中时使用。完整二开流程导航见上级 [../../SKILL.md](../../SKILL.md)；默认宿主机运行与验证见 [../../../crmeb-project-dev/references/verification.md](../../../crmeb-project-dev/references/verification.md)。

## 一、环境架构（Docker）

本项目通过**仓库根目录**的 `docker-compose.yml` 管理 7 个服务，宿主机访问地址 **http://localhost:8011**：

| 容器名 | 镜像 | 宿主机端口 → 容器端口 | 说明 |
|--------|------|----------------------|------|
| `crmeb_nginx` | nginx:v1.29.6 | `8011` → 80 | Web 入口，vhost 配置 `docker/nginx/vhost.conf`（已入库） |
| `crmeb_php` | crmeb_php:8.2 | `9000` | PHP-FPM，代码挂载 `backend/` → `/var/www`；镜像由 `docker/php/Dockerfile` 构建（已入库） |
| `crmeb_timer` | crmeb_php:8.2 | — | 定时任务（`php think timer start`） |
| `crmeb_queue` | crmeb_php:8.2 | — | Redis 队列消费（`docker/queue-worker.sh`，只读挂载为容器内 `/repo/docker/queue-worker.sh`） |
| `crmeb_workerman` | crmeb_php:8.2 | `40001` | WebSocket 网关与 Channel 服务 |
| `crmeb_mysql` | mysql:v8.0.45 | `33061` → 3306 | MySQL 8.0，数据目录 `docker/mysql/data/` |
| `crmeb_redis` | redis:v8.6.1 | `63791` → 6379 | Redis，密码 `123456`（本地默认） |

> PHP 支持范围 **8.2-8.4**，四个 PHP 容器共用同一镜像，镜像标签默认取支持范围下限 `crmeb_php:8.2`。验证其它版本：`PHP_VERSION=8.3 docker compose up -d --build`（8.4 同理），构建参数见 `docker/php/Dockerfile`。

**关键经验**：容器主机名（`crmeb_mysql`、`crmeb_redis`）**仅在 Docker 网络内可解析**（PHP 应用内连接数据库用它们），宿主机工具（Navicat、redis-cli 等）必须用 `localhost + 映射端口`（`33061`/`63791`）。

## 二、一键安装与启停（run.sh）

根目录 `run.sh` 封装 docker-compose 常用操作：

```bash
./run.sh install    # 清理所有数据并重新安装启动（交互确认，危险）
./run.sh start      # 启动容器
./run.sh restart    # 重启容器
./run.sh stop       # 停止并删除容器
./run.sh delete     # 停止并清理所有数据（危险）
./run.sh logs       # 查看日志
./run.sh ps         # 查看容器状态
./run.sh admin:dev    # 管理端前端开发测试启动（Vite 1619，/adminapi 代理 8011）
./run.sh admin:build  # 管理端 build:all（主包+扩展 UMD）并发布到 backend/public/admin
./run.sh uni:dev      # uniapp H5 开发测试启动（默认端口 5173）
./run.sh uni:build    # uniapp H5 构建并发布到 backend/public（index.html、assets、pages、static）
./run.sh            # 交互式菜单（含 11/12 管理端、21/22 uniapp 前端命令）
```

`install` 会删除 `backend/public/install.lock`、清空 `backend/runtime`、`docker/mysql/data` 等数据目录，完成后访问 **http://localhost:8011** 进入安装向导。

## 三、手动安装（无 Docker 环境）

1. 创建数据库（utf8mb4），依次导入 `backend/public/install/crmeb_table.sql`（表结构）与 `crmeb_data.sql`（基础数据）
2. 复制 `backend/.env.example` 为 `backend/.env`，按实际环境修改数据库/Redis 连接（模板内有逐项注释）
3. 设置目录权限：`chmod -R 777 backend/runtime backend/public`
4. 访问 `http://域名/admin`，默认账号 `admin` / 密码 `crmeb.com`

`.env` 关键配置（`backend/.env`）：

```ini
APP_DEBUG = true
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1        # 容器内用 crmeb_mysql
HOSTPORT = 3306             # 宿主机用 33061
DATABASE = crmeb
USERNAME = root             # 容器内用 crmeb
PASSWORD = 123456
PREFIX = eb_
CHARSET = utf8mb4

[REDIS]
REDIS_HOSTNAME = 127.0.0.1  # 容器内用 crmeb_redis
PORT = 6379                 # 宿主机用 63791
REDIS_PASSWORD = 123456
SELECT = 0
```

`backend/public/install.lock` 存在时跳过安装向导（删除后重新进入安装页）。

> 全新安装由安装向导导入 `backend/public/install/crmeb_table.sql` 和 `backend/public/install/crmeb_data.sql`，再逐项执行必需模块、默认模块和默认驱动 Installer；不需要手动补跑历史升级 SQL。

## 四、数据库 / Redis 连接信息速查

| 位置 | MySQL | Redis |
|------|-------|-------|
| 容器内（PHP 应用） | `crmeb_mysql:3306`，user `crmeb`，pwd `123456`，db `crmeb` | `crmeb_redis:6379`，pwd `123456` |
| 宿主机（工具/MCP） | `localhost:33061`，user `root`，pwd `123456`，db `crmeb` | `localhost:63791`，pwd `123456` |

## 五、首版数据库变更规范（二开必备）

- 核心表结构直接同步到 `backend/public/install/crmeb_table.sql`，核心初始数据同步到 `backend/public/install/crmeb_data.sql`；模块自有结构和数据由对应 `Installer.php` 管理
- 当前首版不新增版本升级脚本；未来明确进入发布后维护阶段时，再建立增量升级链
- SQL 文件必须保存为 **UTF-8 无 BOM**；导入时连接必须指定 `utf8mb4`，否则中文注释（表/列注释）会存成乱码 `?`
- 手动验证 SQL 时的导入命令：

```bash
docker exec -i crmeb_mysql mysql -u root -p123456 --default-character-set=utf8mb4 crmeb < xxx.sql
```

- 已乱码注释修复：`ALTER TABLE xxx MODIFY COLUMN col TYPE COMMENT '正确注释'`（同样用 utf8mb4 连接）

## 六、后台常驻服务（队列/定时任务/WebSocket）

```bash
cd backend
php think queue:listen --queue    # 消息队列
php think timer start --d         # 定时任务（自动收货、库存预警等），stop/restart
php think workerman start --d     # 统一长连接网关 + 内部通讯（管理员通知、客服聊天等全部长链接业务），stop/restart
```

容器内执行需先进入容器：`docker exec -it crmeb_php php think <command>`

### 6.1 统一长连接网关（重要）

系统所有 WebSocket 长链接业务（admin 后台通知、kefu_chat 客服聊天、后续插件长链接）**共用唯一对外端口** `40001`：

- 服务端配置 `backend/config/workerman.php` 的 `gateway` 节点；客户端以 URL 参数 `?service=xxx` 区分业务，如 `ws://域名/ws?service=kefu_chat`
- 前端连接地址统一由接口 `/get_workerman_url` 下发（`backend/app/common.php` 的 `getWorkerManUrl()`）
- 业务处理器实现 `crmeb\services\workerman\WsServiceHandlerInterface`，通过监听 `WebSocketServiceRegister` 事件注册；插件卸载/禁用后对应 service 自动摘除
- **Nginx 反代只需配置一条且永久有效**（WSS/TLS 由 Nginx 统一终结）：

```nginx
location /ws {
    proxy_pass http://crmeb_php:40001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_read_timeout 3600s;
}
```

> 新增长链接业务（如直播弹幕、即时通讯）无需再改端口映射与本反代配置。

## 七、前端服务

| 前端 | 技术栈 | 说明 |
|------|--------|------|
| `frontend/admin` | Vue 3 + Element Plus + Vite | 管理后台，构建产物部署到 `/admin`；二开用 `scripts/build-extensions.js` 构建插件 UMD 扩展 |
| `frontend/uni-app` | uni-app CLI（Vue 3） | 用户端（H5/小程序/APP），用 `npm run dev:h5` 等 CLI 命令运行 |

前端也可用仓库根 `run.sh` 一键执行（自动补装 `node_modules`，等价于进入对应目录执行 npm 命令）：

| 菜单 | 命令 | 作用 | 产物 / 地址 |
|------|------|------|------------|
| 11 | `./run.sh admin:dev` | 管理端开发测试启动 | http://localhost:1619 |
| 12 | `./run.sh admin:build` | `npm run build:all`（主包 + 扩展 UMD）后发布 | `backend/public/admin/` |
| 21 | `./run.sh uni:dev` | uniapp H5 开发测试启动 | http://localhost:5173 |
| 22 | `./run.sh uni:build` | `npm run build:h5` 后发布 | `backend/public/`（index.html + assets/pages/static，PHP 侧文件不受影响） |

**注意**：前端 dev server 热更新可能失效，修改文件后页面若未更新，需手动重启服务。

## 八、登录账号

- 后台：`admin` / `crmeb.com`（http://localhost:8011/admin）
- 用户端：`13679281569` / `12345678`

## 九、常见问题

| 现象 | 解决 |
|------|------|
| MySQL 容器启动失败（`data directory has files in it`） | `docker-compose down` 后清空 `docker/mysql/data/*` 再 `up -d` |
| 数据库中文注释显示 `?` | SQL 导入未加 `--default-character-set=utf8mb4`，按第五节修复 |
| 安装向导反复出现 / 改完配置不生效 | 删除 `backend/public/install.lock`；清理 `backend/runtime` 缓存 |
| 后台接口 500 | 检查 `backend/runtime`、`backend/public` 权限（777）及 `.env` 数据库连接 |
| 宿主机无法连接数据库 | 用映射端口 `33061`，不要用容器名 `crmeb_mysql` |
