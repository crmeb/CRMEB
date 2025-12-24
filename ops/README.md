## 运维与上线（最小可用）

本目录提供“可直接复制改名”的模板，避免生产环境临时手写导致遗漏。

### 进程托管

- Supervisor 模板：`ops/supervisor/`
  - `crmeb-queue.conf`：队列监听
  - `crmeb-timer.conf`：定时任务
  - `crmeb-workerman.conf`：长连接服务

按你的实际路径/用户/日志目录修改后，放到 supervisor 的 conf 目录并 `supervisorctl reread && supervisorctl update`。

### Nginx（生产示例）

- `ops/nginx/crmeb.conf.example`：包含 uploads 禁止 PHP、基础安全头、ThinkPHP 兼容 rewrite

### 发布与回滚（最小方案）

- `ops/deploy/README.md`
- `ops/deploy/rollout.sh`
- `ops/deploy/rollback.sh`

### 备份与恢复

- MySQL 备份脚本：`ops/backup/mysql_backup.sh`
- MySQL 恢复脚本：`ops/backup/mysql_restore.sh`

### 生产前自检（最小）

- `ops/checks/ready_check.sh`：检查 `crmeb/.env` 的关键生产安全项（如 `APP_DEBUG`、自定义定时任务开关、CORS 白名单）
  - 首次使用：复制 `crmeb/.env.example` 为 `crmeb/.env` 并按实际环境修改（仓库内的 `crmeb/.env` 仅为占位）
  - 自检脚本不会修改任何配置，仅输出 WARN/FAIL 提示
- `ops/checks/ready_check_test.sh`：ready-check 的最小自测用例（用于回归脚本解析逻辑）
