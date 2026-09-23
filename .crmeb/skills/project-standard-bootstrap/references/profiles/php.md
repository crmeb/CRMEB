# PHP Profile

- 从 `composer.json`、`composer.lock`、容器镜像和 `composer check-platform-reqs` 核对 PHP 与扩展版本。
- 后端根目录统一为 `backend/`，Composer 命令在该目录执行；`vendor/` 默认不入库。
- 使用项目既有的 PSR、框架分层和自动加载约定，不凭经验改 namespace。
- 标准检查优先映射 Composer scripts、PHP-CS-Fixer/PHPCS、PHPStan/Psalm、PHPUnit/Pest 和框架命令。
- 迁移、队列、定时任务、长驻进程和 OPcache 重载必须进入运行与升级说明。
- 宿主 PHP 不满足版本时可使用项目开发镜像，但须说明实际验证环境。

