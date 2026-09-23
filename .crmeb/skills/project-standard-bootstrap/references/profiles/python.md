# Python Profile

- 从 `pyproject.toml`、lockfile、`.python-version`、tox/nox 和镜像核对 Python 版本与依赖工具。
- 后端位于 `backend/`，虚拟环境和缓存不入库；应用包采用明确的 `src` 或框架结构。
- 依赖必须锁定；开发、测试和生产组边界清楚。
- 标准检查映射 Ruff/Black、mypy/pyright、pytest、构建和框架健康检查中的实际工具。
- 数据库迁移、ASGI/WSGI、worker、scheduler 和静态资源处理进入运行说明。
- 测试隔离时区、事件循环、数据库事务和外部服务。

