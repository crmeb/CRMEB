# Node.js Profile

- 从 `package.json` engines/packageManager、lockfile、版本文件和容器镜像核对 Node 与包管理器。
- 只使用 lockfile 对应的 npm、pnpm、Yarn 或 Bun；CI 使用冻结锁文件安装。
- Node 后端位于 `backend/`，多包项目的 workspace 边界必须显式配置。
- 标准检查映射项目实际 scripts：lint、typecheck、test、build、start/dev。
- 生产运行不依赖开发服务器；处理信号、优雅关闭、健康检查和未捕获异常。
- 不提交 `node_modules/`，升级包是否带依赖由部署模型明确决定。

