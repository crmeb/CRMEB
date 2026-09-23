# Java Profile

- 从 Maven/Gradle 配置、toolchain、wrapper 和镜像核对 JDK 与构建工具版本。
- 后端位于 `backend/`；必须使用仓库内 Maven/Gradle Wrapper，避免本机版本漂移。
- 多模块依赖方向在构建文件中显式声明，领域层不依赖 Web 或持久化实现。
- 标准检查映射 formatter、Checkstyle/SpotBugs、单测、集成测试和 assemble/build。
- 配置按 profile/environment 隔离，密钥不进入 `application*.yml`。
- 数据库迁移、JVM 参数、健康检查、优雅关闭和容器内存边界进入运行文档。

