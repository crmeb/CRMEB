# Vue Profile

- Vue 应用位于 `frontend/<应用名>/`，从 package manifest、lockfile 和 Vite/Nuxt 配置核对版本。
- Vue 3 优先 Composition API 与 `<script setup>`；以项目现有 TypeScript/JavaScript策略为准。
- props、emits、slots、template refs 和路由参数保持类型安全；避免解构破坏响应性。
- 服务端状态、全局状态和局部状态职责分离；Pinia 等 store 不承载页面瞬时状态。
- 标准检查映射 ESLint、vue-tsc、Vitest、组件/E2E 测试和生产构建。
- 环境变量公开边界、base path、代理、HMR 和静态部署路径进入开发文档。

