# docker 目录说明

本目录只存放**仓库根 Docker 编排引用的构建配置、运行时脚本与运行时数据目录**。

Docker 编排入口在**仓库根目录**：

```bash
# 在仓库根目录执行（不要在本目录内找 compose 文件）
./run.sh install   # 首次安装（清空数据并启动）
./run.sh start     # 日常启动
```

编排文件：根 `docker-compose.yml`（mysql / redis / phpfpm / timer / queue / workerman / nginx 七服务）。

## 本目录内容

| 路径 | 用途 | 是否入库 |
|---|---|---|
| `php/Dockerfile` | phpfpm / timer / queue / workerman 共用的 PHP 镜像构建文件（默认 8.2，支持范围 8.2-8.4，`PHP_VERSION` 构建参数可切 8.3 / 8.4） | 是（根 compose `build.context` 引用） |
| `nginx/vhost.conf` | Nginx 站点配置（挂载进 nginx 容器） | 是 |
| `queue-worker.sh` | 队列消费者包装脚本：前台启动 `queue:listen`、`SIGTERM` 优雅停机，并每 30 秒写 `runtime/.queue_heartbeat` 心跳。由根 compose 的 `queue` 服务**单文件只读**挂载到容器内 `/repo/docker/queue-worker.sh` 执行（只挂脚本本身，避免连带挂载 `mysql/data` 数据卷） | 是（根 compose `queue` 服务引用） |
| `mysql/data`、`mysql/log` | MySQL 数据与日志卷 | 否（运行时生成，已 gitignore） |
| `nginx/log` | Nginx 日志卷 | 否（运行时生成，已 gitignore） |

## 端口与账号速查

| 服务 | 地址 |
|---|---|
| Web（安装向导 / API） | http://localhost:8011 |
| MySQL | 127.0.0.1:33061（`crmeb` / `123456`，本地默认） |
| Redis | 127.0.0.1:63791（密码 `123456`，本地默认） |
| WebSocket | ws://localhost:40001 |

以上均为**本地开发默认值**，生产部署须修改，见 [部署指南](../help/docs/部署指南.md) 与 [安全与配置](../help/docs/安全与配置.md)。
