## 生产就绪（Ready）评估与最小补强（面向长期稳定运行）

适用范围：本仓库 `crmeb`（ThinkPHP6）后端 + 额外进程（`queue`/`timer`/`workerman`）+ `docker-compose/` 示例。

### 0. 默认上线决策（你已确认执行）

1. 部署形态：**单机 Nginx + PHP-FPM**（不在生产使用本仓库的 `docker-compose/`，仅本地/测试用）
2. 依赖：MySQL/Redis 默认使用**托管服务**，并且**不开放公网直连**
3. 扩容：默认不做水平扩容；需要扩容时再迁移 Session/Cache 到 Redis
4. 进程托管：默认使用 **Supervisor** 托管 `queue/timer/workerman`
5. 跨域：默认不需要跨域（同域）；如确需跨域，必须配置白名单（见下）

### 1. “现在不修，上线一定会出事”的高优先级问题（已做最小补强）

1. **MySQL 容器配置曾包含 `skip-grant-tables`（等于关闭鉴权）**
   - 已修复：`docker-compose/mysql/my.cnf` 移除该项，避免误用导致数据库裸奔。
2. **Nginx 未禁止 `/uploads` 下 PHP 执行（上传 WebShell 风险极高）**
   - 已修复：`docker-compose/nginx/vhost.conf` 增加 uploads/static 等目录禁止执行 PHP，并在 `location ~ \\.php$` 增加 `try_files` 防止任意路径转发到 PHP。
3. **“自定义定时任务”使用 `eval($customCode)`（RCE 级别能力）**
   - 已补强：默认禁用，需显式开关才允许执行。见：`crmeb/app/services/system/crontab/CrontabRunServices.php`。
   - 开启方式（仅建议在隔离环境/严格审计下使用）：`.env` 增加 `[SECURITY] ALLOW_CUSTOM_CRONTAB_CODE=true`。
4. **数据库调试默认开启（`database.debug` 默认 true）**
   - 已修复：默认跟随 `APP_DEBUG`（默认 false），避免生产泄漏 SQL/性能恶化。见：`crmeb/config/database.php`。
5. **docker-compose 默认弱口令/端口暴露**
   - 已补强：`docker-compose/docker-compose.yml` 将 MySQL/Redis 端口绑定到 `127.0.0.1`（仅本机可访问），并修正 MySQL 官方镜像变量名 `MYSQL_PASSWORD`。

### 2. 生产环境需要你最终落地的最小检查清单

**配置与安全**
- `APP_DEBUG=false`（生产必须）
- MySQL/Redis：仅内网访问（安全组/ACL），禁公网直连
- 后台 `/admin`：至少加一层防护（IP 白名单 / VPN / 二次验证）
- 跨域白名单（二选一）：
  - `COOKIE.DOMAIN`（同主域/子域放行）
  - `CORS.ALLOWED_ORIGINS`（精确 Origin 列表）
  - 说明：当 `APP_DEBUG=true` 且未配置白名单时，默认放行（便于本地开发）；生产请务必显式配置白名单

**稳定性与进程**
- Supervisor 托管：`queue:listen`、`timer start`、`workerman start`（见 `ops/supervisor/`）
- 日志：集中采集 `runtime/log`、Supervisor stdout/stderr

**备份与恢复**
- MySQL：全量 + binlog/PITR（托管优先），并做恢复演练（脚本见 `ops/backup/`）
- 文件：`public/uploads` 持久化与备份（对象存储优先）

**健康检查与回滚**
- `GET /healthz`；`/healthz?deep=1`（失败 503，避免对公网暴露）
- 发布回滚：保留上一个 release（见 `ops/deploy/`）
- 自检脚本：`ops/checks/ready_check.sh`（上线前快速扫一遍关键风险项；需要先复制 `crmeb/.env.example` 为 `crmeb/.env`）
