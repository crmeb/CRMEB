# React Profile

- React 应用位于 `frontend/<应用名>/`，从 manifest、lockfile 和构建框架配置核对版本。
- 组件保持纯渲染；副作用只处理外部同步并明确依赖、取消和清理。
- props、事件、路由、API 和表单保持类型安全，避免把派生值复制进 state。
- 服务端缓存、全局客户端状态和组件局部状态使用不同层，避免单一 store 承载一切。
- 标准检查映射 ESLint、TypeScript、单元/组件/E2E 测试和生产构建。
- SSR/CSR 边界、环境变量暴露、hydration、错误边界和静态资源路径进入开发文档。

