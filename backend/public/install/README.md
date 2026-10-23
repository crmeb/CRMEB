# CRMEB v8 安装目录说明

本目录是 CRMEB v8 系统的 Web 安装引导程序，通过浏览器访问 `install/index.php` 即可启动安装向导。安装向导是独立于 ThinkPHP 框架的纯 PHP 程序（主控逻辑全部在 `index.php`），按步骤 + 轮询方式分批执行安装任务，避免超时。

## 目录结构

```
install/
├── css/                          # 安装向导样式文件
├── images/install/               # 安装向导图片资源
├── js/                           # 前端依赖脚本
├── templates/                    # 安装步骤 PHP 模板
│   ├── header.php                # 公共头部（HTML head、导航）
│   ├── footer.php                # 公共底部（版权信息）
│   ├── step1.php                 # 步骤1 - 安装许可协议
│   ├── step2.php                 # 步骤2 - 运行环境检测
│   ├── step3.php                 # 步骤3 - 数据库及管理员配置
│   ├── step4.php                 # 步骤4 - 安装执行过程（前端轮询驱动）
│   └── step5.php                 # 步骤5 - 安装完成
│
├── data/                         # 按表拆分的核心初始数据 SQL
│   ├── system_config_data.sql
│   ├── system_menus_data.sql
│   └── ...
│
├── .env                          # 环境配置模板（含占位符，安装时替换）
├── index.php                     # 安装入口文件（主控逻辑）
├── crmeb_table.sql               # 数据库表结构（仅 CREATE TABLE）
├── crmeb.sql.bak                 # 原始完整 SQL 备份（结构+数据未拆分版本），不参与安装
│
├── install.log                   # 安装过程日志（运行时生成，记录每条建表/写数据/插件安装结果）
└── ../install.lock               # 安装完成锁文件（运行时生成于 public/ 下）
```

## 安装流程

安装向导共 5 个步骤，由 `index.php` 中的 `$step` 参数控制跳转：

| 步骤 | 模板文件 | 说明 |
|------|---------|------|
| 1 | `step1.php` | 许可协议 — 展示用户协议，需同意后方可继续 |
| 2 | `step2.php` | 环境检测 — 检查 PHP 版本、扩展、目录/文件读写权限（详见下文「环境检测项」） |
| 3 | `step3.php` | 参数设置 — 填写数据库连接信息（MySQL + Redis）、管理员账号密码、表前缀、是否导入演示数据；提交前分别校验 MySQL 与 Redis 连通性（详见「连接校验错误码」） |
| 4 | `step4.php` | 安装执行 — 前端轮询接口分批执行「表结构 → 默认数据 → 插件」三个阶段（详见「安装执行三阶段」），随后写入 `.env`、创建管理员账户 |
| 5 | `step5.php` | 安装完成 — 展示安装结果，写入 `.constant`（安装时间与序列号），生成 `install.lock` 锁文件防止重复安装 |

### 环境检测项（步骤2）

- **PHP 版本**：`8.2.0` ≤ 版本 < `8.5.0`（向导展示区间为 8.2-8.4，与 `backend/composer.json` 的 php 约束一致），不满足直接终止安装。
- **PHP 扩展/配置**：`mysqli`、`curl`、`gd`、`bcmath`、`openssl`、`file_uploads`、`session`。
- **目录可写**：`backend/backup`、`backend/public`、`backend/runtime`（不存在时自动创建）。
- **文件可读写**：`backend/.env`、`backend/.version`、`backend/.constant`。

### 连接校验错误码（步骤3）

MySQL 校验（前端提交 `mysqldbpwd` 触发）：

| 返回值 | 含义 |
|--------|------|
| `1` | 连接成功（数据库不存在但有创建权限时，会试建后删除验证） |
| `2002` | 地址或端口错误 |
| `1045` | 用户名或密码错误 |
| `-1` | 连接失败 |
| `-2` | MySQL ≥ 5.7 且 `sql_mode` 含 `STRICT_TRANS_TABLES` / `STRICT_ALL_TABLES` / `TRADITIONAL` / `ANSI`，需要调整数据库配置 |
| `-3` | 目标数据库非空（已存在数据表，不允许覆盖安装） |
| `-4` | 数据库不存在且无权限创建 |
| `-5` | MySQL 版本过低（须 ≥ 5.7，推荐 8.0） |

Redis 校验（前端提交 `redisdbpwd` 触发）：实际执行 `connect` → `auth`（有密码时）→ `select`（指定库时）→ `set` 写入测试键，返回 `1` 成功、`-1` redis 扩展缺失或对象创建失败、`-3` 连接或写入失败。

## 安装执行三阶段（步骤4）

安装接口为 `index.php?step=4&install=1&phase={tables|data|plugins}&n={游标}`，前端逐次轮询，**每次请求只执行一个最小任务单元**，页面实时显示当前进度：

| 阶段 | `phase` 值 | 任务单元 | 结束流转 |
|------|-----------|---------|---------|
| 表结构 | `tables` | 执行一条 `CREATE TABLE`（执行前先 `DROP TABLE IF EXISTS` 同名表，支持覆盖安装） | 全部建表完成后返回 `phase=data`，游标归零 |
| 默认数据 | `data` | 执行一个数据文件（文件内全部 INSERT 一次执行） | 全部文件写入后执行收尾（见下），返回 `phase=plugins` |
| 插件安装 | `plugins` | 初始化 ThinkPHP 后调用 `ModuleInstaller::install()` 执行一个默认安装项（模块/驱动的自有表、菜单与种子数据由各自 Installer 负责） | 游标返回 `n=999999` 表示全部完成 |

- 三个阶段的游标 `n` 相互独立，避免数据阶段沿用表结构的总游标。
- 数据阶段结束后由 `finalizeBaseInstall()` 统一收尾：按需清空演示数据、替换并写入 `.env`、重建管理员账户（`truncate` 后插入 id=1 超管）、按安装域名更新 `system_config` 的 `site_url`。
- 每一步执行结果都追加写入 `install.log`，失败时接口返回 `n=-2` 并携带失败原因，前端停止轮询并展示错误。

## SQL 文件设计

采用表结构 SQL 和按表拆分的数据目录维护安装数据：

- `crmeb_table.sql` — 核心数据库表结构（仅含 `CREATE TABLE` 语句）。
- `data/*_data*.sql` — 按表拆分的核心初始数据，一个文件对应一张表。文件名必须匹配 `^[a-z0-9_]+_data(?:_\d+)?\.sql$`（如 `system_config_data.sql`），**文件名字典序即安装顺序**；数据量特别大的表可拆分为 `table_data_001.sql`、`table_data_002.sql` 等文件（序号补零格式），拆分文件会按序合并为同一张表的任务单元。
- 安装程序读取数据文件时仅提取其中的 `INSERT INTO` 语句，其余内容（注释、SET 等）忽略。
- `crmeb.sql.bak` — 原始完整 SQL 备份（结构+数据未拆分版本），仅用于对照和留档，安装程序不读取。

### 新增核心表与初始数据（维护指南）

核心安装 SQL 只承载**系统框架自身**的表与初始数据；插件/驱动的自有表、菜单与种子数据一律由各自 Installer 负责（遵循插件开发规范），**不要**写入本目录。

新增核心表：

1. 在 `crmeb_table.sql` 末尾追加 `CREATE TABLE IF NOT EXISTS \`eb_表名\`` 语句，表名一律用默认前缀 `eb_` 书写（安装时按用户配置前缀动态替换）；
2. 表结构阶段每次请求执行一条 CREATE（执行前先 DROP 同名表），无需额外登记。

新增核心初始数据：

1. 在 `data/` 下新建 `{表名}_data.sql`，一个文件只写一张表的 `INSERT INTO \`eb_表名\` ...` 语句；
2. **文件名字典序即执行顺序**，跨表外键依赖（如先配置分组后配置项）靠命名顺序保证；
3. 数据量大时可拆分为 `{表名}_data_001.sql`、`{表名}_data_002.sql`（序号补零），同一张表的拆分文件按序合并为一个任务单元一次执行；
4. 文件中不要写注释、`SET` 等非 INSERT 语句（会被忽略）；不要出现生产域名（用 `http://demo.crmeb.com` 占位，安装时自动替换为实际域名，含 JSON 转义形式）。

### SQL 预处理规则

所有安装 SQL 在执行前统一经过 `prepareInstallSql()` 处理：

| 替换项 | 说明 |
|--------|------|
| 表前缀 | `` `eb_ `` → `` `{用户配置前缀}_ ``（仅按反引号形式整体替换，避免误伤 `smtp_host` 等普通字段名） |
| 演示站域名 | `http://demo.crmeb.com` → 实际安装域名 |
| JSON 内层域名 | `http:\/\/demo.crmeb.com`（JSON 转义形式）→ 转义形式的实际域名，保证 `eb_diy`、`eb_theme` 等 JSON 数据安装后同样指向本站 |

实际域名（scheme + host）按以下优先级获取，适配反向代理部署：

1. 反向代理头 `X-Forwarded-Proto` / `X-Forwarded-Host`；
2. `HTTPS` / `HTTP_HOST`；
3. `SERVER_NAME`（非标准端口时自动拼接端口号）。

## .env 配置模板

`.env` 文件包含占位符，安装过程中由安装程序自动替换为用户填写的实际值后写入 `backend/.env`：

| 占位符 | 说明 |
|--------|------|
| `#APP_KEY#` | 应用密钥（自动生成，格式 `app_key_{uniqid}`） |
| `#DB_HOST#` / `#DB_PORT#` / `#DB_USER#` / `#DB_PWD#` / `#DB_NAME#` / `#DB_PREFIX#` | MySQL 数据库连接信息 |
| `#CACHE_TYPE#` / `#CACHE_PREFIX#` / `#CACHE_TAG_PREFIX#` | 缓存配置（file 或 redis），前缀含随机值用于多实例隔离 |
| `#RB_HOST#` / `#RB_PORT#` / `#RB_PWD#` / `#RB_SELECT#` | Redis 连接信息 |
| `#QUEUE_NAME#` | 队列名称（自动生成唯一值） |

## 演示数据控制

步骤4可选择是否导入演示数据；**不导入演示数据时**，默认数据阶段收尾（`finalizeBaseInstall()`）会对库中全部数据表执行 `truncate`，仅保留以下白名单表：

- 清理发生在**插件安装之前**（`tables → data → 清理 → plugins`），因此插件/驱动安装的自有表与种子数据写入在清理之后，天然不受影响。
- 白名单按 `SHOW TABLES` 实际存在的表过滤，仅对实际存在的表执行清理。

白名单（`$baseTables`，共 37 项，按默认前缀 `eb_` 列出，实际按用户配置前缀匹配）：

| 分类 | 表名 |
|------|------|
| 系统配置 | `system_config`、`system_config_tab`、`system_group`、`system_group_data`、`system_event_data` |
| 菜单路由与权限 | `system_menus`、`system_route`、`system_route_cate`、`system_admin`、`system_user_level` |
| 系统任务与工单 | `system_crontab`、`system_ticket`、`system_city` |
| 内容与页面 | `diy`、`page_categroy`、`page_link`、`agreement`、`cache` |
| 物流配送 | `express`、`express_company`、`express_template`、`shipping_templates`、`shipping_templates_region` |
| 多语言 | `lang_code`、`lang_country`、`lang_type` |
| 会员与通知 | `member_right`、`member_ship`、`agent_level`、`notice_config`、`notice_channel` |
| 支付与驱动 | `pay_channel`、`pay_channel_platform`、`driver`、`printer_channel`、`out_interface` |
| 模块安装 | `module_install_log` |

## 运行时生成与读取的文件

| 文件 | 位置 | 说明 |
|------|------|------|
| `install.log` | `install/` 目录内 | 每条建表、写数据、插件安装的结果日志（含时间戳与失败原因），排障首选 |
| `.env` | `backend/.env` | 收尾阶段由模板替换占位符后写入 |
| `.constant` | `backend/.constant` | 步骤5写入 `INSTALL_DATE`（安装时间戳）与 `SERIALNUMBER`（随机序列号） |
| `install.lock` | `public/install.lock` | 安装完成锁文件 |
| `.version` | `backend/.version` | 非生成文件；步骤5读取其中 `version` / `platform` 用于安装完成页展示 |

## 关键逻辑说明

- **防重复安装**：入口处检测 `public/install.lock`，存在即阻止进入向导；如需重装，需手动删除 `public/install.lock`。
- **环境变量优先**：数据库和 Redis 连接信息默认值优先从容器的 `MYSQL_HOST_IP`、`MYSQL_PORT`、`MYSQL_USER`、`MYSQL_PASSWORD`、`MYSQL_DATABASE`、`REDIS_HOST_IP` 等环境变量获取，适用于 Docker 部署场景；表单填写值仍以步骤3提交为准。
- **表前缀替换**：SQL 文件默认使用 `eb_` 前缀，安装时按反引号整体形式动态替换为用户配置前缀；覆盖安装时 `DROP TABLE` 按实际前缀执行。
- **数据库不存在时自动创建**：按 `utf8mb4` / `utf8mb4_general_ci` 字符集执行 `CREATE DATABASE IF NOT EXISTS`。
- **管理员账户**：收尾阶段清空 `system_admin` 后写入 id=1 的超级管理员（账号为步骤3填写，密码经 `PASSWORD_BCRYPT` 加密，角色 `roles=1`）。
- **站点域名写入**：收尾阶段将安装访问域名以 JSON 字符串形式更新到 `system_config` 的 `site_url`（与系统配置读取侧 `json_decode` 约定一致）。
- **最低要求**：PHP 8.2-8.4（不含 8.5+），MySQL ≥ 5.7（推荐 8.0；5.7 需关闭严格 sql_mode），Redis 扩展（选择 redis 缓存时必需）。
